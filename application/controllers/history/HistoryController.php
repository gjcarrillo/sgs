<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class HistoryController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
		$this->load->view('history/history');
	}

	public function fetchRequestHistory() {
		try {
			$em = $this->doctrine->em;
			// Get all current request's history
			$request = $em->find('\Entity\Request', $_GET['id']);
			$historyList = $request->getHistoryList();
			foreach ($historyList as $hKey => $history) {
				$result['historyList'][$hKey]['userResponsable'] = $history->getUserResponsable();
				$result['historyList'][$hKey]['date'] = $history->getDate()->format('d/m/y - h:i:sa');
				$result['historyList'][$hKey]['title'] = $history->getTitleByText();
			}
			$result['message'] = "success";

		} catch(Exception $e) {
			\ChromePhp::log($e);
			$result['message'] = "Error";
		}

		echo json_encode($result);
	}
}
