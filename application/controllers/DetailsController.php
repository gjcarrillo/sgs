<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class DetailsController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('requestsModel', 'requests');
    }

    public function index() {
        $this->load->view('templates/details');
    }

}
