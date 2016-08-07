<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class DetailsBottomSheetController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
		if ($_SESSION['type'] > 2) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('history/detailsBottomSheet');
		}
	}
}
