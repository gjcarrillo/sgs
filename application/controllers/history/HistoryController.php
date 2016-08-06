<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class HistoryController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
		if ($_SESSION['type'] > 2) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('history/history');
		}
	}

	public function fetchRequestHistory() {
		if ($_SESSION['type'] > 2) {
			$this->load->view('errors/index.html');
		} else {
			try {
				$em = $this->doctrine->em;
				// Get all current request's history
				$request = $em->find('\Entity\Request', $_GET['id']);
				$history = $request->getHistory();
				$history = array_reverse($history->getValues());
				foreach ($history as $hKey => $history) {
					$result['history'][$hKey]['userResponsable'] = $history->getUserResponsable();
					$result['history'][$hKey]['date'] = $history->getDate()->format('d/m/y - h:i:sa');
					$result['history'][$hKey]['title'] = $history->getTitleByText();
					$actions = $history->getActions();
					foreach ($actions as $aKey => $action) {
						$result['history'][$hKey]['actions'][$aKey]['summary'] = $action->getSummary();
						$result['history'][$hKey]['actions'][$aKey]['detail'] = $action->getDetail();
					}
				}
				$result['message'] = "success";

			} catch(Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = "Error";
			}

			echo json_encode($result);
		}
	}
}
