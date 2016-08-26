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
			$data = json_decode(file_get_contents('php://input'), true);
			try {
				$em = $this->doctrine->em;
				// Update request
				$request = $em->find('\Entity\Request', $data['id']);
				// Register History
				$history = new \Entity\History();
				$history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
				$history->setUserResponsable($_SESSION['name'] . ' ' . $_SESSION['lastName']);
				// 2 = Addition (in case edition was only documents addition)
				$history->setTitle(2);
				$history->setOrigin($request);
				$request->addHistory($history);
				// Register it's corresponding actions
				if (isset($data['comment']) && $request->getComment() !== $data['comment']) {
					// 3 = Modification
					$history->setTitle(3);
					$action = new \Entity\HistoryAction();
					$action->setSummary("Comentario acerca de la solicitud.");
					$action->setDetail("Comentario realizado: " . $data['comment']);
					$action->setBelongingHistory($history);
					$history->addAction($action);
					$em->persist($action);
				}
				$em->persist($history);
				$request->setStatusByText($data['status']);
				if (isset($data['comment'])) {
					$request->setComment($data['comment']);
				}
				$em->merge($request);
				$em->flush();
				if (isset($history)) {
					// Must do it after flushing, so we can get
					// the database-generated id
					$result['historyId'] = $history->getId();
				}
				$em->clear();
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
			$data = json_decode(file_get_contents('php://input'), true);
			try {
				$em = $this->doctrine->em;
				// Update document's description
				$document = $em->find('\Entity\Document', $data['id']);
				// Register History
				if ($document->getDescription() != $data['description']) {
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
					$action->setDetail("Nueva descripción: " . $data['description']);
					$action->setBelongingHistory($history);
					$history->addAction($action);
					$em->persist($action);
					$em->persist($history);
					// Update description
					$document->setDescription($data['description']);
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
