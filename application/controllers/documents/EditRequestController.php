<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class EditRequestController extends CI_Controller {
	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }
	
	public function index() {
		$this->load->view('documents/editRequest');
	}

	public function editionDialog() {
		$this->load->view('documents/editDocDescription');
	}

	public function updateRequest() {
		try {
			$em = $this->doctrine->em;
			// Update request
			$request = $em->find('\Entity\Request', $_GET['id']);
			// Register History first
			$history = new \Entity\History();
			// TODO: Configure timezone
			$history->setDate(new DateTime('now'));
			$history->setUserResponsable($_SESSION['name'] . ' ' . $_SESSION['lastName']);
			// 2 = Modification
			$history->setTitle(2);
			$history->setOrigin($request);
			$request->addHistoryList($history);
			// TODO: Set ACTION
			$em->persist($history);

			$request->setStatusByText($_GET['status']);
			if (isset($_GET['comment'])) {
				$request->setComment($_GET['comment']);
			}
			$em->merge($request);
			$em->flush();
			$result['message'] = "success";
		} catch (Exception $e) {
			\ChromePhp::log($e);
			$result['message'] = "error";
		}

		echo json_encode($result);
	}

	public function updateDocDescription() {
		try {
			$em = $this->doctrine->em;
			// Update document's description
			$document = $em->find('\Entity\Document', $_GET['id']);
			// Register History first
			$history = new \Entity\History();
			// TODO: Configure timezone
			$history->setDate(new DateTime('now'));
			$history->setUserResponsable($_SESSION['name'] . ' ' . $_SESSION['lastName']);
			// 2 = Modification
			$history->setTitle(2);
			$request = $document->getBelongingRequest();
			$history->setOrigin($request);
			$request->addHistoryList($history);
			$em->merge($request);
			// TODO: Set ACTION
			$em->persist($history);

			$document->setDescription($_GET['description']);
			$em->merge($document);
			$em->flush();
			$result['message'] = "success";
		} catch (Exception $e) {
			\ChromePhp::log($e);
			$result['message'] = "error";
		}

		echo json_encode($result);
	}
}
