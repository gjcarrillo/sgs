<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class ValidationController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('emailModel', 'email');
        $this->load->library('session');
    }

    /**
     * Validates a request.
     */
    public function validateReq() {
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $em = $this->doctrine->em;
            // Get the request entity.
            $request = $em->find('\Entity\Request', $data['rid']);
            if ($request->getUserOwner()->getId() != $this->session->id) {
                $result['message'] = "Esta solicitud no le pertenece.";
            } else {
                // Validate request and register history.
                $this->load->model('historyModel', 'history');
                $this->history->registerValidation($request->getId());
                $request->setValidationDate(new DateTime('now', new DateTimeZone('America/Barbados')));
                $this->email->sendNewRequestEmail($request->getId());
                $em->merge($request);
                $em->flush();
                $em->clear();
                $result['date'] = $request->getValidationDate();
                $result['message'] = "success";
            }
        } catch (Exception $e) {
            $result['message'] = $this->utils->getErrorMsg($e);
        }

        echo json_encode($result);
    }
}
