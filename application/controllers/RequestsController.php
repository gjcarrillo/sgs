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
            $this->load->model('requestsModel', 'requests');
            echo $this->requests->getUserRequests();
        }
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
        $this->load->model('requestsModel', 'requests');
        echo $this->requests->deleteRequestJWT();
    }

    public function deleteRequestUI() {
        $data = json_decode($this->input->raw_input_stream, true);
        $em = $this->doctrine->em;
        $request = $em->find('\Entity\Request', $data['id']);
        if ($this->session->id != $request->getUserOwner()->getId() && $this->session->type['type'] != AGENT) {
            // Only agents can delete a requests that aren't their own.
            $this->load->view('errors/index.html');
        } else {
            $this->load->model('requestsModel', 'requests');
            echo $this->requests->deleteRequestUI();
        }
    }
}