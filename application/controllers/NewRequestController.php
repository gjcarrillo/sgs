<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');
use Mailgun\Mailgun;

class NewRequestController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
		// Everyone can create requests
		$this->load->view('newRequest');
	}

    public function upload() {
		if ($_POST['userId'] != $_SESSION['id'] && $_SESSION['type'] > 1) {
			// Only agents can upload documents that aren't their own
			$this->load->view('errors/index.html');
		} else {
			$uploadfile = DropPath . $_POST['userId'] . '.' .
				$_POST['requestNumb'] . '.' . basename($_FILES['file']['name']);
	        move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);

	        $result['lpath'] = $_POST['userId'] . '.' . $_POST['requestNumb'] .
				'.' . basename($_FILES['file']['name']);

	        echo json_encode($result);
		}
    }

	/**
	 * Gets a specific user's concurrence percentage.
	 */
	public function getUserConcurrence() {
		$result['message'] = "error";
		if ($_GET['userId'] != $_SESSION['id'] && $_SESSION['type'] > 1) {
			$this->load->view('errors/index.html');
		} else {
			$this->db->select('*');
			$this->db->from('db_dt_personales');
			$this->db->where('cedula', $_GET['userId']);
			$query = $this->db->get();
			if (empty($query->result())) {
				// User info not found! Set concurrence to max.
				$result['concurrence'] = 100;
			} else {
				$result['concurrence'] = $query->result()[0]->concurrencia;
				$result['message'] = "success";
			}
		}

		echo json_encode($result);
	}

	/**
	 * Gets a specific user's last requests granting, which indicates whether user can
	 * request the same type of request or not.
	 */
	public function getLastRequestsGranting() {
		$result['message'] = "error";
		if ($_GET['userId'] != $_SESSION['id'] && $_SESSION['type'] > 1) {
			$this->load->view('errors/index.html');
		} else {
			try {
				$em = $this->doctrine->em;
				$span = $em->getRepository('\Entity\Config')->findOneBy(array("key" => 'SPAN'))->getValue();
				$result['granting']['span'] = $span;
				$loanTypes = array(31, 40);
				foreach ($loanTypes as $type) {
					$this->db->select('*');
					$this->db->from('db_dt_prestamos');
					$this->db->where('cedula', $_GET['userId']);
					$this->db->where('concepto', $type);
					$query = $this->db->order_by('otorg_fecha',"desc")->get();
					if (empty($query->result())) {
						// Seems like this is their first request. Grant permission to create!
						$result['granting']['allow'][$type] = true;
					} else {
						$granting = date_create_from_format('d/m/Y', $query->result()[0]->otorg_fecha);
						$currentDate = new DateTime('now', new DateTimeZone('America/Barbados'));
						$interval = $granting->diff($currentDate);
						$monthsPassed = $interval->format("%m");
						$monthsLeft = $span - $monthsPassed;
						$result['granting']['allow'][$type] = $monthsLeft <= 0;
					}
					$result['message'] = 'success';
				}
			} catch (Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = 'error';
			}
		}

		echo json_encode($result);
	}

    public function createRequest() {
		$data = json_decode(file_get_contents('php://input'), true);
		\ChromePhp::log($data);
		if ($data['userId'] != $_SESSION['id'] && $_SESSION['type'] > 1) {
			// Only agents can create requests for other people
			$this->load->view('errors/index.html');
		} else {
	        try {
	            $em = $this->doctrine->em;
	            // New request
	            $request = new \Entity\Request();
				// Register History first
				$history = new \Entity\History();
				$history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
				$history->setUserResponsable($_SESSION['name'] . ' ' . $_SESSION['lastName']);
				// 1 = Creation
				$history->setTitle(1);
				$history->setOrigin($request);
				$request->addHistory($history);
				// Register it's corresponding actions
				$action = new \Entity\HistoryAction();
				$action->setSummary("Estatus de la solicitud: Recibida.");
				$action->setBelongingHistory($history);
				$history->addAction($action);
				$em->persist($action);
				$action = new \Entity\HistoryAction();
				$action->setSummary("Monto solicitado: Bs " . number_format($data['reqAmount'], 2));
				$action->setBelongingHistory($history);
				$history->addAction($action);
				$em->persist($action);
				$action = new \Entity\HistoryAction();
				$action->setSummary("Número de contacto: " . $data['tel']);
				$action->setBelongingHistory($history);
				$history->addAction($action);
				$em->persist($action);
				$action = new \Entity\HistoryAction();
				$action->setSummary("Dirección de correo: " . $data['email']);
				$action->setBelongingHistory($history);
				$history->addAction($action);
				$em->persist($action);
				$action = new \Entity\HistoryAction();
				$action->setSummary("Plazo para pagar: " . $data['due'] . " meses.");
				$action->setBelongingHistory($history);
				$history->addAction($action);
				$em->persist($action);
				$action = new \Entity\HistoryAction();
				$action->setSummary("Tipo de préstamo: " . $this->mapLoanType($data['loanType']));
				$action->setBelongingHistory($history);
				$em->persist($action);
				$history->addAction($action);
				$em->persist($history);
	            $request->setStatus('Recibida');
	            $request->setCreationDate(new DateTime('now', new DateTimeZone('America/Barbados')));
				$request->setRequestedAmount($data['reqAmount']);
				$request->setLoanType($data['loanType']);
				$request->setPaymentDue($data['due']);
				$request->setContactNumber($data['tel']);
				$request->setContactEmail($data['email']);
	            $user = $em->find('\Entity\User', $data['userId']);
	            $request->setUserOwner($user);
	            $user->addRequest($request);
	            $em->persist($request);
	            $em->merge($user);
	            $em->flush();
				// Create the new request doc.
				$this->generateRequestDocument($data, $user, $request);
				$this->createDocuments($request, $history->getId(), $data['docs']);
				// Send request validation token.
				$this->sendValidationToken($request);
				$this->registerValidationSending($request);
	            $result['message'] = "success";
	        } catch (Exception $e) {
	             \ChromePhp::log($e);
	            $result['message'] = "error";
	        }

	        echo json_encode($result);
		}
    }

	// Helper function that creates a set of docs in database.
	private function createDocuments($request, $historyId, $docs) {
		try {
			$em = $this->doctrine->em;
			foreach ($docs as $data) {
				$doc = $em->getRepository('\Entity\Document')->findOneBy(array(
					"lpath" => $data['lpath']
				));
				if ($doc !== null) {
					// doc already exists, so just merge. Otherwise we'll have
					// 'duplicates' in database, because document name is not primary key
					if (isset($data['description'])) {
						$doc->setDescription($data['description']);
						$em->merge($doc);
					}
				} else {
					// New document
					$doc = new \Entity\Document();
					$doc->setName($data['docName']);
					if (isset($data['description'])) {
						$doc->setDescription($data['description']);
					}
					$doc->setLpath($data['lpath']);
					$doc->setBelongingRequest($request);
					$request->addDocument($doc);

					$em->persist($doc);
					$em->merge($request);
				}
				// Set History action for this request's corresponding history
				$history =  $em->find('\Entity\History', $historyId);
				$action = new \Entity\HistoryAction();
				$action->setSummary("Adición del documento '" . $data['docName'] . "'.");
				if (isset($data['description']) && $data['description'] !== "") {
					$action->setDetail("Descripción: " . $data['description']);
				}
				$action->setBelongingHistory($history);
				$history->addAction($action);
				$em->persist($action);
				$em->merge($history);
			}
			$em->flush();
		} catch (Exception $e) {
			\ChromePhp::log($e);
			$result['message'] = "error";
		}
	}

	// Helper function that generates the new request's pdf document.
	private function generateRequestDocument($data, $user, $request) {
		$pdfFilePath = DropPath . $data['docs'][0]['lpath'];
		// Get extra data for the pdf template.
		$data['username'] = $user->getFirstName() . ' ' . $user->getLastName();
		$data['requestId'] = str_pad($request->getId(), 6, '0', STR_PAD_LEFT);
		$data['date'] = new DateTime('now', new DateTimeZone('America/Barbados'));
		$data['loanTypeString'] = $this->mapLoanType($data['loanType']);
		$data['paymentFee'] = $this->calculatePaymentFee($data['reqAmount'], $data['due'], 12);
		// Generate the document.
		\ChromePhp::log("Genrating pdf...");
		$html = $this->load->view('templates/requestPdf', $data, true); // render the view into HTML
		$this->load->library('pdf');
		$pdf = $this->pdf->load();
		$pdf->WriteHTML($html); // write the HTML into the PDF
		// Set footer
		$pdf->SetHTMLFooter (
			'<p style="font-size: 14px">
			* Cuotas y plazo de pago sujetos a cambios en base a solicitudes posteriores
			del afiliado en cuestión.
		</p>');
		$pdf->Output($pdfFilePath, 'F'); // save to file
		\ChromePhp::log("PDF generation success!");
	}

	private function sendValidationToken($request) {
		$tokenData['uid'] = $request->getUserOwner()->getId();
		$tokenData['rid'] = $request->getId();
		$tokenData['reqAmount'] = $request->getRequestedAmount();
		$tokenData['tel'] = $request->getContactNumber();
		$tokenData['email'] = $request->getContactEmail();
		$tokenData['due'] = $request->getPaymentDue();
		$tokenData['loanType'] = $request->getLoanType();
		$encodedURL = $this->createToken($tokenData);
		$mailData['reqId'] = $request->getId();
		$user = $request->getUserOwner();
		$mailData['username'] = $user->getFirstName() . ' ' . $user->getLastName();
		$mailData['userId'] = $user->getId();
		$mailData['creationDate'] = $request->getCreationDate()->format('d/m/Y');
		$mailData['reqAmount'] = $request->getRequestedAmount();
		$mailData['loanTypeString'] = $this->mapLoanType($request->getLoanType());
		$mailData['tel'] = $request->getContactNumber();
		$mailData['email'] = $request->getContactEmail();
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

	private function registerValidationSending($request) {
		$em = $this->doctrine->em;
		// Register History
		$history = new \Entity\History();
		$history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
		$history->setUserResponsable($_SESSION['name'] . ' ' . $_SESSION['lastName']);
		// Register it's corresponding actions
		// 7 = Validation
		$history->setTitle(7);
		$history->setOrigin($request);
		$action = new \Entity\HistoryAction();
		$action->setSummary("Envío de correo de validación.");
		$action->setDetail("Enviado correo de validación a " .
						   "la dirección de correo " . $request->getContactEmail());
		$action->setBelongingHistory($history);
		$history->addAction($action);
		$em->persist($action);
		$em->persist($history);
		$em->merge($request);
		$em->flush();
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

	public function uploadBase64Images() {
		if ($_SESSION['type'] != 1) {
			$this->load->view('errors/index.html');
		} else {
			$data = json_decode(file_get_contents('php://input'), true);
			$imageData = $data['imageData'];
			$imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i',
			 	'', $imageData));

			$filepath = DropPath . $data['userId'] . "." . $data['requestNumb'] .
			 	"." . $data['docName'] . ".png";
			file_put_contents($filepath, $imageData);

			$result['message'] = "success";
			$result['lpath'] = $data['userId'] . "." . $data['requestNumb'] .
			 	"." . $data['docName'] . ".png";
			echo json_encode($result);
		}
	}

	/**
	 * Calculates the monthly payment fee the applicant must pay.
	 *
	 * @param $reqAmount - the amount of money the applicant is requesting.
	 * @param $paymentDue - number in months the applicant chose to pay his debt.
	 * @param $interest - payment interest (percentage).
	 * @return float - monthly payment fee.
	 */
	private function calculatePaymentFee($reqAmount, $paymentDue, $interest){
		$rate = $interest / 100 ;
		// monthly payment.
		$nFreq = 12;
		// calculate the interest as a factor
		$interestFactor = $rate / $nFreq;
		// calculate the monthly payment fee
		return $reqAmount / ((1 - pow($interestFactor +1, $paymentDue * -1)) / $interestFactor);
	}

	private function mapLoanType($code) {
		return $code == 40 ? "PRÉSTAMO PERSONAL" : ($code == 31 ? "VALE DE CAJA" : $code);
	}
}
