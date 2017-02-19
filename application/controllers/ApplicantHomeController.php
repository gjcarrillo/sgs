<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class ApplicantHomeController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
        $this->load->view('templates/applicantHome');
	}

    public function sendValidation() {
        if ($_SESSION['type'] != APPLICANT) {
            $this->load->view('errors/index.html');
        } else {
            try {
                $reqId = json_decode($this->input->raw_input_stream, true);
                $this->load->model('emailModel', 'email');
                $this->email->sendNewRequestEmail($reqId);
                $this->load->model('historyModel', 'history');
                $this->history->registerValidationResend($reqId);
                $result['message'] = "success";
            } catch (Exception $e) {
                \ChromePhp::log($e);
                $result['message'] = $this->utils->getErrorMsg($e);
            }
            echo json_encode($result);
        }
    }
}
