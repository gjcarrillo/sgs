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
    public function getUserRequests() {
        if ($this->input->get('fetchId') != $this->session->id &&
            $this->session->type == APPLICANT) {
            // if fetch id is not the same as logged in user, must be an
            // agent or manager to be able to execute query!
            $result['message'] = 'forbidden';
        } else {
            $result['requests'] = $this->requests->getUserRequests();
            $result['message'] = 'success';
        }
        echo json_encode ($result);
    }

    public function getRequestById() {
        if ($this->input->get('uid') != $this->session->id &&
            $this->session->type == APPLICANT) {
            $result['message'] = 'forbidden.';
        } else {
            try {
                $result['request'] = $this->requests->getRequestById(
                    $this->input->get('rid'),
                    $this->input->get('uid')
                );
                $result['message'] = 'success';
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

    public function getRequestsByDate() {
        if ($this->input->get('uid') != $this->session->id &&
            $this->session->type == APPLICANT) {
            $result['message'] = 'forbidden.';
        } else {
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
        }
        echo json_encode($result);
    }

    public function getRequestsByStatus() {
        if ($this->input->get('uid') != $this->session->id &&
            $this->session->type == APPLICANT) {
            $result['message'] = 'forbidden.';
        } else {
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
    }

    public function getRequestsByType() {
        if ($this->input->get('uid') != $this->session->id &&
            $this->session->type == APPLICANT) {
            $result['message'] = 'forbidden.';
        } else {
            try {
                $result['requests'] = $this->requests->getRequestByType(
                    $this->input->get('concept'),
                    $this->input->get('uid')
                );
                $result['message'] = 'success';
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

    public function getOpenedRequests () {
        if ($this->input->get('uid') != $this->session->id &&
            $this->session->type == APPLICANT) {
            $result['message'] = 'forbidden.';
        } else {
            try {
                $result['requests'] = $this->requests->getOpenedRequests($this->input->get('uid'));
                $result['message'] = 'success';
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

    public function getUserEditableRequests () {
        if ($this->input->get('fetchId') != $this->session->id &&
            $this->session->type == APPLICANT) {
            $result['message'] = 'forbidden.';
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
            $result['message'] = 'forbidden.';
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
        if ($this->session->type == APPLICANT) {
            $result['message'] = 'forbidden.';
        } else {
            try {
                $result['request'] = $this->requests->deleteDocument();
                $result['message'] = "success";
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

    public function download() {
        $this->requests->downloadDocument();
    }

    public function downloadAll() {
        $this->requests->downloadAllDocuments();
    }

    public function deleteRequestUI() {
        $data = json_decode($this->input->raw_input_stream, true);
        $em = $this->doctrine->em;
        $request = $em->find('\Entity\Request', $data['id']);
        if ($this->session->id != $request->getUserOwner()->getId() && $this->session->type != AGENT) {
            // Only agents can delete a requests that aren't their own.
            $result['message'] = 'forbidden';
        } else {
            $this->requests->deleteRequestUI();
            $result['message'] = 'success';
        }
        echo json_encode($result);
    }
}