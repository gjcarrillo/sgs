<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class NewAgentController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
		if ($_SESSION['type'] != 2) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('users/newAgent');
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
}
