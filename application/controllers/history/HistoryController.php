<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class HistoryController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }
	
	public function index() {
		$this->load->view('history/history');
	}
}
