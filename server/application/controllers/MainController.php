<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class MainController extends CI_Controller {

	public function index() {
		$this->load->view('errors/index.html');
	}

	public function footer() {
        $this->load->view('templates/footer');
	}

	public function incompatible () {
        $this->load->view('templates/incompatible');
	}

	public function overlay () {
		$this->load->view('templates/overlay');
	}

	public function expired() {
		$this->load->view('templates/sessionExpired');
	}

	public function perspective() {
		$this->load->view('templates/perspective');
	}
}
