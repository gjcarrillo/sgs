<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class ValidationController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('emailModel', 'email');
        $this->load->model('requestsModel', 'requests');
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
                $data = $this->getRequestData($request);
                switch (intval($data['loanType'], 10)) {
                    case CASH_VOUCHER:
                        $this->requests->validateCashVoucherCreation($data, true);
                        break;
                    case PERSONAL_LOAN:
                        $this->requests->validatePersonalLoanCreation($data, true);
                        break;
                }
                // Validate request and register history.
                $this->load->model('historyModel', 'history');
                $this->history->registerValidation($request->getId());
                $request->setValidationDate(new DateTime('now', new DateTimeZone('America/Barbados')));
                $this->email->sendNewRequestEmail($request->getId());
                $em->merge($request);
                $em->flush();
                $em->clear();
                $result['date'] = $request->getValidationDate()->format('d/m/Y');
                $result['message'] = "success";
            }
        } catch (Exception $e) {
            $result['message'] = $this->utils->getErrorMsg($e);
        }

        echo json_encode($result);
    }

    private function getRequestData($request) {
        return array(
            "userId" => $request->getUserOwner()->getId(),
            "reqAmount" => $request->getRequestedAmount(),
            "due" => $request->getPaymentDue(),
            "loanType" => $request->getLoanType()
        );
    }
}
