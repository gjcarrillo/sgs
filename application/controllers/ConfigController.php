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
        $this->load->model('configModel');
        $this->load->library('session');
    }

    public function index() {
        if ($this->session->type != MANAGER) {
            $this->load->view('errors/index.html');
        } else {
            $this->load->view('templates/systemConfig');
        }
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
        if ($_SESSION['type'] != MANAGER) {
            $this->load->view('errors/index.html');
        } else {
            echo $this->configModel->getStatuses();
        }
    }

    public function getStatusesForConfig() {
        if ($_SESSION['type'] != MANAGER) {
            $this->load->view('errors/index.html');
        } else {
            echo $this->configModel->getStatusesForConfig();
        }
    }

    public function saveStatuses() {
        if ($_SESSION['type'] != MANAGER) {
            $this->load->view('errors/index.html');
        } else {
            echo $this->configModel->saveStatuses();
        }
    }

    /** Max. requested amount configuration **/
    public function getMaxReqAmount() {
        if ($_SESSION['type'] != MANAGER) {
            $this->load->view('errors/index.html');
        } else {
            try {
                $result['maxAmount'] = $this->configModel->getMaxReqAmount();
                $result['message'] = 'success';
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
            echo json_encode($result);
        }
    }

    /** Min. requested amount configuration **/
    public function getMinReqAmount() {
        if ($_SESSION['type'] != MANAGER) {
            $this->load->view('errors/index.html');
        } else {
            try {
                $result['minAmount'] = $this->configModel->getMinReqAmount();
                $result['message'] = 'success';
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
            echo json_encode($result);
        }
    }

    /**
     * Sets both min. and max. request amount.
     */
    public function setReqAmount() {
        if ($_SESSION['type'] != MANAGER) {
            $this->load->view('errors/index.html');
        } else {
            echo $this->configModel->setReqAmount();
        }
    }

    /** Requests month span for applying to same type of loan configuration **/

    public function getRequestsSpan() {
        if ($_SESSION['type'] != MANAGER) {
            $result['message'] = "Forbidden";
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
        if ($_SESSION['type'] != MANAGER) {
            $result['message'] = "Forbidden";
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
        if ($_SESSION['type'] != MANAGER) {
            $result['message'] = "Forbidden";
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
        if ($_SESSION['type'] != MANAGER) {
            $this->load->view('errors/index.html');
        } else {
            $data = json_decode($this->input->raw_input_stream, true);
            echo $this->configModel->updateRequestsSpan($data['span']);
        }
    }

    // Updates the requests terms.
    public function updateRequestsTerms() {
        if ($_SESSION['type'] != MANAGER) {
            $result['message'] = "Forbidden";
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
