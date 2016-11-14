<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class ManageAgentUsers extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
		if ($_SESSION['type'] != 2) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('manageAgentUsers');
		}
	}

	public function createNewAgent() {
		if ($_SESSION['type'] != 2) {
			$this->load->view('errors/index.html');
		} else {
			$data = json_decode(file_get_contents('php://input'), true);
			try {
				$em = $this->doctrine->em;
				$user = $em->find('\Entity\User', $data['id']);
				if ($user != null) {
					$result['message'] = "La cédula " . $data['id'] . " ya se encuentra registrada";
				} else {
					$user = new \Entity\User();
					$user->setId($data['id']);
					$user->setPassword($data['psw']);
					$user->setName($data['name']);
					$user->setLastname($data['lastname']);
					$user->setType(1);

					$em->persist($user);
					$em->flush();
					$result['message'] = "success";
				}
			} catch (Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = "Ha ocurrido un error en sistema.";
			}
			echo json_encode($result);
		}
	}

	public function fetchAllAgents() {
		if ($_SESSION['type'] != 2) {
			$this->load->view('errors/index.html');
		} else {
			try {
				$em = $this->doctrine->em;
				// Get all agents (type == 1)
				$agents = $em->getRepository('\Entity\User')->findBy(array('type' => 1));
				foreach ($agents as $aKey => $agent) {
					$result['agents'][$aKey] =
						$agent->getId() . " (" . $agent->getName() . " " . $agent->getLastName() . ")";
				}
			} catch (Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = "Ha ocurrido un error en sistema.";
			}
			echo json_encode($result);
		}
	}

	public function deleteAgentUser() {
		if ($_SESSION['type'] != 2) {
			$this->load->view('errors/index.html');
		} else {
			$data = file_get_contents('php://input');
			\ChromePhp::log($data);
			try {
				$em = $this->doctrine->em;
				$agent = $em->find('\Entity\User', $data);
				$em->remove($agent);
				// Persist the changes in database.
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
