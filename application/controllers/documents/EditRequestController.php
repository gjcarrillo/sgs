<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class EditRequestController extends CI_Controller {

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
			$request->setStatusByText($_GET['status']);
			if (isset($_GET['comment'])) {
				$request->setComment($_GET['comment']);
			}
			$em->merge($request);
			$em->persist($request);
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
			$document->setDescription($_GET['description']);
			$em->merge($document);
			$em->persist($document);
			$em->flush();
			$result['message'] = "success";
		} catch (Exception $e) {
			\ChromePhp::log($e);
			$result['message'] = "error";
		}

		echo json_encode($result);
	}
}
