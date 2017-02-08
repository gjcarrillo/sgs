<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class UserInfoController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
		if ($_SESSION['type'] == APPLICANT) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('userInfo');
		}
	}

	public function getUserInfo() {
		$result['message'] = "error";

		if ($_SESSION['type'] == APPLICANT) {
			$this->load->view('errors/index.html');
		} else {
			$this->db->select('*');
			$this->db->from('db_dt_personales');
			$this->db->where('cedula', $_GET['userId']);
			$query = $this->db->get();
			if (empty($query->result())) {
				$result['message'] = "No se encontró información para el afiliado";
			} else {
				$result['data'] = $query->result()[0];
				try {
					$em = $this->doctrine->em;
					$user = $em->find('\Entity\User', $_GET['userId']);
					$result['userName'] = $user->getFirstName() . " " . $user->getLastName();
					$result['message'] = "success";
				} catch (Exception $e) {
					$result['message'] = "Error";
					\ChromePhp::log($e);
				}
			}
		}

		echo json_encode($result);
	}
}
