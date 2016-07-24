<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class HomeController extends CI_Controller {

	public function index() {
		$this->load->view('home/home');
	}
	// Obtain all requests with with all their documents.
	// NOTICE: sensitive information
	public function getUserRequests() {
		try {
			$em = $this->doctrine->em;
			// TODO: Fetch current session's usertype. If he does not have permission -- stop!
			// otherwise authorize query.
			$user = $em->getRepository('\Entity\User')->findOneBy(array("id"=>$_GET['fetchId']));
			if ($user === null) {
				$result['error'] = "La cédula ingresada no se encuentra en la base de datos";
			} else {
				$requests = $user->getRequests();
				foreach ($requests as $rKey => $request) {
					$result['requests'][$rKey]['id'] = $request->getId();
					$result['requests'][$rKey]['creationDate'] = $request->getCreationDate()->format('d-m-y');
					$result['requests'][$rKey]['comment'] = $request->getComment();
					$result['requests'][$rKey]['status'] = $request->getStatusByText();
					$docs = $request->getDocuments();
					foreach ($docs as $dKey => $doc) {
						$result['requests'][$rKey]['docs'][$dKey]['id'] = $doc->getId();
						$result['requests'][$rKey]['docs'][$dKey]['name'] = $doc->getName();
						$result['requests'][$rKey]['docs'][$dKey]['description'] = $doc->getDescription();
						$result['requests'][$rKey]['docs'][$dKey]['fullPath'] = DropPath . $doc->getLpath();
					}
				}
				$result['message'] = "success";
			}
		} catch (Exception $e) {
			\ChromePhp::log($e);
            $result['message'] = "error";
		}

		echo json_encode($result);
	}

	public function upload() {
		$uploaddir = 'C:/Users/Kristopher/Dropbox/' . $_POST['userId'] . '/' . $_POST['requestId'] . '/';
		$uploadfile = $uploaddir . basename($_FILES['file']['name']);
		if (!file_exists($uploaddir)) {
			mkdir($uploaddir, 0777, true);
		}
		move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);

		$result['lpath'] = $_POST['userId'] . '/' . $_POST['requestId'] . '/' . basename($_FILES['file']['name']);

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
			$user = $em->getRepository('\Entity\User')->findOneBy(array("id"=>$_GET['userId']));
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
			// New document
			$doc = new \Entity\Document();
			$doc->setName($_GET['docName']);
			if (isset($_GET['description'])) {
				$doc->setDescription($_GET['description']);
			}
			$doc->setLpath($_GET['lpath']);
			$request = $em->getRepository('\Entity\Request')->findOneBy(array("id"=>$_GET['requestId']));
			$doc->setBelongingRequest($request);
			$request->addDocument($doc);

			$em->persist($doc);
			$em->merge($request);
			$em->persist($request);
			$em->flush();
			$result['message'] = "success";
		} catch (Exception $e) {
			\ChromePhp::log($e);
			$result['message'] = "error";
		}

		echo json_encode($result);
	}
}
