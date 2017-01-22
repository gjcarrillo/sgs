<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');
use Mailgun\Mailgun;

class ApplicantHomeController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
        $this->load->view('applicantHome');
	}

    // Obtain all requests with with all their documents.
    // NOTICE: sensitive information
    public function getUserRequests() {
        if ($_GET['fetchId'] != $_SESSION['id'] && $_SESSION['type'] > 2) {
            // if fetch id is not the same as logged in user, must be an
            // agent or manager to be able to execute query!
            $this->load->view('errors/index.html');
        } else {
            try {
				// Get configured's max. and min. request amount.
                $em = $this->doctrine->em;
                $config = $em->getRepository('\Entity\Config');
                $result['maxReqAmount'] = $config->findOneBy(array('key' => 'MAX_AMOUNT'))->getValue();
                $result['minReqAmount'] = $config->findOneBy(array('key' => 'MIN_AMOUNT'))->getValue();
                $user = $em->find('\Entity\User', $_GET['fetchId']);
                $requests = $user->getRequests();
                if ($requests->isEmpty()) {
                    $result['message'] = "No se han encontrado solicitudes";
                } else {
                    $requests = array_reverse($requests->getValues());
                    foreach ($requests as $rKey => $request) {
                        $result['requests'][$rKey]['id'] = $request->getId();
                        $result['requests'][$rKey]['creationDate'] = $request->getCreationDate()->format('d/m/y');
                        $result['requests'][$rKey]['comment'] = $request->getComment();
                        $result['requests'][$rKey]['reqAmount'] = $request->getRequestedAmount();
                        $result['requests'][$rKey]['approvedAmount'] = $request->getApprovedAmount();
                        $result['requests'][$rKey]['reunion'] = $request->getReunion();
                        $result['requests'][$rKey]['status'] = $request->getStatus();
						$result['requests'][$rKey]['type'] = $request->getLoanType();
                        $result['requests'][$rKey]['phone'] = $request->getContactNumber();
                        $result['requests'][$rKey]['due'] = $request->getPaymentDue();
                        $result['requests'][$rKey]['email'] = $request->getContactEmail();
                        $result['requests'][$rKey]['validationDate'] = $request->getValidationDate();
                        $docs = $request->getDocuments();
                        foreach ($docs as $dKey => $doc) {
                            $result['requests'][$rKey]['docs'][$dKey]['id'] = $doc->getId();
                            $result['requests'][$rKey]['docs'][$dKey]['name'] = $doc->getName();
                            $result['requests'][$rKey]['docs'][$dKey]['description'] = $doc->getDescription();
                            $result['requests'][$rKey]['docs'][$dKey]['lpath'] = $doc->getLpath();
                        }
                    }
                    $result['message'] = "success";
                }
            } catch (Exception $e) {
                \ChromePhp::log($e);
                $result['message'] = "error";
            }
            echo json_encode($result);
        }
    }

    public function sendValidation() {
        if ($_SESSION['type'] != 3) {
            $this->load->view('errors/index.html');
        } else {
            try {
                $reqId = json_decode(file_get_contents('php://input'), true);
                $em = $this->doctrine->em;
                $request = $em->find('\Entity\Request', $reqId);
                $tokenData['uid'] = $request->getUserOwner()->getId();
                $tokenData['rid'] = $reqId;
                $tokenData['reqAmount'] = $request->getRequestedAmount();
                $tokenData['tel'] = $request->getContactNumber();
                $tokenData['email'] = $request->getContactEmail();
                $tokenData['due'] = $request->getPaymentDue();
                $tokenData['loanType'] = $request->getLoanType();
                $this->sendValidationToken($tokenData, $request);
                $this->registerValidationResend($em, $request);
                $result['message'] = "success";
            } catch (Exception $e) {
                \ChromePhp::log($e);
                $result['message'] = "error";
            }
            echo json_encode($result);
        }
    }

    private function registerValidationResend($em, $request) {
        // Register History
        $history = new \Entity\History();
        $history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
        $history->setUserResponsable($_SESSION['name'] . ' ' . $_SESSION['lastName']);
        // Register it's corresponding actions
        // 7 = Validation
        $history->setTitle(7);
        $history->setOrigin($request);
        $request->addHistory($history);
        $action = new \Entity\HistoryAction();
        $action->setSummary("Reenvío de correo de validación.");
        $action->setDetail("Enviado nuevo correo de validación por solicitud de reenvío, a " .
                           "la dirección de correo " . $request->getContactEmail());
        $action->setBelongingHistory($history);
        $history->addAction($action);
        $em->persist($action);
        $em->persist($history);
        $em->merge($request);
        $em->flush();
        $em->clear();
    }

    private function sendValidationToken($tokenData, $request) {
        $encodedURL = $this->createToken($tokenData);
        $mailData['reqId'] = $request->getId();
        $user = $request->getUserOwner();
        $mailData['username'] = $user->getFirstName() . ' ' . $user->getLastName();
        $mailData['userId'] = $user->getId();
        $mailData['creationDate'] = $request->getCreationDate()->format('d/m/Y');
        $mailData['reqAmount'] = $request->getRequestedAmount();
        $mailData['tel'] = $request->getContactNumber();
        $mailData['email'] = $request->getContactEmail();
        $mailData['loanTypeString'] = $this->mapLoanType($request->getLoanType());
        $mailData['due'] = $request->getPaymentDue();
        $mailData['paymentFee'] = $this->calculatePaymentFee($mailData['reqAmount'], $mailData['due'], 12);
        $mailData['subject'] = '[Solicitud ' . str_pad($mailData['reqId'], 6, '0', STR_PAD_LEFT) .
                               '] Confirmación de Nueva Solicitud';
        $mailData['validationURL'] = $this->config->base_url() . '#validate/' . $encodedURL;
        $reqTokenData['rid'] = $request->getId();
        $mailData['deleteURL'] = $this->config->base_url() . '#delete/' . $this->createToken($reqTokenData);
        $html = $this->load->view('templates/validationMail', $mailData, true); // render the view into HTML
        $this->sendEmail($mailData['email'], $mailData['subject'], $html);
    }

    private function createToken ($data) {
        $encoded = JWT::encode($data, SECRET_KEY);
        $urlEncoded = JWT::urlsafeB64Encode($encoded);
        return $urlEncoded;
    }

    private function sendEmail ($to, $subject, $html) {
        $mgClient = new Mailgun('key-53747f43c23bd393d8172814c60e17ba', new \Http\Adapter\Guzzle6\Client());
        $domain = "sandbox5acc2f3be9df4e80baaa6a9884d6299b.mailgun.org";
        $email = array(
            'from'    => 'IPAPEDI <noreply@ipapedi.com>',
            'to'      => $to,
            'subject' => $subject,
            'html'    => $html
        );
        $mgClient->sendMessage($domain, $email);
        \ChromePhp::log("Message sent!");
    }

    public function download() {
        // [0] = userId, [1] = request type, [2] = loan number, [3] = filename, [4] = file extension
        $parsed = explode('.', $_GET['lpath']);
        // Get the Id of the document's owner.
        $userOwner = $parsed[0];
        if ($userOwner != $_SESSION['id'] && $_SESSION['type'] > 2) {
            // Only agents can download documents that are not their own.
            $this->load->view('errors/index.html');
        } else {
            // file information
            if ($parsed[4] === "pdf") {
				// Don't force downloads on pdf files
                header('Content-type: application/pdf');
                header('Content-Disposition: inline; filename="' . $parsed[3] . '.' . $parsed[4] . '"');
            } else if ($parsed[4] === "png"
                || $parsed[4] === "jpg"
                || $parsed[4] === "jpeg"
                || $parsed[4] === "gif"
                || $parsed[4] === "tif") {
				// Don't force downloads on image files
                header('Content-type: image/' . $parsed[4]);
                header('Content-Disposition: inline; filename="' . $parsed[3] . '.' . $parsed[4] . '"');
            } else {
				// Force downloads on files that aren't pdf nor image files.
                header('Content-Disposition: attachment; filename="' . $parsed[3] . '.' . $parsed[4] . '"');
            }
            // The document source
            readfile(DropPath . $_GET['lpath']);
        }
    }

    public function downloadAll() {
        // At least 2 documents will always be available for download.
        $docs = json_decode($_GET['docs']);
		// [0] = userId, [1] = request type, [2] = loan number, [3] = filename, [4] = file extension
        $parsed = explode('.', $docs[0]);
        // Get the Id of the document's owner.
        $userOwner = $parsed[0];
        // Create the ZIP
        $zipname = time() . ".zip";
        $zip = new ZipArchive;
        $zip->open($zipname, ZipArchive::CREATE);
        foreach ($docs as $doc) {
            if ($userOwner == $_SESSION['id'] || $_SESSION['type'] <= 2) {
                // Only agents & managers can download documents that are not their own.
                $tmp = explode('.', $doc);
                $filename = $tmp[3] . "." . $tmp[4];
                $zip->addFromString(basename($filename),  file_get_contents(DropPath . $doc));
            }
        }
        $zip->close();
		ignore_user_abort(true);
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename='.$zipname);
        header('Content-Length: ' . filesize($zipname));
        readfile($zipname);
        // TODO: Test in a non-local hosting to see if download is not interrupted
        unlink($zipname);
    }


    private function mapLoanType($code) {
        return $code == 40 ? "PRÉSTAMO PERSONAL" : ($code == 31 ? "VALE DE CAJA" : $code);
    }

    /**
     * Calculates the monthly payment fee the applicant must pay.
     *
     * @param $reqAmount - the amount of money the applicant is requesting.
     * @param $paymentDue - number in months the applicant chose to pay his debt.
     * @param $interest - payment interest (percentage).
     * @return float - monthly payment fee.
     */
    private function calculatePaymentFee($reqAmount, $paymentDue, $interest) {
        $rate = $interest / 100 ;
        // monthly payment.
        $nFreq = 12;
        // calculate the interest as a factor
        $interestFactor = $rate / $nFreq;
        // calculate the monthly payment fee
        return $reqAmount / ((1 - pow($interestFactor +1, $paymentDue * -1)) / $interestFactor);
    }
}
