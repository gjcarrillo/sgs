<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class ManageAgentUsers extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
		$this->load->view('templates/dialogs/manageAgentUsers');
	}

	public function createNewAgent() {
		if ($this->session->type != MANAGER) {
			$result['message'] = 'forbidden';
		} else {
			$data = json_decode(file_get_contents('php://input'), true);
			try {
				$em = $this->doctrine->em;
				$user = $em->find('\Entity\User', $data['id']);
				if ($user != null && ($user->getStatus() == "ACTIVO" || $user->getStatus() == "activo")) {
					if ($user->getType() == AGENT) {
						$result['message'] = "El agente " . $data['id'] . " ya se encuentra registrado.";
					} else if ($user->getType() == MANAGER) {
						$result['message'] = "El usuario " . $data['id'] . " posee privilegios de GERENTE.";
					} else if ($user->getType() == REVISER) {
						$result['message'] = 'El usuario ' . $data['id'] . " ya se encuentra registrado.";
					} else {
						$result['message'] = "El usuario " . $data['id'] . " posee privilegios de AFILIADO.";
					}
				} else {
					if ($user != null) {
						// User was most likely inactive.
						$this->users->resurrectUser($user->getId());
						$this->users->updateUserInfo($data);
					} else {
						// User not found. Create it.
						$data['status'] = "ACTIVO";
						$this->users->createUser($data);
					}
					$result['created'] = true;
					$result['message'] = "success";
				}
			} catch (Exception $e) {
				$result['message'] = $this->utils->getErrorMsg($e);
			}
		}
		echo json_encode($result);
	}

	public function upgradeUser () {
		if ($this->session->type != MANAGER) {
			$result['message'] = 'forbidden';
		} else {
			$data = json_decode(file_get_contents('php://input'), true);
			try {
				$this->users->upgradeUser($data['userId']);
				$result['message'] = "success";
			} catch (Exception $e) {
				$result['message'] = $this->utils->getErrorMsg($e);
			}
		}
		echo json_encode($result);
	}

	public function degradeUser () {
		if ($this->session->type != MANAGER) {
			$result['message'] = 'forbidden';
		} else {
			$data = json_decode(file_get_contents('php://input'), true);
			try {
				$this->users->degradeUser($data['userId']);
				$result['message'] = "success";
			} catch (Exception $e) {
				$result['message'] = $this->utils->getErrorMsg($e);
			}
		}
		echo json_encode($result);
	}

	public function fetchAllAgents() {
		$result = null;
		if ($this->session->type != MANAGER) {
			$result['message'] = 'forbidden';
		} else {
			try {
				$em = $this->doctrine->em;
				// Get all agents
				$agents = $em->getRepository('\Entity\User')->findBy(array('type' => array(AGENT, REVISER), 'status' => "ACTIVO"));
				foreach ($agents as $aKey => $agent) {
					$result['agents'][$aKey]['type'] = $agent->getType();
					$result['agents'][$aKey]['value'] = $agent->getId();
					$result['agents'][$aKey]['display'] =
						$agent->getId() . " (" . $agent->getFirstName() . " " . $agent->getLastName() . ")";
				}
				$result['message'] = "success";
			} catch (Exception $e) {
				$result['message'] = $this->utils->getErrorMsg($e);
			}
		}
		echo json_encode($result);
	}

	public function deleteAgentUser() {
		if ($this->session->type != MANAGER) {
			$result['message'] = 'forbidden';
		} else {
			$data = file_get_contents('php://input');
			try {
				$em = $this->doctrine->em;
				$agent = $em->find('\Entity\User', $data);
				$agent->setStatus("INACTIVO");
				// Persist the changes in database.
				$em->merge($agent);
				$em->flush();
				$result['message'] = "success";
			} catch (Exception $e) {
				$result['message'] = $this->utils->getErrorMsg($e);
			}
		}
		echo json_encode($result);
	}
}
