<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class UserInfoController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
		$this->load->view('templates/userInfo');
	}

	public function getUserInfo() {
		$result['message'] = "error";
		try {
			if ($this->input->get('userId') != $this->session->id && $this->session->type == APPLICANT) {
				$result['message'] = 'forbidden';
			} else {
				$result['personal'] = $this->users->getIpapediUserInfo($this->input->get('userId'));
				$result['contribution'] = $this->users->getContributionData($this->input->get('userId'));
				$result['contribution']->totalContribution = intval($result['contribution']->u_saldo_disp, 10) +
															   intval($result['contribution']->p_saldo_disp,10);
				$result['message'] = "success";
			}
		} catch (Exception $e) {
			$result['message'] = $this->utils->getErrorMsg($e);
		}
		echo json_encode($result);
	}
}
