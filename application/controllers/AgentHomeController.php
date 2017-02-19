<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class AgentHomeController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
        if ($this->session->type != AGENT) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('templates/agentHome');
		}
    }

    public function deleteDocument() {
        if ($this->session->type != AGENT) {
            $this->load->view('errors/index.html');
        } else {
            $this->load->model('requestsModel', 'requests');
            echo $this->requests->deleteDocument();
        }
    }
}
