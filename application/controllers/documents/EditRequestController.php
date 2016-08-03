<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class EditRequestController extends CI_Controller {
	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
		if ($_SESSION['type'] != 1) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('documents/editRequest');
		}
	}

	public function editionDialog() {
		if ($_SESSION['type'] != 1) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('documents/editDocDescription');
		}
	}

	public function updateRequest() {
		if ($_SESSION['type'] != 1) {
			$this->load->view('errors/index.html');
		} else {
			try {
				$em = $this->doctrine->em;
				// Update request
				$request = $em->find('\Entity\Request', $_GET['id']);
				// // Register History
				if (isset($_GET['status']) && $request->getStatusByText() !== $_GET['status'] ||
					(isset($_GET['comment']) && $request->getComment() !== $_GET['comment']) ||
					$_GET['docsAdded'] === "true") {
					// Register History
					$history = new \Entity\History();
					$history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
					$history->setUserResponsable($_SESSION['name'] . ' ' . $_SESSION['lastName']);
					// 2 = Addition (in case edition was only documents addition)
					$history->setTitle(2);
					$history->setOrigin($request);
					$request->addHistory($history);
					// Register it's corresponding actions
					if (isset($_GET['status']) && $request->getStatusByText() !== $_GET['status']) {
						// 3 = Modification
						$history->setTitle(3);
						$action = new \Entity\HistoryAction();
						$action->setSummary("Cambio en el estado de la solicitud.");
						$action->setDetail("Nuevo estado: " . $_GET['status']);
						$action->setBelongingHistory($history);
						$history->addAction($action);
						$em->persist($action);
					}
					if (isset($_GET['comment']) && $request->getComment() !== $_GET['comment']) {
						// 3 = Modification
						$history->setTitle(3);
						$action = new \Entity\HistoryAction();
						$action->setSummary("Comentario acerca de la solicitud.");
						$action->setDetail("Comentario realizado: " . $_GET['comment']);
						$action->setBelongingHistory($history);
						$history->addAction($action);
						$em->persist($action);
					}
					$em->persist($history);
				}

				$request->setStatusByText($_GET['status']);
				if (isset($_GET['comment'])) {
					$request->setComment($_GET['comment']);
				}
				$em->merge($request);
				$em->flush();
				if (isset($history)) {
					// Must do it after flushing, so we can get
					// the database-generated id
					$result['historyId'] = $history->getId();
				}
				$result['message'] = "success";
			} catch (Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = "error";
			}

			echo json_encode($result);
		}
	}

	public function updateDocDescription() {
		if ($_SESSION['type'] != 1) {
			$this->load->view('errors/index.html');
		} else {
			try {
				$em = $this->doctrine->em;
				// Update document's description
				$document = $em->find('\Entity\Document', $_GET['id']);
				// Register History
				if ($document->getDescription() != $_GET['description']) {
					$history = new \Entity\History();
					$history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
					$history->setUserResponsable($_SESSION['name'] . ' ' . $_SESSION['lastName']);
					// 3 = Modification
					$history->setTitle(3);
					$request = $document->getBelongingRequest();
					$history->setOrigin($request);
					$request->addHistory($history);
					$em->merge($request);
					// Register it's corresponding action
					$action = new \Entity\HistoryAction();
					$action->setSummary("Descripción del documento '" . $document->getName() . "' modificada.");
					$action->setDetail("Nueva descripción: " . $_GET['description']);
					$action->setBelongingHistory($history);
					$history->addAction($action);
					$em->persist($action);
					$em->persist($history);
					// Update description
					$document->setDescription($_GET['description']);
					$em->merge($document);
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
}
