<?php

/**
 * Created by PhpStorm.
 * User: Kristopher
 * Date: 2/4/2017
 * Time: 10:30 PM
 */
class RequestsController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('requestsModel', 'requests');
        $this->load->library('session');
    }

    // Obtain all requests with with all their documents.
    // NOTICE: sensitive information
    public function getUserRequests() {
        if ($this->input->get('fetchId') != $this->session->id &&
            $this->session->type == APPLICANT) {
            // if fetch id is not the same as logged in user, must be an
            // agent or manager to be able to execute query!
            $this->load->view('errors/index.html');
        } else {
            echo $this->requests->getUserRequests();
        }
    }

    public function getRequestById() {
        try {
            $result['request'] = $this->requests->getRequestById(
                $this->input->get('rid'),
                $this->input->get('uid')
            );
            $result['message'] = 'success';
        } catch (Exception $e) {
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        echo json_encode($result);
    }

    public function getRequestsByDate() {
        try {
            // from first second of the day
            $from = date_create_from_format(
                'd/m/Y H:i:s',
                $this->input->get('from') . ' ' . '00:00:00',
                new DateTimeZone('America/Barbados')
            );
            // to last second of the day
            $to = date_create_from_format(
                'd/m/Y H:i:s',
                $this->input->get('to') . ' ' . '23:59:59',
                new DateTimeZone('America/Barbados')
            );
            $result['requests'] = $this->requests->getRequestByDate($from, $to, $this->input->get('uid'));
            $result['message'] = 'success';
        } catch (Exception $e) {
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        echo json_encode($result);
    }

    public function getRequestsByStatus() {
        try {
            $result['requests'] = $this->requests->getRequestByStatus(
                $this->input->get('status'),
                $this->input->get('uid')
            );
            $result['message'] = 'success';
        } catch (Exception $e) {
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        echo json_encode($result);
    }

    public function getRequestsByType() {
        try {
            $result['requests'] = $this->requests->getRequestByType(
                $this->input->get('concept'),
                $this->input->get('uid')
            );
            $result['message'] = 'success';
        } catch (Exception $e) {
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        echo json_encode($result);
    }

    public function getOpenedRequests () {
        try {
            $result['requests'] = $this->requests->getOpenedRequests($this->input->get('uid'));
            $result['message'] = 'success';
        } catch (Exception $e) {
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        echo json_encode($result);
    }

    public function getUserEditableRequests () {
        if ($this->input->get('fetchId') != $this->session->id &&
            $this->session->type == APPLICANT) {
            $result['message'] = 'Forbidden.';
        } else {
            try {
                $result['requests'] = $this->requests->getUserEditableRequests($this->input->get('fetchId'));
                $result['message'] = "success";
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

    public function getUserOpenedRequest () {
        if ($this->input->get('fetchId') != $this->session->id &&
            $this->session->type == APPLICANT) {
            $result['message'] = 'Forbidden.';
        } else {
            try {
                $result['id'] = $this->requests->getUserOpenedRequest(
                    $this->input->get('fetchId'),
                    $this->input->get('concept')
                );
                $result['hasOpened'] = $result['id'] !== null;
                $result['message'] = "success";
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

    public function deleteDocument() {
        if ($this->session->type != AGENT) {
            $this->load->view('errors/index.html');
        } else {
            $this->load->model('requestsModel', 'requests');
            echo $this->requests->deleteDocument();
        }
    }

    public function download() {
        $this->load->model('requestsModel', 'requests');
        $this->requests->downloadDocument();
    }

    public function downloadAll() {
        $this->load->model('requestsModel', 'requests');
        $this->requests->downloadAllDocuments();
    }

    public function deleteRequestView() {
        // Validations are performed when executing (automatically) deleteRequestJWT
        $this->load->view('templates/deleteRequest');
    }

    public function deleteRequestJWT() {
        // Validations are performed when executing deletion function
        echo $this->requests->deleteRequestJWT();
    }

    public function deleteRequestUI() {
        $data = json_decode($this->input->raw_input_stream, true);
        $em = $this->doctrine->em;
        $request = $em->find('\Entity\Request', $data['id']);
        if ($this->session->id != $request->getUserOwner()->getId() && $this->session->type != AGENT) {
            // Only agents can delete a requests that aren't their own.
            $this->load->view('errors/index.html');
        } else {
            echo $this->requests->deleteRequestUI();
        }
    }
}