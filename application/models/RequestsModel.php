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
    private $loanTypes = null;

    public function __construct() {
        parent::__construct();
        $this->load->model('driveModel');
        $this->loanTypes = $this->configModel->getLoanTypes();
        $this->load->library('session');
    }

    public function getUserRequests() {
        $result = array();
        try {
            $em = $this->doctrine->em;
            $user = $em->find('\Entity\User', $this->input->get('fetchId'));
            if ($user === null) {
                throw new Exception("La cédula ingresada no se encuentra en la base de datos");
            } else {
                $requests = $user->getRequests();
                $requests = array_reverse($requests->getValues());
                foreach ($requests as $rKey => $request) {
                    $result[$rKey] = $this->utils->reqToArray($request);
                }
                return $result;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getRequestById($rid, $uid) {
        try {
            $em = $this->doctrine->em;
            $request = $em->find('\Entity\Request', $rid);
            if ($request === null ||
                ($request->getUserOwner()->getId() != $uid)) {
                throw new Exception('No se ha encontrado solicitud con ID ' .
                                     str_pad($_GET['rid'], 6, '0', STR_PAD_LEFT));
            } else {
                return $this->utils->reqToArray($request);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Gets the requests of a specific user that are still being paid.
     *
     * @param $uid - user's id.
     * @return array of active requests.
     * @throws Exception
     */
    public function getActiveRequests($uid) {
        try {
            $em = $this->doctrine->em;
            $user = $em->find('\Entity\User', $uid);
            if ($user === null) {
                throw new Exception("La cédula ingresada no se encuentra en la base de datos");
            } else {
                $result = array();
                $requests = $user->getRequests();
                $requests = array_reverse($requests->getValues());
                // Get active request for each request concept available.
                foreach ($this->loanTypes as $lKey => $loanType) {
                    $lastLoan = $this->getLastLoanInfo($uid, $lKey);
                    if ($lastLoan !== null && $lastLoan->saldo_edo > 0) {
                        // Loan still active found.
                        foreach ($requests as $request) {
                            if ($request->getStatus() == APPROVED && $request->getLoanType() == $lKey) {
                                $array = $this->utils->reqToArray($request);
                                $array['fecha_edo'] = $lastLoan->fecha_edo;
                                $array['saldo_edo'] = $lastLoan->saldo_edo;
                                $array['saldo_actual'] = $lastLoan->saldo_actual;
                                if ($array['saldo_actual'] <= 0) {
                                    $array['mensualidad'] = '------';
                                } else {
                                    $array['mensualidad'] = $lastLoan->otorg_cuota;
                                }
                                // Add this array to result.
                                array_push($result, $array);
                                break;
                            }
                        }
                    }
                }
                return $result;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getRequestByDate($from, $to, $uid) {
        try {
            $em = $this->doctrine->em;
            $query = $em->createQuery('SELECT t FROM \Entity\Request t WHERE t.creationDate BETWEEN ?1 AND ?2');
            $query->setParameter(1, $from);
            $query->setParameter(2, $to);
            $requests = $query->getResult();
            $result = array();
            foreach ($requests as $request) {
                if ($request->getUserOwner()->getId() != $uid)
                    continue;
                // Add this request to result
                array_push($result, $this->utils->reqToArray($request));
            }
            $interval = $from->diff($to);
            $days = $interval->format("%a");
            if (empty($result)) {
                if ($days > 0) {
                    throw new Exception("No se han encontrado solicitudes para el rango de fechas especificado");
                } else {
                    throw new Exception ("No se han encontrado solicitudes para la fecha especificada");
                }
            } else {
                return $result;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getRequestByStatus($status, $uid) {
        try {
            $em = $this->doctrine->em;
            $requestsRepo = $em->getRepository('\Entity\Request');
            $requests = $requestsRepo->findBy(array("status" => $status));
            $result = array();
            foreach ($requests as $request) {
                if ($request->getUserOwner()->getId() != $uid)
                    continue;
                // Add this request to result
                array_push($result, $this->utils->reqToArray($request));
            }
            if (empty($result)) {
                throw new Exception("No se han encontrado solicitudes con estatus " . $status);
            } else {
                return $result;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getRequestByType($loanType, $uid) {
        try {
            $em = $this->doctrine->em;
            $requestsRepo = $em->getRepository('\Entity\Request');
            $requests = $requestsRepo->findBy(array("loanType" => $loanType));
            $result = array();
            foreach ($requests as $request) {
                if ($request->getUserOwner()->getId() != $uid)
                    continue;
                // Add this request to result
                array_push($result, $this->utils->reqToArray($request));
            }
            if (empty($result)) {
                throw new Exception("No se han encontrado solicitudes del tipo " . $this->loanTypes[$loanType]->DescripcionDelPrestamo);
            } else {
                return $result;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getOpenedRequests($uid) {
        try {
            $em = $this->doctrine->em;
            $requestsRepo = $em->getRepository('\Entity\Request');
            $statuses = $this->utils->getAdditionalStatuses();
            array_push($statuses, RECEIVED);
            $requests = $requestsRepo->findBy(array("status" => $statuses));
            $result = array();
            foreach ($requests as $request) {
                if ($request->getUserOwner()->getId() != $uid)
                    continue;
                // Add this request to result
                array_push($result, $this->utils->reqToArray($request));
            }
            if (empty($result)) {
                throw new Exception("No se han encontrado solicitudes abiertas.");
            } else {
                return $result;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getUserEditableRequests($uid) {
        $editables = array();
        try {
            $em = $this->doctrine->em;
            $user = $em->find('\Entity\User', $uid);
            if ($user === null) {
                throw new Exception("El usuario " . $uid . " no se encuentra en la base de datos");
            } else {
                $requests = $user->getRequests();
                // Re-order requests from newest to oldest.
                $requests = array_reverse($requests->getValues());
                foreach ($requests as $rKey => $request) {
                    if ($request->getValidationDate() !== null) continue;
                    $req = $this->utils->reqToArray($request);
                    // Add this request obj to editable requests array.
                    array_push($editables, $req);
                }
                return $editables;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Obtains the ID of the open request with a specified concept.
     *
     * @param $uid - user's id.
     * @param $concept - request's concept.
     * @return int id of the opened request. Null if no opened request is found.
     * @throws Exception
     */
    public function getUserOpenedRequest($uid, $concept) {
        try {
            $em = $this->doctrine->em;
            $user = $em->find('\Entity\User', $uid);
            if ($user === null) {
                throw new Exception("El usuario " . $uid . " no se encuentra en la base de datos");
            } else {
                $requests = $user->getRequests();
                foreach ($requests as $rKey => $request) {
                    // Look for only a specific type of requests.
                    if ($request->getLoanType() != $concept) continue;
                    // If request is opened, stop searching and send result.
                    if ($request->getStatus() != APPROVED && $request->getStatus() != REJECTED) {
                        return $request->getId();
                    }
                }
                return null;
            }
        } catch (Exception $e) {
            throw $e;
        }
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
            if (!$this->isRequestValidated($request) || $this->isRequestClosed($request)) {
                // request must be validated & not yet closed.
                $result['message'] = 'Esta solicitud no puede ser modificada.';
            } else if ($doc->getType() != ADDITIONAL) {
                $result['message'] = 'Este documento no puede ser eliminado.';
            } else {
                if ($doc->getStorage() == REMOTE) {
                    // Delete document from drive
                    $parsed = explode('.', $doc->getLpath());
                    // [0] = userId, [1] = file uuid, [2] = filename, [3] = file extension
                    $this->driveModel->deleteDocument($parsed[1]);
                } else {
                    // Delete document from local storage.
                    unlink(DropPath . $data['lpath']);
                }
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
                $result = $this->utils->reqToArray($request);
                $this->load->model('emailModel', 'email');
                $this->email->sendRequestUpdateEmail(
                    $request->getId(),
                    $this->loanTypes[$request->getLoanType()]->DescripcionDelPrestamo,
                    $changes
                );
                // Persist the changes in database.
                $em->flush();
                return $result;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function downloadDocument () {
        $em = $this->doctrine->em;
        $doc = $em->find('\Entity\Document', $this->input->get('doc'));
        // [0] = userId, [1] = file uuid, [2] = filename, [3] = file extension
        $parsed = explode('.', $doc->getLpath());
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
            if ($doc->getStorage() == REMOTE) {
                $contents = $this->driveModel->getDocumentContents($parsed[1]);
                echo $contents;
            } else {
                readfile(DropPath . $doc->getLpath());
            }
        }
    }

    public function downloadAllDocuments () {
        $em = $this->doctrine->em;
        $docs = (json_decode($this->input->get('docs')));
        // Create the ZIP
        $zipname = time() . ".zip";
        $zip = new ZipArchive;
        $zip->open($zipname, ZipArchive::CREATE);
        foreach ($docs as $docId) {
            $doc = $em->find('\Entity\Document', $docId);
            $tmp = explode('.', $doc->getLpath());
            // [0] = userId, [1] = file uuid, [2] = filename, [3] = file extension
            if ($tmp[0] == $this->session->id || $this->session->type != APPLICANT) {
                // applicants are not allowed to download documents that are not their own.
                $filename = $tmp[2] . "." . $tmp[3];
                if ($doc->getStorage() == REMOTE) {
                    $contents = $this->driveModel->getDocumentContents($tmp[1]);
                    $zip->addFromString(basename($filename),  $contents);
                } else {
                    $zip->addFromString(basename($filename),  file_get_contents(DropPath . $doc->getLpath()));
                }
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

    public function deleteRequestUI() {
        try {
            $data = json_decode($this->input->raw_input_stream, true);
            $em = $this->doctrine->em;
            $request = $em->find('\Entity\Request', $data['id']);
            if ($this->isRequestValidated($request) || $this->isRequestClosed($request)) {
                throw new Exception('Esta solicitud no puede ser eliminada.');
            } else {
                // Must delete all documents belonging to this request first
                $docs = $request->getDocuments();
                foreach($docs as $doc) {
                    if ($doc->getStorage() == REMOTE) {
                        // Delete document from drive
                        $parsed = explode('.', $doc->getLpath());
                        // [0] = userId, [1] = file uuid, [2] = filename, [3] = file extension
                        $this->driveModel->deleteDocument($parsed[1]);
                    } else {
                        // Delete document from local storage.
                        unlink(DropPath . $doc->getLpath());
                    }
                }
                // Now we can remove the current request (and docs on cascade)
                $em->remove($request);
                // Persist the changes in database.
                $em->flush();
            }
        } catch (Exception $e) {
            throw $e;
        }
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
        $data['loanTypeString'] = $this->loanTypes[$data['loanType']]->DescripcionDelPrestamo;
        $data['paymentFee'] = $this->utils->calculatePaymentFee($data['reqAmount'],
                                                                $data['due'],
                                                                $this->loanTypes[$data['loanType']]->InteresAnual);
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

    // Helper function that adds a set of additional docs to a request in database & returns an html string with
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
                        $doc->setType(ADDITIONAL);
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
            $span = $em->getRepository('\Entity\Config')->findOneBy(array('key' => 'SPAN' . $loanType))->getValue();

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
            return ($request->getStatus() == APPROVED ||
                    $request->getStatus() == REJECTED ||
                    $request->getStatus() == PRE_APPROVED);
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
     * Adds the granting date when request is approved. FOR TESTING PURPOSES ONLY!
     *
     * @param $request - request Entity.
     * @throws Exception
     */
    public function addGrantingDate($request) {
        try {
            $fee = round($this->utils->calculatePaymentFee(
                $request->getApprovedAmount(),
                $request->getPaymentDue(),
                $this->loanTypes[$request->getLoanType()]->InteresAnual
            ), 2);
            $newData = array(
                'cedula' => $request->getUserOwner()->getId(),
                'concepto' => $request->getLoanType(),
                'fecha_edo' => $request->getCreationDate()->format('d/m/Y'),
                'saldo_edo' => $fee * intval($request->getPaymentDue(), 10),
                'saldo_actual' => $fee * intval($request->getPaymentDue(), 10),
                'otorg_fecha' => (new DateTime('now', new DateTimeZone('America/Barbados')))->format('d/m/Y'),
                'otorg_monto' => $request->getApprovedAmount(),
                'otorg_inter' => $this->loanTypes[$request->getLoanType()]->InteresAnual,
                'otorg_plazo' => $request->getPaymentDue(),
                'otorg_cuota' => $fee
            );
            $this->ipapedi_db = $this->load->database('ipapedi_db', true);
            $this->ipapedi_db->from('db_dt_prestamos');
            $this->ipapedi_db->where('cedula', $request->getUserOwner()->getId());
            $this->ipapedi_db->where('concepto', $request->getLoanType());
            // get last granting date for corresponding request type.
            $query = $this->ipapedi_db->order_by('otorg_fecha',"desc")->get();
            if (empty($query->result())) {
                $this->ipapedi_db->insert('db_dt_prestamos', $newData);
            } else {
                $this->ipapedi_db->where('cedula', $request->getUserOwner()->getId());
                $this->ipapedi_db->where('concepto', $request->getLoanType());
                $this->ipapedi_db->update('db_dt_prestamos', $newData);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }


    public function shouldApproveRequest($rid) {
        try {
            $this->ipapedi_db = $this->load->database('ipapedi_db', true);
            $em = $this->doctrine->em;
            $request = $em->find('\Entity\Request', $rid);
            $this->ipapedi_db->select('*');
            $this->ipapedi_db->from('db_dt_prestamos');
            $this->ipapedi_db->where('cedula', $request->getUserOwner()->getId());
            $this->ipapedi_db->where('concepto', $request->getLoanType());
            // get last granting date for corresponding request type.
            $query = $this->ipapedi_db->order_by('otorg_fecha',"desc")->get();
            if (empty($query->result())) {
                // Still no new entry.
                return false;
            } else {
                $granting = date_create_from_format('d/m/Y', $query->result()[0]->otorg_fecha);
                if (!$granting) {
                    // No granting date found in most recent granting entry. This means last loan request was rejected.
                    return false;
                } else {
                    $creationDate = $request->getCreationDate();
                    // If creation date is older than last granting date, it means request approval was updated.
                    // Go ahead and allow approval.
                    return $creationDate <= $granting;
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function approveRequest ($rid) {
        try {
            $loanTypes = $this->configModel->getLoanTypes();
            $em = $this->doctrine->em;
            $request = $em->find('\Entity\Request', $rid);
            // Register History
            $history = new \Entity\History();
            $history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
            $history->setUserResponsible($this->users->getSystemGeneratedUser());
            $history->setTitle($this->utils->getHistoryActionCode('closure'));
            $history->setOrigin($request);
            $request->addHistory($history);
            // Register it's corresponding actions
            $history->setTitle($this->utils->getHistoryActionCode('closure'));
            $action = new \Entity\HistoryAction();
            $action->setSummary("Cierre de solicitud.");
            $action->setDetail("Nuevo estatus: " . APPROVED);
            $action->setBelongingHistory($history);
            $history->addAction($action);
            $em->persist($action);
            $changes = "<li>Cambio de estatus: <s>" . $request->getStatus() .
                       "</s> " . APPROVED . "." . "</li>";
            $changes = $changes .
                       '<br/><div>El préstamo solicitado ha sido abonado. Puede entrar en IPAPEDI en línea ' .
                       'para ver los cambios realizados en su Estado de Cuenta.</div>';
            $em->persist($history);
            $request->setStatus(APPROVED);
            $em->merge($request);
            $this->load->model('emailModel', 'email');
            $this->email->sendRequestUpdateEmail(
                $request->getId(),
                $loanTypes[$request->getLoanType()]->DescripcionDelPrestamo,
                $changes
            );
            $em->flush();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Obtains last loan request of specified type registered in ipapedi_db.
     *
     * @param $uid - applicant's id.
     * @param $concept - request concept.
     * @return array with loan info. null otherwise.
     * @throws Exception
     */
    public function getLastLoanInfo($uid, $concept) {
        try {
            $this->ipapedi_db = $this->load->database('ipapedi_db', true);
            $this->ipapedi_db->select('*');
            $this->ipapedi_db->from('db_dt_prestamos');
            $this->ipapedi_db->where('cedula', $uid);
            $this->ipapedi_db->where('concepto', $concept);
            $query = $this->ipapedi_db->order_by('otorg_fecha',"desc")->get();
            if (empty($query->result())) {
                // User's first request.
                return null;
            } else {
                return $query->result()[0];
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}