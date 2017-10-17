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
                                    $array['mensualidad'] = null;
                                } else {
                                    $array['mensualidad'] =
                                        $this->isDbPrestamos($request->getLoanType()) ? $lastLoan->otorg_cuota : null;
                                }
                                // Add this array to result.
                                array_push($result, $array);
                                break;
                            }
                        }
                    }
                }
                if (empty($result)) {
                    throw new Exception('No se encontraron solicitudes activas');
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
            array_push($statuses, PRE_APPROVED);
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
                if (empty($editables)) {
                    throw new Exception('No se han encontrado solicitudes editables');
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

    /**
     * Loads additional deductions corresponding to the specified request.
     *
     * @param uid - request owner's user id.
     * @param rid - request's id.
     * @param concept - request's concept.
     * @return mixed - user's active debts with their corresponding deduction.
     * @throws Exception
     */
    public function loadAdditionalDeductions ($uid, $rid, $concept) {
        try {
            $result = $this->getAllActiveLoans($uid);
            $this->filterAdditionalDeductions($result, $concept);
            $em = $this->doctrine->em;
            if ($rid) {
                $request = $em->find('\Entity\Request', $rid);
                $deductions = $request->getAdditionalDeductions();
                if ($deductions !== null) {
                    foreach ($deductions as $deduction) {
                        foreach ($result as &$pmo) {
                            if (intval($pmo['concepto'], 10) == $deduction->getConcept()) {
                                $pmo['amount'] = $deduction->getAmount();
                            }
                        }
                    }
                }
            }
            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Filters some loans from additional deductions that should not appear
     *
     * @param $activeLoans - active loans (still with pending debt).
     * @param $concept - request type.
     */
    private function filterAdditionalDeductions(&$activeLoans, $concept) {
        switch (intval($concept, 10)) {
            case PERSONAL_LOAN:
                foreach ($activeLoans as $key => $pmo) {
                    // Do not show personal loan or medical expenses (this last one is deducted automatically)
                    if ($pmo['concepto'] == PERSONAL_LOAN || $pmo['concepto'] == MEDICAL_EXPENSES) {
                        unset($activeLoans[$key]);
                    }
                }
                break;
            case CASH_VOUCHER:
                foreach ($activeLoans as $key => $pmootro) {
                    if ($pmootro['concepto'] == CASH_VOUCHER) {
                        // Do not show cash vouchers debt
                        unset($activeLoans[$key]);
                    }
                }
                break;
        }
    }

    /**
     * Obtains all active loans from the specified user.
     *
     * @param $uid - user's id.
     * @return mixed - all user's active loans and other loans, with their corresponding description.
     * @throws Exception
     */
    public function getAllActiveLoans ($uid) {
        try {
            $result = array();
            $this->ipapedi_db = $this->load->database('ipapedi_db', true);

            $this->ipapedi_db->select('*');
            $this->ipapedi_db->from('db_dt_prestamos');
            $this->ipapedi_db->where('cedula', $uid);
            $this->ipapedi_db->where('saldo_actual > ', 0);
            $this->ipapedi_db->join('db_ds_conceptos', 'db_dt_prestamos.concepto = db_ds_conceptos.codigo');
            $pmos = $this->ipapedi_db->get()->result_array();
            foreach ($pmos as &$pmo) {
                $pmo['amount'] = null;
                array_push($result, $pmo);
            }

            $this->ipapedi_db->select('*');
            $this->ipapedi_db->from('db_dt_prestamosotros');
            $this->ipapedi_db->where('cedula', $uid);
            $this->ipapedi_db->where('saldo_actual > ', 0);
            $this->ipapedi_db->join('db_ds_conceptos', 'db_dt_prestamosotros.concepto = db_ds_conceptos.codigo');
            $pmosotros = $this->ipapedi_db->get()->result_array();
            foreach ($pmosotros as &$pmo) {
                $pmo['amount'] = null;
                array_push($result, $pmo);
            }
            return $result;
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
                throw new Exception('Esta solicitud no puede ser modificada.');
            } else if ($doc->getType() != ADDITIONAL) {
                throw new Exception('Este documento no puede ser eliminado.');
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

    public function validateCashVoucherCreation ($data, $editMode) {
        try {
            $em = $this->doctrine->em;
            $loanTypes = $this->configModel->getLoanTypes();
            $userData = $this->users->getPersonalData($data['userId']);
            $terms = $this->utils->extractLoanTerms($loanTypes[$data['loanType']]);
            if ($userData->concurrencia > 40) {
                throw new Exception("Concurrencia muy alta (mayor a 40%)");
            } else if (!$editMode && !$this->utils->checkPreviousRequests($data['userId'], $data['loanType'])) {
                // Another request of same type is already open.
                throw new Exception('Usted ya posee una solicitud del tipo ' .
                                     $loanTypes[$data['loanType']]->description . ' en transcurso.');
            } else if ($this->requests->getSpanLeft($data['userId'], $data['loanType']) > 0) {
                // Span between requests of same type not yet through.
                $span = $em->getRepository('\Entity\Config')->findOneBy(array('key' => 'SPAN' . $data['loanType']))->getValue();
                throw new Exception("No ha" . ($span == 1 ? "" : "n") .
                                     " transcurrido al menos " . $span . ($span == 1 ? " mes " : " meses ") .
                                     "desde su última otorgación de préstamo del tipo: " .
                                     $loanTypes[$data['loanType']]->DescripcionDelPrestamo);
            } else if (!$this->users->isReqAmountValid($data['reqAmount'], $data['loanType'], $userData)) {
                throw new Exception('Monto solicitado no válido.');
            } else if (!in_array($data['due'], $terms)) {
                throw new Exception($result['message'] = 'Plazo de pago no válido.');
            } else if (!$this->utils->isRequestTypeValid($loanTypes, $data['loanType'])) {
                throw new Exception('Tipo de préstamo inválido.');
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function validatePersonalLoanCreation ($data, $editMode) {
        try {
            $em = $this->doctrine->em;
            $loanTypes = $this->configModel->getLoanTypes();
            $userData = $this->users->getPersonalData($data['userId']);
            $userContribution = $this->users->getContributionData($data['userId']);
            $lastLoan = $this->requests->getLastLoanInfo($data['userId'], $data['loanType']);
            $allLoans = $this->requests->getAllLoansInfo($data['userId']);
            $newConcurrence = $this->users->calculateNewConcurrence(
                $allLoans,
                $userData->sueldo,
                $this->utils->calculatePaymentFee($data['reqAmount'],$data['due'],$loanTypes[$data['loanType']]->InteresAnual)
            );
            $diff = $this->utils->getDateInterval(
                new DateTime('now', new DateTimeZone('America/Barbados')),
                date_create_from_format('d/m/Y', $userData->ingreso)
            );
            $terms = $this->utils->extractLoanTerms($loanTypes[$data['loanType']]);
            if ($userData->concurrencia > 40) {
                throw new Exception("Concurrencia muy alta (mayor a 40%)");
            } else if ($newConcurrence > 40) {
                $maxPaymentFee = number_format($this->users->calculateMaxFeeByConcurrence($allLoans, $userData->sueldo), 2);
                $maxTerms = $this->users->calculateMaxTermsByConcurrence($allLoans, $userData->sueldo,
                                                                         $data['reqAmount'], $terms, $data['loanType']);
                throw new Exception ("Su concurrencia con el nuevo préstamo excede el 40%. Su concurrencia " .
                                     "actual (" . $userData->concurrencia ."%) le permite una cuota máxima de Bs. " .
                                     $maxPaymentFee .
                                     ", por lo que debe elegir como mínimo un plazo de " . $maxTerms . " meses o " .
                                     "solicitar un monto menor.");
            } else if (($diff['months'] + ($diff['years'] * 12) < 6)) {
                throw new Exception("Deben transcurrir seis meses desde su fecha de ingreso.");
            } else if (!$editMode && !$this->utils->checkPreviousRequests($data['userId'], $data['loanType'])) {
                // Another request of same type is already open.
                throw new Exception ('Usted ya posee una solicitud del tipo ' .
                                     $loanTypes[$data['loanType']]->DescripcionDelPrestamo . ' en transcurso.');
            } else if ($this->requests->getSpanLeft($data['userId'], $data['loanType']) > 0 &&
                       ($lastLoan != null && $lastLoan->saldo_edo > 0)) {
                // Span between requests of same type not yet through and debts still not paid.
                $span = $em->getRepository('\Entity\Config')->findOneBy(array('key' => 'SPAN' . $data['loanType']))->getValue();
                throw new Exception("No ha" . ($span == 1 ? "" : "n") .
                                     " transcurrido al menos " . $span . ($span == 1 ? " mes " : " meses ") .
                                     "desde su última otorgación de préstamo del tipo: " .
                                     $loanTypes[$data['loanType']]->DescripcionDelPrestamo);
            } else if (!$this->users->isReqAmountValid($data['reqAmount'], $data['loanType'], $userContribution)) {
                throw new Exception('Monto solicitado no válido.');
            } else if (!in_array($data['due'], $terms)) {
                throw new Exception($result['message'] = 'Plazo de pago no válido.');
            } else if (!$this->utils->isRequestTypeValid($loanTypes, $data['loanType'])) {
                throw new Exception('Tipo de préstamo inválido.');
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function generateRequestDocument ($request) {
        // Generate the document.
        if ($request->getLoanType() == CASH_VOUCHER) {
            $data = $this->getNewCashVoucherDocData($request);
            $html = $this->load->view('templates/docsTemplates/cashVoucher/newRequest', $data, true);
        } else if ($request->getLoanType() == PERSONAL_LOAN) {
            $data = $this->getPersonalLoanDocData($request);
            $html = $this->load->view('templates/docsTemplates/personalLoan/newRequest', $data, true);
        }
        $this->load->library('Pdf');
        $pdf = $this->pdf->load();
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdfFilePath = DropPath . $data['lpath'];
        $pdf->Output($pdfFilePath, 'F'); // save to file
    }

    public function getNewCashVoucherDocData($request) {
        $data['reqAmount'] = $request->getRequestedAmount();
        $data['approvedAmount'] = $request->getApprovedAmount();
        $data['tel'] = $request->getContactNumber();
        $data['email'] = $request->getContactEmail();
        $data['due'] = $request->getPaymentDue();
        $data['userId'] = $request->getUserOwner()->getId();
        $data['loanType'] = $request->getLoanType();
        $data['lpath'] = $request->getDocuments()[0]->getLpath();
        $data['username'] = $request->getUserOwner()->getFirstName() . ' ' . $request->getUserOwner()->getLastName();
        $data['requestId'] = str_pad($request->getId(), 6, '0', STR_PAD_LEFT);
        $data['date'] = new DateTime('now', new DateTimeZone('America/Barbados'));
        $data['amount'] = $request->getStatus() != PRE_APPROVED ? $data['reqAmount'] : $data['approvedAmount'];
        $data['loanTypeString'] = $this->loanTypes[$data['loanType']]->DescripcionDelPrestamo;
        $data['paymentFee'] = $this->utils->calculatePaymentFee($data['amount'],
                                                                $data['due'],
                                                                $this->loanTypes[$data['loanType']]->InteresAnual);
        $data['interest'] = $this->loanTypes[$data['loanType']]->InteresAnual;
        $data['totals'] = $this->calculateTotals($request->getLoanType(), $data);
        return $data;
    }

    public function getPersonalLoanDocData ($request) {
        $data['reqAmount'] = $request->getRequestedAmount();
        $data['approvedAmount'] = $request->getApprovedAmount();
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
        $data['amount'] = $request->getStatus() != PRE_APPROVED ? $data['reqAmount'] : $data['approvedAmount'];
        $data['paymentFee'] = round($this->utils->calculatePaymentFee($data['amount'],
                                                                $data['due'],
                                                                $this->loanTypes[$data['loanType']]->InteresAnual), 2);
        $lastLoan = $this->requests->getLastLoanInfo($data['userId'], PERSONAL_LOAN);
        // Get interests adjustment data.
        $data['daysOfMonth'] = intval(date("t"), 10);
        $approvalDate = new DateTime('now', new DateTimeZone('America/Barbados'));
        $data['lastLoanInterestDays'] = intval($approvalDate->format('d'), 10);
        $data['newLoanInterestDays'] = $data['daysOfMonth'] - $data['lastLoanInterestDays'];
        $data['lastLoanInterestFee'] = ($lastLoan ? $lastLoan->saldo_actual : 0) * 0.01 /
                                       $data['daysOfMonth'] * $data['lastLoanInterestDays'];
        $data['lastLoanFee'] = intval($lastLoan ? $lastLoan->otorg_cuota : 0, 10);
        if ($data['lastLoanFee'] > 0) {
            $data['newLoanInterestFee'] = ($data['amount'] + $data['lastLoanFee']) * 0.01 / $data['daysOfMonth'] * $data['newLoanInterestDays'];
        } else {$data['newLoanInterestFee'] = 0;}
        // Calculate deductions
        $medicalExpenses = $this->requests->getLastLoanInfo($data['userId'], MEDICAL_EXPENSES);
        if ($medicalExpenses != null) {
            $data['medicalContribution'] = 0.2 * $medicalExpenses->saldo_actual;
        } else { $data['medicalContribution'] = 0;}
        $data['lastLoanBalance'] = intval($lastLoan ? $lastLoan->saldo_actual : 0, 10);
        $deductions = $request->getAdditionalDeductions();
        $data['deductionsTotal'] = 0;
        foreach ($deductions as $key => $deduction) {
            $data['deductions'][$key]['description'] = $deduction->getDescription();
            $data['deductions'][$key]['amount'] = $deduction->getAmount();
            $data['deductionsTotal'] += $deduction->getAmount();
        }
        $data['totals'] = $this->calculateTotals($request->getLoanType(), $data);
        return $data;
    }

    private function calculateTotals ($concept, $data) {
        switch (intval($concept, 10)) {
            case CASH_VOUCHER:
                return $this->calculateCashVoucherTotals($data);
            case PERSONAL_LOAN:
                return $this->calculatePersonalLoanTotals($data);
            default:
                return 0;
        }
    }

    private function  calculatePersonalLoanTotals($data) {
        $result = array();
        array_push($result, round($data['amount'] + $data['lastLoanFee'], 2));
        array_push($result, round(end($result) - $data['newLoanInterestFee'], 2));
        array_push($result, round(end($result) - $data['medicalContribution'], 2));
        array_push($result, round(end($result) - $data['lastLoanBalance'], 2));
        array_push($result, round(end($result) - $data['deductionsTotal'], 2));
        return $result;
    }

    private function  calculateCashVoucherTotals($data) {
        $result = array();
        array_push($result, round($data['amount'] - $data['amount'] * $data['interest'] / 100, 2));
        return $result;
    }

    public function generateApprovalDocument ($request, $doc) {
        $data = null;
        // Get extra data for the pdf template.
        if ($request->getLoanType() == CASH_VOUCHER) {
            $data = $this->getNewCashVoucherDocData($request);
            $html = $this->load->view('templates/docsTemplates/cashVoucher/requestApproval', $data, true);
        } else if ($request->getLoanType() == PERSONAL_LOAN) {
            $data = $this->getPersonalLoanDocData($request);
            $html = $this->load->view('templates/docsTemplates/personalLoan/requestApproval', $data, true);
        }
        // Generate the document.
        $this->load->library('Pdf');
        $pdf = $this->pdf->load();
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdfFilePath = DropPath . $doc['lpath'];
        $pdf->Output($pdfFilePath, 'F'); // save to file
        return end($data['totals']);
    }

    // Helper function that adds a set of additional docs to a request in database & returns an html string with
    // registered changes (for email notification).
    public function addDocuments($request, $history, $docs, $isMandatory) {
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
                            $doc->setType($isMandatory ? MANDATORY : ADDITIONAL);
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
                        $doc->setType($isMandatory ? MANDATORY : ADDITIONAL);
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

    /**
     * Determines how many months are still left for applicant to be able to request a specific type of loan again.
     * * HAS TO BE USED FOR VALIDATION ONLY (e.g. creatin, edition & request validation) *
     *
     * @param $uid - applicant's id.
     * @param $loanType - loan's concept.
     * @return int|null
     * @throws Exception
     */
    public function getSpanLeft ($uid, $loanType) {
        try {
            $em = $this->doctrine->em;
            $span = $em->getRepository('\Entity\Config')->findOneBy(array('key' => 'SPAN' . $loanType))->getValue();
            $lastLoan = $this->getLastLoanInfo($uid, $loanType);
            if ($lastLoan == null) {
                // User's first request.
                return 0;
            } else if ($this->isDbPrestamos($loanType)) {
                $granting = date_create_from_format('d/m/Y', $lastLoan->otorg_fecha);
                if (!$granting) {
                    // No granting date found in granting entry. Perhaps it was rejected
                    // Go ahead and allow this request type creation
                    return 0;
                } else {
                    $currentDate = new DateTime('now', new DateTimeZone('America/Barbados'));
                    $diff = $this->utils->getDateInterval($currentDate, $granting);
                    return $span - ($diff['months'] + $diff['years'] * 12);
                }
            } else {
                $lastRequest = $this->getLastRequest($uid, CASH_VOUCHER);
                if ($lastRequest == null || !$lastRequest->getValidationDate()) {
                    // if last Request is null or it is yet to be validated, it means user has not made requests
                    // from this systems yet, so if it's already paid (0 or less balance), he can request.
                    return 0;
                } else {
                    $granting = $lastRequest->getValidationDate();
                    $currentDate = new DateTime('now', new DateTimeZone('America/Barbados'));
                    $diff = $this->utils->getDateInterval($currentDate, $granting);
                    return $span - ($diff['months'] + $diff['years'] * 12);
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function updateDeductions ($request, $deductions, $history) {
        try {
            $em = $this->doctrine->em;
            $originalDeductions = $request->getAdditionalDeductions();
            // For each new deduction, find out whether there is a previous deduction
            foreach ($deductions as $deduction) {
                $found = false;
                foreach ($originalDeductions as $original) {
                    if ($original->getConcept() == intval($deduction['concepto'], 10)) {
                        $found = true;
                        break;
                    }
                }
                if (isset($deduction['amount']) && $deduction['amount'] > 0) {
                    if (!$found) {
                        // If no previous deduction found, create new one.
                        $deductionEntity = new \Entity\AdditionalDeduction();
                        $deductionEntity->setAmount($deduction['amount']);
                        $deductionEntity->setConcept($deduction['concepto']);
                        $deductionEntity->setRequest($request);
                        $deductionEntity->setDescription($deduction['descripcion']);
                        $request->addAdditionalDeduction($deductionEntity);
                        $action = new \Entity\HistoryAction();
                        $action->setSummary("Deducción adicional solicitada");
                        $action->setDetail("Abono de Bs. " . number_format($deduction['amount'], 2) . " para deuda de " .
                            $deduction['descripcion']);
                        $action->setBelongingHistory($history);
                        $history->addAction($action);
                        $em->persist($action);
                        $em->merge($request);
                        $em->persist($deductionEntity);
                    } else if (isset($original) && $original->getAmount() != $deduction['amount']) {
                        // Previous deduction found. Update original deduction amount.
                        $action = new \Entity\HistoryAction();
                        $action->setSummary("Deducción adicional modificada");
                        $action->setDetail("Abono para deuda de " . $deduction['descripcion'] . " cambiado de Bs. "
                                           . number_format($original->getAmount(), 2) .
                                           " a Bs. " . number_format($deduction['amount'], 2));
                        $action->setBelongingHistory($history);
                        $history->addAction($action);
                        $em->persist($action);
                        $original->setAmount($deduction['amount']);
                        $em->merge($original);
                    }
                } else if ($found && isset($original)) {
                    // New amount was set to zero. Remove deduction from request.
                    $action = new \Entity\HistoryAction();
                    $action->setSummary("Deducción adicional cancelada");
                    $action->setDetail("Abono de Bs. " . number_format($original->getAmount(), 2) .
                                       " para deuda de " . $original->getDescription() . " cancelado.");
                    $action->setBelongingHistory($history);
                    $history->addAction($action);
                    $em->persist($action);
                    $request->removeAdditionalDeduction($original);
                    $em->remove($original);
                    $em->merge($request);
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function deleteDeductions($request, $history) {
        try {
            $em = $this->doctrine->em;
            $originalDeductions = $request->getAdditionalDeductions();
            // For each new deduction, find out whether there is a previous deduction
            foreach ($originalDeductions as $deduction) {
                $em->remove($deduction);
            }
            // Set History action for this request's corresponding history
            $action = new \Entity\HistoryAction();
            $action->setSummary("Deducciones adicionales canceladas");
            $action->setBelongingHistory($history);
            $history->addAction($action);
            $em->persist($action);
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
            if ($this->isDbPrestamos($request->getLoanType())) {
                $this->addPrestamosGrantingDate($request);
            } else {
                $this->addPrestamosOtrosGrantingDate($request);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * TEST PURPOSES ONLY: adds db_dt_prestamos entry.
     * @param $request - request entity.
     * @throws Exception
     */
    public function addPrestamosGrantingDate($request) {
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

    /**
     * TEST PURPOSES ONLY: adds db_dt_prestamosotros entry.
     * @param $request - request entity.
     * @throws Exception
     */
    public function addPrestamosOtrosGrantingDate($request) {
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
            );
            $this->ipapedi_db = $this->load->database('ipapedi_db', true);
            $this->ipapedi_db->from('db_dt_prestamosotros');
            $this->ipapedi_db->where('cedula', $request->getUserOwner()->getId());
            $this->ipapedi_db->where('concepto', $request->getLoanType());
            $query = $this->ipapedi_db->get();
            if (empty($query->result())) {
                $this->ipapedi_db->insert('db_dt_prestamosotros', $newData);
            } else {
                $this->ipapedi_db->where('cedula', $request->getUserOwner()->getId());
                $this->ipapedi_db->where('concepto', $request->getLoanType());
                $this->ipapedi_db->update('db_dt_prestamosotros', $newData);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Determines whether specified loan type belongs to db_dt_prestamos or db_dt_prestamosotros.
     *
     * @param $concept - loan type.
     * @return bool
     */
    public function isDbPrestamos($concept) {
        return $concept == PERSONAL_LOAN || $concept == MEDICAL_EXPENSES;
    }


    public function shouldApproveRequest($rid) {
        try {
            $this->ipapedi_db = $this->load->database('ipapedi_db', true);
            $em = $this->doctrine->em;
            $request = $em->find('\Entity\Request', $rid);
            $lastLoan = $this->getLastLoanInfo($request->getUserOwner()->getId(), $request->getLoanType());
            if ($lastLoan == null) {
                // Still no new entry.
                return false;
            } else {
                if (!$this->isDbPrestamos($request->getLoanType())) {
                    // Cash vouchers are supposed to be available if no debt is pending. If there is any debt pending,
                    // it means that new cash voucher request is now registered.
                    return $lastLoan->saldo_actual > 0;
                }
                $granting = date_create_from_format('d/m/Y - h:i:sa', $lastLoan->otorg_fecha . ' - 11:59:59pm');
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
                       '<br/><div>El préstamo solicitado ha sido abonado.<br/>' .
                       'Puede entrar en IPAPEDI en línea para ver los cambios realizados en su Estado de Cuenta.</div>';
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
            if ($this->isDbPrestamos($concept)) {
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
            } else {
                $this->ipapedi_db = $this->load->database('ipapedi_db', true);
                $this->ipapedi_db->select('*');
                $this->ipapedi_db->from('db_dt_prestamosotros');
                $this->ipapedi_db->where('cedula', $uid);
                $this->ipapedi_db->where('concepto', $concept);
                $query = $this->ipapedi_db->get();
                if (empty($query->result())) {
                    // User's first request.
                    return null;
                } else {
                    return $query->result()[0];
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Obtains all loan request of specified type registered in ipapedi_db.
     *
     * @param $uid - applicant's id.
     * @return array with all loan info. null if no requests found.
     * @throws Exception
     */
    public function getAllLoansInfo($uid) {
        try {
            $this->ipapedi_db = $this->load->database('ipapedi_db', true);
            $this->ipapedi_db->select('*');
            $this->ipapedi_db->from('db_dt_prestamos');
            $this->ipapedi_db->where('cedula', $uid);
            $query = $this->ipapedi_db->get();
            if (empty($query->result())) {
                return [];
            } else {
                return $query->result();
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getLastRequest($uid, $concept) {
        try {
            $em = $this->doctrine->em;
            $owner = $em->find('\Entity\User', $uid);
            $requests = $em->getRepository('\Entity\Request')->findBy(
                array('userOwner' => $owner, 'loanType' => $concept),
                array('creationDate' => 'DESC')
            );
            return empty($requests) ? null : $requests[0];
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getAvailabilityData($uid, $concept) {
        try {
            switch (intval($concept, 10)) {
                case CASH_VOUCHER:
                    $result = $this->requests->getCashVoucherAvailabilityData($uid);
                    break;
                case PERSONAL_LOAN:
                    $result = $this->requests->getPersonalLoanAvailabilityData($uid);
                    break;
                default:
                    throw new Exception ('Tipo de préstamo no disponible');
            }
        } catch (Exception $e) {
            throw $e;
        }
        return $result;
    }

    /**
     * Gets a user's availability data (i.e. conditions for creating new request of cash voucher). This is:
     * 1. Concurrence.
     * 2. Max possible amount of money to request.
     * 3. Request frequency constrain.
     *
     * @param $uid - user's id.
     * @return mixed - cash voucher's availability data.
     * @throws Exception
     */
    public function getCashVoucherAvailabilityData($uid) {
        try {
            $em = $this->doctrine->em;
            $span = $this->configModel->getRequestSpan(CASH_VOUCHER);
            $result['granting']['span'] = $span;
            $config = $em->getRepository('\Entity\Config');
            $lastRequest = $this->getLastRequest($uid, CASH_VOUCHER);
            if ($lastRequest == null) {
                // Seems like this is their first request. Grant permission to create!
                $result['granting']['allow'] = true;
            } else {
                $granting = $lastRequest->getValidationDate();
                if (!$granting) {
                    // This would mean last request is open (not validated).
                    $result['granting']['allow'] = false;
                } else {
                    $lastLoan = $this->getLastLoanInfo($uid, CASH_VOUCHER);
                    $currentDate = new DateTime('now', new DateTimeZone('America/Barbados'));
                    $diff = $this->utils->getDateInterval($currentDate, $granting);
                    // Tell user when will he be able to request again in case time constrain is not over.
                    $result['granting']['allow'] =
                        // Allow if time constrain is over OR if all the debt was paid.
                        ($diff['months'] + ($diff['years'] * 12) >= $span) || ($lastLoan != null && $lastLoan->saldo_actual <= 0);
                    $result['granting']['dateAvailable'] =
                        $granting->modify('+' . $span . ' month')->format('d/m/Y');
                }
            }
            $userData = $this->users->getPersonalData($uid);
            if ($userData == null) {
                // User info not found! This should never happen. Nevertheless, throw error.
                throw new Exception("Parece que su información personal aún no ha sido ingresada en nuestro sistema.");
            } else {
                $result['concurrence'] = $userData->concurrencia;
            }
            // Get max req amount
            $percentage = $config->findOneBy(array('key' => 'MAX_AMOUNT' . CASH_VOUCHER))->getValue();
            $result['salary'] = $userData->sueldo;
            $result['maxReqAmount'] = $userData->sueldo * $percentage / 100;
            $result['percentage'] = $percentage;
            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Gets a user's availability data (i.e. conditions for creating new request of personal loan). This is:
     * 1. Concurrence.
     * 2. Max possible amount of money to request.
     * 3. Request frequency constrain.
     * 4. Whether user has at least six months old in the system.
     *
     * @param $uid - user's id.
     * @return mixed - personal loan's availability data.
     * @throws Exception
     */
    public function getPersonalLoanAvailabilityData($uid) {
        try {
            $span = $this->configModel->getRequestSpan(PERSONAL_LOAN);
            $result['granting']['span'] = $span;
            $lastLoan = $this->requests->getLastLoanInfo($uid, PERSONAL_LOAN);

            $result['lastLoanBalance'] = 0;
            $result['lastLoanFee'] = 0;
            $result['medicalDebt'] = 0;
            $result['daysOfMonth'] = intval(date("t"), 10);
            $result['lastLoanInterestDays'] = 0;
            $result['newLoanInterestDays'] = 0;
            $result['lastLoanInterestFee'] = 0;
            if ($lastLoan == null) {
                // Seems like this is their first request. Grant permission to create!
                $result['granting']['allow'] = true;
            } else {
                $granting = date_create_from_format('d/m/Y', $lastLoan->otorg_fecha);
                if (!$granting) {
                    // No granting date found in most recent granting entry. Perhaps it was rejected.
                    // Go ahead and allow this request type creation
                    $result['granting']['allow'] = true;
                } else {
                    $currentDate = new DateTime('now', new DateTimeZone('America/Barbados'));
                    $diff = $this->utils->getDateInterval($currentDate, $granting);
                    $result['granting']['allow'] =
                        // Allow if time constrain is over OR if all the debt was paid.
                        ($diff['months'] + ($diff['years'] * 12) >= $span) || ($lastLoan->saldo_actual <= 0);
                    // Tell user when will he be able to request again in case time constrain is not over.
                    $result['granting']['dateAvailable'] = $granting->modify('+' . $span . ' month')->format('d/m/Y');
                    // Get last loan info
                    $result['lastLoanBalance'] = round(intval($lastLoan->saldo_actual, 10), 10);
                    $result['lastLoanFee'] = round(intval($lastLoan->otorg_cuota, 10), 10);
                    $result['lastLoanGrantingDate'] = $lastLoan->otorg_fecha;
                    // Get interests adjustment data.
                    $result['lastLoanInterestDays'] = intval($currentDate->format('d'), 10);
                    $result['newLoanInterestDays'] = $result['daysOfMonth'] - $result['lastLoanInterestDays'];
                    $result['lastLoanInterestFee'] = round($lastLoan->saldo_actual * 0.01 / $result['daysOfMonth'] * $result['lastLoanInterestDays'], 2);
                }
            }
            $userData = $this->users->getPersonalData($uid);
            if ($userData == null) {
                // User info not found! This should never happen. Nevertheless, throw error.
                throw new Exception("Parece que su información personal aún no ha sido ingresada en nuestro sistema.");
            } else {
                $result['concurrence'] = $userData->concurrencia;
                // Applicant must be 6 months old to request personal loans.
                $admissionDate = date_create_from_format('d/m/Y', $userData->ingreso);
                if (!$admissionDate) {
                    // People without admission date seem to be extremely old in ipapedi...
                    // So go ahead and allow creation.
                    $result['sixMonthsOld'] = true;
                    $result['admissionDate'] = '01/01/1963';
                } else {
                    $today = new DateTime('now', new DateTimeZone('America/Barbados'));
                    $diff = $this->utils->getDateInterval($today, $admissionDate);
                    $result['sixMonthsOld'] = $diff['months'] + ($diff['years'] * 12) >= 6;
                    $result['admissionDate'] = $userData->ingreso;
                    $result['dateAvailable'] = $admissionDate->modify('+6 month')->format('d/m/Y');
                }
            }
            $userContribution = $this->users->getContributionData($uid);
            if ($userContribution == null) {
                // Contribution info not found! This should never happen. Nevertheless, throw error.
                throw new Exception("Parece que aún no posee ningún tipo de aporte.");
            } else {
                $result['contribution'] = $userContribution->p_saldo_disp + $userContribution->u_saldo_disp;
            }
            $medicalExpenses = $this->requests->getLastLoanInfo($uid, MEDICAL_EXPENSES);
            if ($medicalExpenses != null) {
                $result['medicalDebt'] = round($medicalExpenses->saldo_actual, 2);
            }
            $result['maxReqAmount'] = Round($result['contribution'] * 0.8, 2);
            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
