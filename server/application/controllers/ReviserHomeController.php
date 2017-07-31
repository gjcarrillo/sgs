<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class ReviserHomeController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

    public function index() {
        $this->load->view('templates/reviserHome');
    }

    public function getPreApprovedRequests() {
        if ($this->session->type != REVISER) {
            $result['message'] = 'forbidden';
        } else {
            try {
                $em = $this->doctrine->em;
                $requestsRepo = $em->getRepository('\Entity\Request');
                $requests = $requestsRepo->findBy(array("status" => PRE_APPROVED));
                if (empty($requests)) {
                    $result['message'] = "No se encontraron solicitudes con status " . PRE_APPROVED;
                } else {
                    foreach ($requests as $rKey => $request) {
                        $result['requests'][$rKey] = $this->utils->reqToArray($request);
                    }
                    $result['message'] = 'success';
                }
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }

        echo json_encode($result);
    }

}
