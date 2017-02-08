<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class EditRequestController extends CI_Controller {
	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
		if ($_SESSION['type'] != AGENT) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('editRequest');
		}
	}

	public function editionDialog() {
		if ($_SESSION['type'] != AGENT) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('editDocDescription');
		}
	}

	public function emailEditionDialog() {
		\ChromePhp::log($_SESSION['type']);
		if ($_SESSION['type'] != APPLICANT) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('editEmail');
		}
	}

	public function updateRequest() {
		if ($_SESSION['type'] != AGENT) {
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
				// Addition (in case edition was only documents addition)
				$history->setTitle($this->utils->getHistoryActionCode('addition'));
				$history->setOrigin($request);
				$request->addHistory($history);
				// Register it's corresponding actions
				if (isset($data['comment']) && $request->getComment() !== $data['comment']) {
					$history->setTitle($this->utils->getHistoryActionCode('update'));
					$action = new \Entity\HistoryAction();
					$action->setSummary("Comentario acerca de la solicitud.");
					$action->setDetail("Comentario realizado: " . $data['comment']);
					$action->setBelongingHistory($history);
					$history->addAction($action);
					$em->persist($action);
				}
				$em->persist($history);
				$request->setStatus($data['status']);
				if (isset($data['comment'])) {
					$request->setComment($data['comment']);
				}
				$em->merge($request);
				$em->flush();
				$this->load->model('requestsModel', 'requests');
				$this->requests->addDocuments($request, $history->getId(), $data['newDocs']);
				$em->clear();
				$result['message'] = "success";
			} catch (Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = $this->utils->getErrorMsg($e);
			}

			echo json_encode($result);
		}
	}

	public function editRequest() {
		$data = json_decode(file_get_contents('php://input'), true);
		\ChromePhp::log($data);
		if ($data['userId'] != $_SESSION['id'] && $_SESSION['type'] != AGENT) {
			// Only agents can edit requests for other people
			$this->load->view('errors/index.html');
		} else {
			try {
				$em = $this->doctrine->em;
				// Update request
				$request = $em->find('\Entity\Request', $data['rid']);
				if ($request->getValidationDate() != null) {
					$result['message'] = 'Información de solicitud ya validada.';
				} else {
					// Register History
					$history = new \Entity\History();
					$history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
					$history->setUserResponsable($_SESSION['name'] . ' ' . $_SESSION['lastName']);
					$history->setTitle($this->utils->getHistoryActionCode('modification'));
					$history->setOrigin($request);
					$request->addHistory($history);
					// Register it's corresponding actions
					if ($request->getRequestedAmount() != $data['reqAmount']) {
						$action = new \Entity\HistoryAction();
						$action->setSummary("Monto solicitado cambiado.");
						$action->setDetail("Cambiado de Bs " . number_format($request->getRequestedAmount(), 2) .
										   " a Bs " . number_format($data['reqAmount'], 2));
						$action->setBelongingHistory($history);
						$request->setRequestedAmount($data['reqAmount']);
						$history->addAction($action);
						$em->persist($action);
					}
					if ($request->getLoanType() != $data['loanType']) {
						$action = new \Entity\HistoryAction();
						$action->setSummary("Tipo de préstamo cambiado.");
						$action->setDetail("Cambiado de " . $this->utils->mapLoanType($request->getLoanType()) .
										   " a " . $this->utils->mapLoanType($data['loanType']));
						$action->setBelongingHistory($history);
						$request->setLoanType($data['loanType']);
						$history->addAction($action);
						$em->persist($action);
					}
					if ($request->getPaymentDue() != $data['due']) {
						$action = new \Entity\HistoryAction();
						$action->setSummary("Plazo para pagar cambiado.");
						$action->setDetail("Cambiado de " . $request->getPaymentDue() .
										   " meses a " . $data['due'] . " meses");
						$action->setBelongingHistory($history);
						$request->setPaymentDue($data['due']);
						$history->addAction($action);
						$em->persist($action);
					}
					if ($request->getContactNumber() != $data['tel']) {
						$action = new \Entity\HistoryAction();
						$action->setSummary("Número celular cambiado.");
						$action->setDetail("Cambiado de " . $request->getContactNumber() .
										   " a " . $data['tel']);
						$action->setBelongingHistory($history);
						$request->setContactNumber($data['tel']);
						$history->addAction($action);
						$em->persist($action);
					}
					if ($request->getContactEmail() != $data['email']) {
						$action = new \Entity\HistoryAction();
						$action->setSummary("Correo electrónico cambiado.");
						$action->setDetail("Cambiado de  " .$request->getContactEmail() .
										   " a " . $data['email']);
						$action->setBelongingHistory($history);
						$request->setContactEmail($data['email']);
						$history->addAction($action);
						$em->persist($action);
					}
					// This function will be called if at least one field was edited, so
					// we can register History without any previous validation.
					$em->persist($history);
					$em->merge($request);
					$em->flush();
					$this->load->model('requestsModel', 'requests');
					$this->requests->generateRequestDocument($request);
					$this->sendValidation($request->getId());;
					$result['message'] = "success";
				}
			} catch (Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = $this->utils->getErrorMsg($e);
			}

			echo json_encode($result);
		}
	}

	public function updateDocDescription() {
		if ($_SESSION['type'] != AGENT) {
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
					$history->setTitle($this->utils->getHistoryActionCode('update'));
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
				$result['message'] = $this->utils->getErrorMsg($e);
			}

			echo json_encode($result);
		}
	}

	public function updateEmail() {
		if ($_SESSION['type'] != APPLICANT) {
			$this->load->view('errors/index.html');
		} else {
			$data = json_decode(file_get_contents('php://input'), true);
			try {
				$em = $this->doctrine->em;
				// Update request
				$request = $em->find('\Entity\Request', $data['reqId']);
				$request->setContactEmail($data['newAddress']);
				// Register History
				$history = new \Entity\History();
				$history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
				$history->setUserResponsable($_SESSION['name'] . ' ' . $_SESSION['lastName']);
				// Register it's corresponding actions
				$history->setTitle($this->utils->getHistoryActionCode('modification'));
				$history->setOrigin($request);
				$action = new \Entity\HistoryAction();
				$action->setSummary("Cambio de correo electrónico.");
				$action->setDetail("Nuevo correo electrónico: " . $data['newAddress']);
				$action->setBelongingHistory($history);
				$history->addAction($action);
				$em->persist($action);
				$em->persist($history);
				$em->merge($request);
				$em->flush();
				$em->clear();
				$this->load->model('requestsModel', 'requests');
				$this->requests->generateRequestDocument($request);
				$result['message'] = "success";
			} catch (Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = $this->utils->getErrorMsg($e);
			}

			echo json_encode($result);
		}
	}

	private function sendValidation($reqId) {
		try {
			$this->load->model('emailModel', 'email');
			$this->email->sendNewRequestEmail($reqId);
			$this->load->model('historyModel', 'history');
			$this->history->registerValidationResend($reqId);
		} catch (Exception $e) {
			\ChromePhp::log($e);
			throw $e;
		}
	}
}
