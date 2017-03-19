<?php

/**
 * Created by PhpStorm.
 * User: Kristopher
 * Date: 3/18/2017
 * Time: 8:45 PM
 */
class DriveController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('drive');
        $this->load->model('driveModel');
    }

    /**
     * Cron job. This will delete all documents being stored locally after uploading
     * them to Drive.
     */
    public function uploadNewDocuments() {
        $result['message'] = "error";
        try {
            $uploads = $this->driveModel->uploadDocuments();
            if ($uploads == null) {
                $result['message'] = "No documents to upload.";
            } else {
                $result['uploads'] = $uploads;
                $result['message'] = "success";
            }
        } catch (Exception $e) {
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        echo json_encode($result);
    }
}