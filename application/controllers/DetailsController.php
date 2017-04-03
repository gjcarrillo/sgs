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

    public function getDetails() {
        $em = $this->doctrine->em;
        try {
            $rid = $this->input->get('rid');
            $request = $em->find('\Entity\Request', $rid);
            if ($request->getUserOwner()->getId() != $this->session->id && $this->session->type == APPLICANT) {
                $result['message'] = "Esta solicitud no le pertenece.";
            } else {
                $result['details'] = $this->requests->getDetails($rid);
                $result['message'] = 'success';
            }
        } catch (Exception $e) {
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        echo json_encode($result);
    }
}
