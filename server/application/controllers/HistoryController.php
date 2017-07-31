<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class HistoryController extends CI_Controller {

	public function __construct() {
        parent::__construct();
		$this->load->library('session');
    }

	public function index() {
		$this->load->view('templates/history');
	}

	public function actionDetails () {
		$this->load->view('templates/detailsBottomSheet');
	}

	public function fetchRequestHistory() {
		if ($this->session->type == APPLICANT) {
			$result['message'] = 'forbidden';
		} else {
			try {
				$em = $this->doctrine->em;
				// Get all current request's history
				$request = $em->find('\Entity\Request', $this->input->get('id'));
				$histories = $request->getHistory();
				$histories = array_reverse($histories->getValues());
				foreach ($histories as $hKey => $history) {
					$user = $history->getUserResponsible();
					$result['history'][$hKey]['userResponsible'] = $user->getId() .
																   ' (' . $user->getFirstName() . ' ' . $user->getLastName() . ')';
					$result['history'][$hKey]['date'] = $history->getDate()->format('d/m/Y - h:i:sa');
					$result['history'][$hKey]['title'] = $this->utils->getHistoryActionName($history->getTitle());
					$result['history'][$hKey]['picture'] = $this->users->getUserProfileImg($user->getId());
					$actions = $history->getActions();
					foreach ($actions as $aKey => $action) {
						$result['history'][$hKey]['actions'][$aKey]['summary'] = $action->getSummary();
						$result['history'][$hKey]['actions'][$aKey]['detail'] = $action->getDetail();
					}
				}
				$result['message'] = "success";

			} catch(Exception $e) {
				$result['message'] = $this->utils->getErrorMsg($e);
			}
		}
		echo json_encode($result);
	}
}
