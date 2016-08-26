<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class NewRequestController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
		if ($_SESSION['type'] != 1 && $_SESSION['type'] != 3) {
			$this->load->view('errors/index.html');
		} else {
			// Both agents and applicants can create a new request
			$this->load->view('documents/newRequest');
		}
	}

    public function upload() {
        // $uploaddir = DropPath . $_POST['userId'] . '/' . $_POST['requestId'] . '/';
        // $uploadfile = $uploaddir . basename($_FILES['file']['name']);
        // if (!file_exists($uploaddir)) {
        //     mkdir($uploaddir, 0777, true);
        // }
		if ($_SESSION['type'] != 1 && $_SESSION['type'] != 3) {
			$this->load->view('errors/index.html');
		} else {
			$uploadfile = DropPath . $_POST['userId'] . '.' . $_POST['requestId'] . '.' . basename($_FILES['file']['name']);
	        move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);

	        $result['lpath'] = $_POST['userId'] . '.' . $_POST['requestId'] . '.' . basename($_FILES['file']['name']);

	        echo json_encode($result);
		}
    }

    public function createRequest() {
		if ($_SESSION['type'] != 1 && $_SESSION['type'] != 3) {
			$this->load->view('errors/index.html');
		} else {
			// Both agents and applicants can create new requests
			$data = json_decode(file_get_contents('php://input'), true);
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
				$action->setSummary("Solicitud creada.");
				$action->setDetail("Estado de la solicitud: Recibida.\nMonto solicitado: Bs " . number_format($data['reqAmount'], 2));
				$action->setBelongingHistory($history);
				$history->addAction($action);
				$em->persist($action);
				$em->persist($history);
	            // 1 = Waiting
	            $request->setStatus(1);
	            $request->setCreationDate(new DateTime('now', new DateTimeZone('America/Barbados')));
				$request->setRequestedAmount($data['reqAmount']);
	            $user = $em->find('\Entity\User', $data['userId']);
	            $request->setUserOwner($user);
	            $user->addRequest($request);

	            $em->persist($request);
	            $em->merge($user);
	            $em->flush();
	            $result['requestId'] = $request->getId();
				$result['historyId'] = $history->getId();
	            $result['message'] = "success";
	        } catch (Exception $e) {
	            \ChromePhp::log($e);
	            $result['message'] = "error";
	        }

	        echo json_encode($result);
		}
    }

    public function createDocument() {
		if ($_SESSION['type'] != 1 && $_SESSION['type'] != 3) {
			$this->load->view('errors/index.html');
		} else {
			$data = json_decode(file_get_contents('php://input'), true);
	        try {
	            $em = $this->doctrine->em;
				$doc = $em->getRepository('\Entity\Document')->findOneBy(array(
					"lpath"=>$data['lpath']
				));
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
					$request = $em->find('\Entity\Request', $data['requestId']);
		            $doc->setBelongingRequest($request);
		            $request->addDocument($doc);

		            $em->persist($doc);
		            $em->merge($request);
				}
				// Set History action for this request's corresponding history
				$history =  $em->find('\Entity\History', $data['historyId']);
				$action = new \Entity\HistoryAction();
				$action->setSummary("Adición del documento '" . $data['docName'] . "'.");
				if (isset($data['description']) && $data['description'] !== "") {
					$action->setDetail("Descripción: " . $data['description']);
				}
				$action->setBelongingHistory($history);
				$history->addAction($action);
				$em->persist($action);
				$em->merge($history);
				$em->flush();
				$result['message'] = "success";
	        } catch (Exception $e) {
	            \ChromePhp::log($e);
	            $result['message'] = "error";
	        }

	        echo json_encode($result);
		}
    }

	public function camera() {
		if ($_SESSION['type'] != 1) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('camera/camera');
		}
	}

	public function uploadBase64Images() {
		if ($_SESSION['type'] != 1) {
			$this->load->view('errors/index.html');
		} else {
			$data = json_decode(file_get_contents('php://input'), true);
			$imageData = $data['imageData'];
			$imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

			$filepath = DropPath . $data['userId'] . "." . $data['requestId'] . "." . $data['docName'] . ".png";
			file_put_contents($filepath, $imageData);

			$result['message'] = "success";
			$result['lpath'] = $data['userId'] . "." . $data['requestId'] . "." . $data['docName'] . ".png";
			echo json_encode($result);
		}
	}
}
