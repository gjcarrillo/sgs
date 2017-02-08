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
			try {
				$em = $this->doctrine->em;
				$user = $em->find('\Entity\User', $data['id']);
				if ($user != null && $user->getStatus() == "ACTIVE") {
					$result['message'] = "La cédula " . $data['id'] . " ya se encuentra registrada";
				} else {
					if ($user != null) {
						// User was most likely inactive.
						$this->resurrectUser($em, $user);
					} else {
						// User not found. Create it.
						$this->createUser($em, $data);
					}
					$result['message'] = "success";
				}
			} catch (Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = "Ha ocurrido un error en sistema.";
			}
			echo json_encode($result);
		}
	}

	/**
	 * Resurrects a currently inactive user.
	 *
	 * @param $em - doctrine's entity manager.
	 * @param $user - corresponding user entity.
	 */
	private function resurrectUser($em, $user) {
		$user->setStatus("ACTIVE");
		$em->merge($user);
		$em->flush();
	}

	/**
	 * Creates a new user in database.
	 *
	 * @param $em - doctrine's entity manager.
	 * @param $data - new user's data.
	 */
	private function createUser($em, $data) {
		$user = new \Entity\User();
		$user->setId($data['id']);
		$user->setPassword($data['psw']);
		$user->setName($data['name']);
		$user->setLastname($data['lastname']);
		$user->setType(1);
		$em->persist($user);
		$em->flush();
	}

	public function fetchAllAgents() {
		$result = null;
		if ($_SESSION['type'] != MANAGER) {
			$this->load->view('errors/index.html');
		} else {
			try {
				$em = $this->doctrine->em;
				// Get all agents
				$agents = $em->getRepository('\Entity\User')->findBy(array('type' => AGENT, 'status' => "ACTIVE"));
				foreach ($agents as $aKey => $agent) {
					$result['agents'][$aKey] =
						$agent->getId() . " (" . $agent->getFirstName() . " " . $agent->getLastName() . ")";
				}
			} catch (Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = "Ha ocurrido un error en sistema.";
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
				$agent->setStatus("INACTIVE");
				// Persist the changes in database.
				$em->merge($agent);
				$em->flush();
				$result['message'] = "success";
			} catch (Exception $e) {
				$result['message'] = "error";
				\ChromePhp::log($e);
			}
			echo json_encode($result);
		}
	}
}
