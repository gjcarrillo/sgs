<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class UserHomeController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
        $this->load->view('home/userHome');
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
				// Get user's max request amount
				$this->db->select('*');
				$this->db->from('db_dt_personales');
				$this->db->where('cedula', $_GET['fetchId']);
				$query = $this->db->get();
				if (!empty($query->result())) {
					$data = $query->result()[0];
					// use Data to get user info in the formula
					$result['maxReqAmount'] = 250000;
				} else {
					$result['maxReqAmount'] = 100000;
				}
                $em = $this->doctrine->em;
                $user = $em->find('\Entity\User', $_GET['fetchId']);
                $requests = $user->getRequests();
                if ($requests->isEmpty()) {
                    $result['message'] = "Usted aún no posee solicitudes";
                } else {
                    $requests = array_reverse($requests->getValues());
                    foreach ($requests as $rKey => $request) {
                        $result['requests'][$rKey]['id'] = $request->getId();
                        $result['requests'][$rKey]['creationDate'] = $request->getCreationDate()->format('d/m/y');
                        $result['requests'][$rKey]['comment'] = $request->getComment();
                        $result['requests'][$rKey]['reqAmount'] = $request->getRequestedAmount();
                        $result['requests'][$rKey]['approvedAmount'] = $request->getApprovedAmount();
                        $result['requests'][$rKey]['reunion'] = $request->getReunion();
                        $result['requests'][$rKey]['status'] = $request->getStatusByText();
						$result['requests'][$rKey]['type'] = $request->getLoanType();
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

    public function download() {
        // [0] = userId, [1] = requestId, [2] = filename, [3] = file extension
        $parsed = explode('.', $_GET['lpath']);
        // Get the Id of the document's owner.
        $userOwner = $parsed[0];
        if ($userOwner != $_SESSION['id'] && $_SESSION['type'] > 2) {
            // Only agents can download documents that are not their own.
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
            readfile(DropPath . $_GET['lpath']);
        }
    }

    public function downloadAll() {
        // At least 2 documents will always be available for download.
        $docs = json_decode($_GET['docs']);
        // [0] = userId, [1] = requestId, [2] = filename, [3] = file extension
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
        // TODO: Test in a non-local hosting to see if download is not interrupted
        unlink($zipname);
    }
}
