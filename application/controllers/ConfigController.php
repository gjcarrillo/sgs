<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');
/**
 * Created by PhpStorm.
 * User: Kristopher
 * Date: 1/17/2017
 * Time: 11:41 PM
 */
class ConfigController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
    }

    public function index() {
        $this->load->view('templates/dialogs/systemConfig');
    }

    public function getLoanTypes() {
        try {
            $result['type'] = $this->configModel->getLoanTypes();
            $result['message'] = 'success';
        } catch (Exception $e) {
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        echo json_encode($result);
    }

    /** Requests' status configuration **/

    public function getStatuses() {
        echo $this->configModel->getStatuses();
    }

    public function getStatusesForConfig() {
        if ($this->session->type != MANAGER) {
            $result['message'] = 'forbidden';
        } else {
            try {
                $result['statuses'] = $this->configModel->getStatusesForConfig();
                $result['message'] = 'success';
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

    public function saveStatuses() {
        if ($this->session->type != MANAGER) {
            $result['message'] = 'forbidden';
        } else {
            try {
                $this->configModel->saveStatuses();
                $result['message'] = 'success';
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

    /** Max. requested amount configuration **/
    public function getMaxReqAmount() {
        if ($this->session->type != MANAGER) {
            $result['message'] = 'forbidden';
        } else {
            try {
                $result['maxAmount'] = $this->configModel->getMaxReqAmount();
                $result['message'] = 'success';
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

    public function getCashVoucherPercentage() {
        if ($this->session->type != MANAGER) {
            $result['message'] = 'forbidden';
        } else {
            try {
                $result['percentage'] = $this->configModel->getCashVoucherPercentage();
                $result['message'] = 'success';
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }


    /**
     * Sets both max. request amount percentage of cash voucher.
     */
    public function setReqAmount() {
        if ($this->session->type != MANAGER) {
            $result['message'] = 'forbidden';
        } else {
            try {
                $this->configModel->setReqAmount();
                $result['message'] = 'success';
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

    /** Requests month span for applying to same type of loan configuration **/

    public function getRequestsSpan() {
        if ($this->session->type != MANAGER) {
            $result['message'] = 'forbidden';
        } else {
            try {
                $result['loanTypes'] = $this->configModel->getRequestsSpan();
                $result['message'] = 'success';
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

    /** Gets the configured requests terms for all request types **/
    public function getRequestsTerms() {
        if ($this->session->type != MANAGER) {
            $result['message'] = 'forbidden';
        } else {
            try {
                $result['loanTypes'] = $this->configModel->getRequestsTerms();
                $result['message'] = 'success';
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

    /**
     * Gets the specified request's configured span.
     */
    public function getRequestSpan() {
        if ($this->session->type != MANAGER) {
            $result['message'] = 'forbidden';
        } else {
            $data = json_decode(file_get_contents('php://input'), true);
            try {
                $result['span'] = $this->configModel->getRequestSpan($data['concept']);
                $result['message'] = 'success';
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }



    // Updates the month requests span, required to applying to same type of loan.
    public function updateRequestsSpan() {
        if ($this->session->type != MANAGER) {
            $result['message'] = 'forbidden';
        } else {
            $data = json_decode($this->input->raw_input_stream, true);
            try {
                $this->configModel->updateRequestsSpan($data['span']);
                $result['message'] = 'success';
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

    // Updates the requests terms.
    public function updateRequestsTerms() {
        if ($this->session->type != MANAGER) {
            $result['message'] = 'forbidden';
        } else {
            $data = json_decode($this->input->raw_input_stream, true);
            try {
                $this->configModel->updateRequestsTerms($data['terms']);
                $result['message'] = 'success';
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

    /**
     * Get a specified request concept's available terms.
     */
    public function getRequestTerms() {
        try {
            $result['terms'] = $this->configModel->getRequestTerms($this->input->get('concept'));
            $result['message'] = 'success';
        } catch (Exception $e) {
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        echo json_encode($result);
    }
}
