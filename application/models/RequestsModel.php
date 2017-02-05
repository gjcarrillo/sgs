<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

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
            \ChromePhp::log($e);
            $result['message'] = "error";
        }
        return json_encode($result);
    }

    public function deleteDocument () {
        try {
            $data = json_decode($this->input->raw_input_stream, true);
            $em = $this->doctrine->em;
            // Delete the document from the server.
            unlink(DropPath . $data['lpath']);
            // Get the specified doc entity
            $doc = $em->find('\Entity\Document', $data['id']);
            // Get it's request.
            $request = $doc->getBelongingRequest();
            // Remove this doc from it's request entity
            $request->removeDocument($doc);
            // Register History
            $history = new \Entity\History();
            $history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
            $history->setUserResponsable($this->session->name . ' ' . $this->session->lastName);
            // 5 = Elimination
            $history->setTitle(5);
            $history->setOrigin($request);
            $request->addHistory($history);
            $em->merge($request);
            // Register it's corresponding action
            $action = new \Entity\HistoryAction();
            $action->setSummary("Eliminación del documento '" . $doc->getName() . "'.");
            $action->setBelongingHistory($history);
            $history->addAction($action);
            $em->persist($action);
            $em->persist($history);
            // Delete the document.
            $em->remove($doc);
            // Persist the changes in database.
            $em->flush();
            $result['message'] = "success";
        } catch (Exception $e) {
            $result['message'] = "error";
            \ChromePhp::log($e);
        }
        return json_encode($result);
    }

    public function downloadDocument () {
        // [0] = userId, [1] = request type, [2] = loan number, [3] = filename, [4] = file extension
        $parsed = explode('.', $this->input->get('lpath'));
        // Get the Id of the document's owner.
        $userOwner = $parsed[0];
        if ($userOwner != $this->session->id && $this->session->type == APPLICANT) {
            // applicants are not allowed to download documents that are not their own.
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
            // [0] = userId, [1] = request type, [2] = loan number, [3] = filename, [4] = file extension
            if ($tmp[0] == $this->session->id || $this->session->type != APPLICANT) {
                // applicants are not allowed to download documents that are not their own.
                $filename = $tmp[3] . "." . $tmp[4];
                \ChromePhp::log($filename, DropPath . $doc);
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
            $decoded = JWT::decode($urlDecoded, SECRET_KEY);

            $request = $em->find('\Entity\Request', $decoded->rid);
            if ($request == null) {
                $result['message'] = 'No existe dicha solicitud.';
            } else if ($request->getUserOwner()->getId() != $_SESSION['id']) {
                $result['message'] = 'Esta solicitud no le pertenece.';
            } else if ($request->getValidationDate() !== null) {
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
            $result['message'] = "Token inválido.";
            \ChromePhp::log($e);
        }
        return json_encode($result);
    }

    public function deleteRequestUI() {
        try {
            $data = json_decode($this->input->raw_input_stream, true);
            $em = $this->doctrine->em;
            $request = $em->find('\Entity\Request', $data['id']);
            if ($request->getValidationDate() !== null) {
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
            $result['message'] = null;
            \ChromePhp::log($e);
        }
        return json_encode($result);
    }
}