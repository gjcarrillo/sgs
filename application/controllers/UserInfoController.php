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
			$this->load->view('templates/userInfo');
		}
	}

	public function getUserInfo() {
		$result['message'] = "error";
		try {
			if ($_SESSION['type'] == APPLICANT) {
				$this->load->view('errors/index.html');
			} else {
				$this->load->model('userModel', 'users');
				$result = $this->users->getIpapediUserInfo($this->input->get('userId'));
				$result['message'] = "success";
			}
		} catch (Exception $e) {
			\ChromePhp::log($e);
			$result['message'] = $this->utils->getErrorMsg($e);
		}
		echo json_encode($result);
	}
}
