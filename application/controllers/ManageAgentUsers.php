<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class ManageAgentUsers extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
		if ($_SESSION['type'] != MANAGER) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('manageAgentUsers');
		}
	}

	public function createNewAgent() {
		if ($_SESSION['type'] != MANAGER) {
			$this->load->view('errors/index.html');
		} else {
			$data = json_decode(file_get_contents('php://input'), true);
			\ChromePhp::log($data);
			try {
				$em = $this->doctrine->em;
				$user = $em->find('\Entity\User', $data['id']);
				if ($user != null && $user->getStatus() == "ACTIVO") {
					$result['message'] = "La cédula " . $data['id'] . " ya se encuentra registrada";
				} else {
					$this->load->model('userModel', 'users');
					if ($user != null) {
						// User was most likely inactive.
						$this->users->resurrectUser($user->getId());
					} else {
						// User not found. Create it.
						$data['type'] = AGENT;
						$data['status'] = "ACTIVO";
						$this->users->createUser($data);
					}
					$result['message'] = "success";
				}
			} catch (Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = $this->utils->getErrorMsg($e);
			}
			echo json_encode($result);
		}
	}

	public function fetchAllAgents() {
		$result = null;
		if ($_SESSION['type'] != MANAGER) {
			$this->load->view('errors/index.html');
		} else {
			try {
				$em = $this->doctrine->em;
				// Get all agents
				$agents = $em->getRepository('\Entity\User')->findBy(array('type' => AGENT, 'status' => "ACTIVO"));
				foreach ($agents as $aKey => $agent) {
					$result['agents'][$aKey] =
						$agent->getId() . " (" . $agent->getFirstName() . " " . $agent->getLastName() . ")";
				}
				$result['message'] = "success";
			} catch (Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = $this->utils->getErrorMsg($e);
			}
			echo json_encode($result);
		}
	}

	public function deleteAgentUser() {
		if ($_SESSION['type'] != MANAGER) {
			$this->load->view('errors/index.html');
		} else {
			$data = file_get_contents('php://input');
			\ChromePhp::log($data);
			try {
				$em = $this->doctrine->em;
				$agent = $em->find('\Entity\User', $data);
				$agent->setType(APPLICANT);
				// Persist the changes in database.
				$em->merge($agent);
				$em->flush();
				$result['message'] = "success";
			} catch (Exception $e) {
				$result['message'] = $this->utils->getErrorMsg($e);
				\ChromePhp::log($e);
			}
			echo json_encode($result);
		}
	}
}
