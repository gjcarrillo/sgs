<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class NewRequest extends CI_Controller {

	public function index() {
		$this->load->view('documents/newRequest');
	}

    public function upload() {
        // $uploaddir = DropPath . $_POST['userId'] . '/' . $_POST['requestId'] . '/';
        // $uploadfile = $uploaddir . basename($_FILES['file']['name']);
        // if (!file_exists($uploaddir)) {
        //     mkdir($uploaddir, 0777, true);
        // }
		$uploadfile = DropPath . $_POST['userId'] . '.' . $_POST['requestId'] . '.' . basename($_FILES['file']['name']);
        move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);

        $result['lpath'] = $_POST['userId'] . '.' . $_POST['requestId'] . '.' . basename($_FILES['file']['name']);

        echo json_encode($result);
    }

    public function createRequest() {
        try {
            $em = $this->doctrine->em;
            // New request
            $request = new \Entity\Request();
            // 1 = Waiting
            $request->setStatus(1);
            // TODO: Configure TIMEZONE
            $request->setCreationDate(new \DateTime('now'));
            $user = $em->find('\Entity\User', $_GET['userId']);

            $request->setUserOwner($user);
            $user->addRequest($request);
            $em->persist($request);
            $em->merge($user);
            $em->persist($user);
            $em->flush();
            $result['requestId'] = $request->getId();
            $result['message'] = "success";
        } catch (Exception $e) {
            \ChromePhp::log($e);
            $result['message'] = "error";
        }

        echo json_encode($result);
    }

    public function createDocument() {
        try {
            $em = $this->doctrine->em;
			$doc = $em->getRepository('\Entity\Document')->findOneBy(array(
				"lpath"=>$_GET['lpath']
			));

			if ($doc !== null) {
				// doc already exists, so just merge
				if (isset($_GET['description'])) {
					$doc->setDescription($_GET['description']);
					$em->merge($doc);
					$em->persist($doc);
					$em->flush();
				}
			} else {
	            // New document
	            $doc = new \Entity\Document();
	            $doc->setName($_GET['docName']);
	            if (isset($_GET['description'])) {
	                $doc->setDescription($_GET['description']);
	            }
	            $doc->setLpath($_GET['lpath']);
	            $request = $em->find('\Entity\Request', $_GET['requestId']);
	            $doc->setBelongingRequest($request);
	            $request->addDocument($doc);

	            $em->persist($doc);
	            $em->merge($request);
	            $em->persist($request);
	            $em->flush();
			}
            $result['message'] = "success";
        } catch (Exception $e) {
            \ChromePhp::log($e);
            $result['message'] = "error";
        }

        echo json_encode($result);
    }
}
