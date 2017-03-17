<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once (APPPATH. '/libraries/ChromePhp.php');

/**
 * Created by PhpStorm.
 * User: Kristopher
 * Date: 2/4/2017
 * Time: 6:55 PM
 */
class RequestsModel extends CI_Model
{
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

    public function getUserRequests() {
        try {
            // Get configured's max. and min. request amount.
            $em = $this->doctrine->em;
            $config = $em->getRepository('\Entity\Config');
            $result['maxReqAmount'] = $config->findOneBy(array('key' => 'MAX_AMOUNT'))->getValue();
            $result['minReqAmount'] = $config->findOneBy(array('key' => 'MIN_AMOUNT'))->getValue();
            $user = $em->find('\Entity\User', $this->input->get('fetchId'));
            if ($user === null) {
                $result['error'] = "La cédula ingresada no se encuentra en la base de datos";
            } else {
                $requests = $user->getRequests();
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
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        return json_encode($result);
    }

    public function deleteDocument () {
        try {
            $data = json_decode($this->input->raw_input_stream, true);
            $em = $this->doctrine->em;
            // Delete the document from the server.
            // Get the specified doc entity
            $doc = $em->find('\Entity\Document', $data['id']);
            // Get it's request.
            $request = $doc->getBelongingRequest();
            if (!$this->isRequestValidated($request) ||
                $this->isRequestClosed($request) ||
                // can't delete auto-generated document.
                $doc->getLpath() == $request->getDocuments()[0]->getLpath()) {
                // request must be validated & not yet closed.
                $result['message'] = 'Esta solicitud no puede ser modificada.';
            } else {
                unlink(DropPath . $data['lpath']);
                // Remove this doc from it's request entity
                $request->removeDocument($doc);
                // Register History
                $history = new \Entity\History();
                $history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
                $history->setUserResponsible($this->users->getUser($this->session->id));
                $history->setTitle($this->utils->getHistoryActionCode('elimination'));
                $history->setOrigin($request);
                $request->addHistory($history);
                $em->merge($request);
                // Register it's corresponding action
                $action = new \Entity\HistoryAction();
                $action->setSummary("Eliminación del documento '" . $doc->getName() . "'.");
                $changes = "<li>Eliminación del documento '" . $doc->getName() . "'.</li>";
                $action->setBelongingHistory($history);
                $history->addAction($action);
                $em->persist($action);
                $em->persist($history);
                // Delete the document.
                $em->remove($doc);
                $this->load->model('emailModel', 'email');
                $this->email->sendRequestUpdateEmail($request->getId(), $changes);
                // Persist the changes in database.
                $em->flush();
                $result['message'] = "success";
            }
        } catch (Exception $e) {
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        return json_encode($result);
    }

    public function downloadDocument () {
        // [0] = userId, [1] = file uuid, [2] = filename, [3] = file extension
        $parsed = explode('.', $this->input->get('lpath'));
        // Get the Id of the document's owner.
        $userOwner = $parsed[0];
        if ($userOwner != $this->session->id && $this->session->type == APPLICANT) {
            // applicants are not allowed to download documents that are not their own.
            $this->load->view('errors/index.html');
        } else {
            // file information
            if ($parsed[3] === "pdf") {
                // Don't force downloads on pdf files
                header('Content-type: application/pdf');
                header('Content-Disposition: inline; filename="' . $parsed[2] . '.' . $parsed[3] . '"');
            } else if ($parsed[3] === "png"
                       || $parsed[3] === "jpg"
                       || $parsed[3] === "jpeg"
                       || $parsed[3] === "gif"
                       || $parsed[3] === "tif") {
                // Don't force downloads on image files
                header('Content-type: image/' . $parsed[3]);
                header('Content-Disposition: inline; filename="' . $parsed[2] . '.' . $parsed[3] . '"');
            } else {
                // Force downloads on files that aren't pdf nor image files.
                header('Content-Disposition: attachment; filename="' . $parsed[2] . '.' . $parsed[3] . '"');
            }
            // The document source
            readfile(DropPath . $this->input->get('lpath'));
        }
    }

    public function downloadAllDocuments () {
        $docs = json_decode($this->input->get('docs'));
        // Create the ZIP
        $zipname = time() . ".zip";
        $zip = new ZipArchive;
        $zip->open($zipname, ZipArchive::CREATE);
        foreach ($docs as $doc) {
            $tmp = explode('.', $doc);
            // [0] = userId, [1] = file uuid, [2] = filename, [3] = file extension
            if ($tmp[0] == $this->session->id || $this->session->type != APPLICANT) {
                // applicants are not allowed to download documents that are not their own.
                $filename = $tmp[2] . "." . $tmp[3];
                $zip->addFromString(basename($filename),  file_get_contents(DropPath . $doc));
            }
        }
        $zip->close();
        ignore_user_abort(true);
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename='.$zipname);
        header('Content-Length: ' . filesize($zipname));
        readfile($zipname);
        unlink($zipname);
    }

    public function deleteRequestJWT() {
        $data = json_decode($this->input->raw_input_stream, true);
        try {
            $em = $this->doctrine->em;
            $urlDecoded = JWT::urlsafeB64Decode($data['rid']);
            $decoded = JWT::decode($urlDecoded, JWT_SECRET_KEY);

            $request = $em->find('\Entity\Request', $decoded->rid);
            if ($request == null) {
                $result['message'] = 'No existe dicha solicitud.';
            } else if ($request->getUserOwner()->getId() != $_SESSION['id']) {
                $result['message'] = 'Esta solicitud no le pertenece.';
            } else if ($this->isRequestValidated($request) || $this->isRequestClosed($request)) {
                $result['message'] = 'Esta solicitud no puede ser eliminada.';
            } else {
                // Must delete all documents belonging to this request first
                $docs = $request->getDocuments();
                foreach($docs as $doc) {
                    unlink(DropPath . $doc->getLpath());
                }
                // Now we can remove the current request (and docs on cascade)
                $em->remove($request);
                // Persist the changes in database.
                $em->flush();
                $result['message'] = "success";
            }
        } catch (Exception $e) {
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        return json_encode($result);
    }

    public function deleteRequestUI() {
        try {
            $data = json_decode($this->input->raw_input_stream, true);
            $em = $this->doctrine->em;
            $request = $em->find('\Entity\Request', $data['id']);
            if ($this->isRequestValidated($request) || $this->isRequestClosed($request)) {
                $result['message'] = 'Esta solicitud no puede ser eliminada.';
            } else {
                // Must delete all documents belonging to this request first
                $docs = $request->getDocuments();
                foreach($docs as $doc) {
                    unlink(DropPath . $doc->getLpath());
                }
                // Now we can remove the current request (and docs on cascade)
                $em->remove($request);
                // Persist the changes in database.
                $em->flush();
                $result['message'] = "success";
            }
        } catch (Exception $e) {
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        return json_encode($result);
    }

    public function generateRequestDocument ($request) {
        // Get extra data for the pdf template.
        $data['reqAmount'] = $request->getRequestedAmount();
        $data['tel'] = $request->getContactNumber();
        $data['email'] = $request->getContactEmail();
        $data['due'] = $request->getPaymentDue();
        $data['userId'] = $request->getUserOwner()->getId();
        $data['loanType'] = $request->getLoanType();
        $data['lpath'] = $request->getDocuments()[0]->getLpath();
        $data['username'] = $request->getUserOwner()->getFirstName() . ' ' . $request->getUserOwner()->getLastName();
        $data['requestId'] = str_pad($request->getId(), 6, '0', STR_PAD_LEFT);
        $data['date'] = new DateTime('now', new DateTimeZone('America/Barbados'));
        $data['loanTypeString'] = $this->utils->mapLoanType($data['loanType']);
        $data['paymentFee'] = $this->utils->calculatePaymentFee($data['reqAmount'],
                                                                $data['due'],
                                                                $this->utils->getInterestRate($data['loanType']));
        // Generate the document.
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
        $pdfFilePath = DropPath . $data['lpath'];
        $pdf->Output($pdfFilePath, 'F'); // save to file
    }

    // Helper function that adds a set of docs to a request in database & returns an html string with
    // registered changes (for email notification).
    public function addDocuments($request, $history, $docs) {
        if ($this->isRequestClosed($request)) {
            // request must not yet closed.
            throw new Exception('Esta solicitud no puede ser modificada.');
        } else {
            try {
                $em = $this->doctrine->em;
                $changes = '';
                foreach ($docs as $data) {
                    $doc = $em->getRepository('\Entity\Document')->findOneBy(array("lpath" => $data['lpath']));
                    if ($doc !== null) {
                        // doc already exists, so just merge. Otherwise we'll have
                        // 'duplicates' in database, because document name is not unique
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
                    $action = new \Entity\HistoryAction();
                    $action->setSummary("Adición del documento '" . $data['docName'] . "'.");
                    $changes = $changes . "<li>Adición del documento '" . $data['docName'] . "'. ";
                    if (isset($data['description']) && $data['description'] !== "") {
                        $action->setDetail("Descripción: " . $data['description']);
                        $changes = $changes .
                                   'Descripción: ' .$data['description'] . '.</li>';
                    }
                    $action->setBelongingHistory($history);
                    $history->addAction($action);
                    $em->persist($action);
                }
                return $changes;
            } catch (Exception $e) {
                throw $e;
            }
        }
    }

    public function getSpanLeft ($uid, $loanType) {
        try {
            $em = $this->doctrine->em;
            $span = $em->getRepository('\Entity\Config')->findOneBy(array('key' => 'SPAN'))->getValue();

            $this->ipapedi_db = $this->load->database('ipapedi_db', true);
            $this->ipapedi_db->select('*');
            $this->ipapedi_db->from('db_dt_prestamos');
            $this->ipapedi_db->where('cedula', $uid);
            $this->ipapedi_db->where('concepto', $loanType);
            $query = $this->ipapedi_db->order_by('otorg_fecha',"desc")->get();
            if (empty($query->result())) {
                // User's first request.
                return 0;
            } else {
                $granting = date_create_from_format('d/m/Y', $query->result()[0]->otorg_fecha);
                if (!$granting) {
                    // No granting date found in granting entry. Perhaps it was rejected?
                    // Go ahead and allow this request type creation
                    // TODO: CONFIRM IF THIS IS THE ACTION TO TAKE IN THIS CASE
                    return 0;
                }
                $currentDate = new DateTime('now', new DateTimeZone('America/Barbados'));
                $interval = $granting->diff($currentDate);
                $monthsPassed = $interval->format("%m");
                return $span - $monthsPassed;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Determines whether the corresponding request is closed or not.
     *
     * @param $request - doctrine request object.
     * @return bool {@code true} if specified request is closed. {@code false} otherwise.
     * @throws Exception
     */
    public function isRequestClosed($request) {
        try {
            return ($request->getStatus() == APPROVED || $request->getStatus() == REJECTED);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Determines whether the corresponding request was already validated.
     *
     * @param $request - doctrine request object.
     * @return bool {@code true} if specified request is valid. {@code false} otherwise.
     * @throws Exception
     */
    public function isRequestValidated($request) {
        try {
            return $request->getValidationDate() !== null;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Adds the granting date when request is approved.
     *
     * @param $request - request Entity.
     * @throws Exception
     */
    public function addGrantingDate($request) {
        try {
            $this->ipapedi_db = $this->load->database('ipapedi_db', true);
            $newData = array(
                'cedula' => $request->getUserOwner()->getId(),
                'concepto' => $request->getLoanType(),
                'otorg_fecha' => (new DateTime('now', new DateTimeZone('America/Barbados')))->format('d/m/Y'),
                'otorg_monto' => $request->getApprovedAmount(),
                'otorg_inter' => $this->utils->getInterestRate($request->getLoanType()),
                'otorg_plazo' => $request->getPaymentDue(),
                'otorg_cuota' => $this->utils->calculatePaymentFee(
                    $request->getApprovedAmount(),
                    $request->getPaymentDue(),
                    $this->utils->getInterestRate($request->getLoanType()))
            );
            $this->ipapedi_db->insert('db_dt_prestamos', $newData);
        } catch (Exception $e) {
            throw $e;
        }
    }
}