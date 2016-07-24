<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class EditRequest extends CI_Controller {

	public function index() {
		$this->load->view('documents/editRequest');
	}

	public function updateRequest() {
		try {
			$em = $this->doctrine->em;
			// Update request
			\ChromePhp::log($_GET['id']);
			\ChromePhp::log($_GET['status']);
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
}
