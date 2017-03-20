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

    /**
     * Uploads (or updates) a backup of the DB dump to Drive.
     */
    public function uploadDB() {
        $result['message'] = 'error';
        try {
            if(file_exists(DropPath . 'backup.sql')) {
                $dbId = $this->driveModel->getDBId();
                if ($dbId === null) {
                    // DB backup not found. Upload as new file.
                    print "backup.sql not found. Updating new...\n";
                    $result['id'] = $this->driveModel->uploadDB();
                } else {
                    // Update existing DB backup.
                    print "Updating fileId" . $dbId . "\n";
                    $result['id'] = $this->driveModel->updateFile(
                        $dbId,
                        'application/sql',
                        DropPath . 'backup.sql');
                }
                $result['message'] = "success";
            } else {
                $result['message'] = DropPath . "/backup.sql NOT FOUND";
            }
        } catch (Exception $e) {
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        echo json_encode($result);
    }

    /**
     * Generates the backup.sql in the Drop Path.
     */
    public function dumpDB() {
        $result['message'] = 'error';
        try {
            exec("mysqldump --user=" . getenv('DB_USERNAME') . " --password=" . getenv('DB_PASSWORD') .
                 " --host=" . getenv('DB_HOSTNAME') . " --port=3306 --result-file=" . DropPath . "backup.sql" .
                 " --default-character-set=utf8 --single-transaction=TRUE --databases \"" . getenv('DB_DATABASE') ."\"");
            print "backup.sql dump created successfully.\n";
            $result['message'] = 'success';
        } catch (Exception $e) {
            $result['message'] = $this->utils->getErrorMsg($e);
        }
    }

    /**
     * Obtains the backed up DB from Drive & executes the script to restore the database.
     */
    public function restoreDB() {
        $result['message'] = 'error';
        try {
            $dbId = $this->driveModel->getDBId();
            if ($dbId === null) {
                print "backup.sql not found.\n";
            } else {
                $sql = $this->driveModel->getDocumentContents($dbId);
                file_put_contents(DropPath . "backup.sql", $sql);
                exec("mysql --user=" . getenv('DB_USERNAME') . " --password=" . getenv('DB_PASSWORD') .
                     " < " . DropPath . "backup.sql");
                print "backup.sql executed successfully.\n";
            }
            $result['message'] = 'success';
        } catch (Exception $e) {
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        echo json_encode($result);
    }
}