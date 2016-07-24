<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class EditRequest extends CI_Controller {

	public function index() {
		$this->load->view('documents/editRequest');
	}
}
