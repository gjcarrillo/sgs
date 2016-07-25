<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class HomeController extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library('session');
	}

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
			$user = $em->find('\Entity\User', $_GET['fetchId']);
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

	public function deleteDocument() {
		try {
			$em = $this->doctrine->em;
			// Delete the document from the server.
			unlink($_GET['fullPath']);
			// Get the specified doc entity
			$doc = $em->find('\Entity\Document', $_GET['id']);
			// Get it's request.
			$request = $doc->getBelongingRequest();
			// Remove this doc from it's request entity
			$request->removeDocument($doc);
			// Register History
			$history = new \Entity\History();
			$history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
			$history->setUserResponsable($_SESSION['name'] . ' ' . $_SESSION['lastName']);
			// 3 = Elimination
			$history->setTitle(3);
			$history->setOrigin($request);
			$request->addHistory($history);
			$em->merge($request);
			// Register it's corresponding action
			$action = new \Entity\HistoryAction();
			$action->setSummary("Eliminación del documento '" . $doc->getName() . "'.");
			$action->setBelongingHistory($history);
			$history->addAction($action);
			$em->persist($action);
			$em->persist($history);
			// Delete the document.
			$em->remove($doc);
			// Persist the changes in database.
			$em->flush();
			$result['message'] = "success";
		} catch (Exception $e) {
			$result['message'] = "error";
			\ChromePhp::log($e);
		}
		echo json_encode($result);
	}

	public function deleteRequest() {
		try {
			$em = $this->doctrine->em;
			// Must delete all documents belonging to this request first
			$request = $em->find('\Entity\Request', $_GET['id']);
			$docs = $request->getDocuments();
			foreach($docs as $doc) {
				unlink(DropPath . $doc->getLpath());
			}
			// Now we can remove the current request (and docs on cascade)
			$em->remove($request);
			// Persist the changes in database.
			$em->flush();
			$result['message'] = "success";
		} catch (Exception $e) {
			$result['message'] = "error";
			\ChromePhp::log($e);
		}
		echo json_encode($result);
	}
}
