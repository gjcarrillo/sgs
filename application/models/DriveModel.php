<?php
/**
 * Created by PhpStorm.
 * User: Kristopher
 * Date: 3/18/2017
 * Time: 10:02 PM
 */
include_once (APPPATH. '/libraries/ChromePhp.php');

class DriveModel extends CI_Model
{
    private $service;

    public function __construct() {
        $this->load->library('drive');
        $this->service = $this->drive->getService();
        parent::__construct();
    }

    /**
     * Uploads documents to Drive and then deletes them from local storage.
     * @return Array with uploaded documents id.
     * @throws Exception
     */
    public function uploadDocuments() {
        $result = null;
        try {
            $em = $this->doctrine->em;
            // Find locally stored documents.
            $docs = $em->getRepository('\Entity\Document')->findBy(array('storage' => LOCAL));
            $this->service = $this->drive->getService();
            foreach ($docs as $key => $doc) {
                // Upload each document to Drive.
                $fileMetadata = new Google_Service_Drive_DriveFile(array('name' => $doc->getLpath()));
                $content = file_get_contents(DropPath . $doc->getLpath());
                $file = $this->service->files->create($fileMetadata, array(
                    'data' => $content,
                    'uploadType' => 'multipart',
                    'fields' => 'id'));
                // Delete from local storage.
                unlink(DropPath . $doc->getLpath());
                // [0] = userId, [1] = file uuid, [2] = filename, [3] = file extension
                $parsed = explode('.', $doc->getLpath());
                // Update document In DB.
                $doc->setStorage(REMOTE);
                $doc->setLpath($parsed[0] . '.' . $file->id . '.' . $parsed[2] . '.' . $parsed[3]);
                $em->merge($doc);
                $result[$key] = $file->id;
            }
            $em->flush();
        } catch (Exception $e) {
            throw $e;
        }
        return $result;
    }

    public function deleteDocument($fileId) {
        try {
            // Delete document from drive
            $this->service->files->delete($fileId);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getDocumentContents($fileId) {
        try {
            // Download document from drive
            $response = $this->service->files->get($fileId, array(
                'alt' => 'media' ));
            return $response->getBody()->getContents();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Update an existing file's metadata and content.
     *
     * @param string $fileId ID of the file to update.
     * @param string $newTitle New title for the file.
     * @param string $newMimeType New MIME type for the file.
     * @param string $newFileName Filename of the new content to upload.
     * @return string The updated file id. NULL is returned if
     *     an API error occurred.
     * @throws Exception
     */
    public function updateFile($fileId, $newMimeType, $newFileName)
    {
        try {
            // File's new content.
            $data = file_get_contents($newFileName);

            $additionalParams = array(
                'uploadType' => 'multipart',
                'data' => $data,
                'mimeType' => $newMimeType
            );

            // Send the request to the API.
            $updatedFile = $this->service->files->update(
                $fileId,
                (new \Google_Service_Drive_DriveFile()),
                $additionalParams)
            ;
            if (isset($updatedFile)) {
                return $updatedFile->id;
            } else {
                return null;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function uploadDB() {
        try {
            $fileMetadata = new Google_Service_Drive_DriveFile(array('name' => 'backup.sql'));
            $content = file_get_contents(DropPath . 'backup.sql');
            $file = $this->service->files->create($fileMetadata, array(
                'data' => $content,
                'mimeType' => 'application/sql',
                'uploadType' => 'multipart',
                'fields' => 'id'));
            return $file->id;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getDBId() {
        // Print the names and IDs for up to 10 files.
        $pageToken = null;
        $results = $this->service->files->listFiles(array(
            'q' => "mimeType='application/sql'",
            'spaces' => 'drive',
            'pageToken' => $pageToken,
            'fields' => 'nextPageToken, files(id, name)',
        ));

        if (count($results->getFiles()) == 0) {
            print "No files found.\n";
        } else {
            print "File:\n";
            foreach ($results->getFiles() as $file) {
                printf("%s (%s)\n", $file->getName(), $file->getId());
                return $file->getId();
            }
        }
        // No sql file found
        return null;
    }
}