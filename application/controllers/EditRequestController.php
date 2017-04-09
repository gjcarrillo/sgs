<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class EditRequestController extends CI_Controller {
	public function __construct() {
        parent::__construct();
        $this->load->library('session');
		$this->load->model('requestsModel', 'requests');
	}

	public function index() {
		if ($_SESSION['type'] != AGENT) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('templates/editRequest');
		}
	}

	public function editionDialog() {
		if ($_SESSION['type'] != AGENT) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('templates/editDocDescription');
		}
	}

	public function emailEditionDialog() {
		if ($_SESSION['type'] != APPLICANT) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('templates/editEmail');
		}
	}

	/**
	 * Update request for Agent users (i.e. comment & files attachment)
	 */
	public function updateRequest() {
		if ($_SESSION['type'] != AGENT) {
			$this->load->view('errors/index.html');
		} else {
			$data = json_decode(file_get_contents('php://input'), true);
			try {
				$em = $this->doctrine->em;
				// Update request
				$request = $em->find('\Entity\Request', $data['id']);
				if (!$this->requests->isRequestValidated($request) || $this->requests->isRequestClosed($request)) {
					// request must be validated and not yet closed.
					throw new Exception('Esta solicitud no puede ser modificada.');
				} else {
					// Register History
					$history = new \Entity\History();
					$history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
					$history->setUserResponsible($this->users->getUser($this->session->id));
					$history->setTitle($this->utils->getHistoryActionCode('update'));
					$history->setOrigin($request);
					$request->addHistory($history);
					$changes = '';
					// Register it's corresponding actions
					if (isset($data['comment']) && $request->getComment() !== $data['comment']) {
						$action = new \Entity\HistoryAction();
						$action->setSummary("Comentario acerca de la solicitud.");
						$action->setDetail("Comentario realizado: " . $data['comment']);
						$changes = $changes . '<li>Comentario realizado: ' . $data['comment'] . '</li>';
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
					$changes = $changes . $this->requests->addDocuments($request, $history, $data['newDocs']);
					$em->persist($history);
					$em->flush();
					$result['request'] = $this->utils->reqToArray($request);
					$this->load->model('emailModel', 'email');
					$this->email->sendRequestUpdateEmail($request->getId(), $changes);
					$result['message'] = "success";
				}
			} catch (Exception $e) {
				$result['message'] = $this->utils->getErrorMsg($e);
			}

			echo json_encode($result);
		}
	}

	public function editRequest() {
		$data = json_decode(file_get_contents('php://input'), true);
		if ($data['userId'] != $_SESSION['id'] && $_SESSION['type'] != AGENT) {
			// Only agents can edit requests for other people
			$this->load->view('errors/index.html');
		} else {
			try {
				$em = $this->doctrine->em;
				$this->load->model('configModel');
				$this->load->model('userModel');
				$maxAmount = $this->configModel->getMaxReqAmount();
				$minAmount = $this->configModel->getMinReqAmount();
				$loanTypes = $this->configModel->getLoanTypes();
				$userData = $this->userModel->getPersonalData($data['userId']);
				$lastLoan = $this->requests->getLastLoanInfo($data['userId'], $data['loanType']);
				$terms = $this->utils->extractLoanTerms($loanTypes[$data['loanType']]);
				$diff = $this->utils->getDateInterval(
					new DateTime('now', new DateTimeZone('America/Barbados')),
					date_create_from_format('d/m/Y', $userData->ingreso)
				);
				if ($userData->concurrencia >= 40) {
					$result['message'] = "Concurrencia muy alta (40% ó más)";
				} else if ($data['loanType'] == 40 && ($diff['months'] + ($diff['years'] * 12) < 6)) {
					$result['message'] = "Deben transcurrir seis meses desde su fecha de ingreso.";
				} else if ($this->requests->getSpanLeft($data['userId'], $data['loanType']) > 0 &&
					($lastLoan != null && $lastLoan->saldo_edo > 0)) {
					// Span between requests of same type not yet through.
					$span = $em->getRepository('\Entity\Config')->findOneBy(array('key' => 'SPAN'))->getValue();
					$result['message'] = "No ha" . ($span == 1 ? "" : "n") .
										 " transcurrido al menos " . $span . ($span == 1 ? " mes " : " meses ") .
										 "desde su última otorgación de préstamo del tipo: " .
										 $this->utils->mapLoanType($data['loanType']);
				} else if ($data['reqAmount'] < $minAmount || $data['reqAmount'] > $maxAmount) {
					$result['message'] = 'Monto solicitado no válido.';
				} else if (!in_array($data['due'], $terms)) {
					$result['message'] = 'Plazo de pago no válido.';
				} else if (!$this->utils->isRequestTypeValid($loanTypes, $data['loanType'])) {
					$result['message'] = 'Tipo de préstamo inválido.';
				} else {
					// Update request
					$request = $em->find('\Entity\Request', $data['rid']);
					if ($request->getValidationDate()) {
						$result['message'] = 'Solicitud ya validada. Edición no permitida.';
					} else {
						// Register History
						$history = new \Entity\History();
						$history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
						$history->setUserResponsible($this->users->getUser($this->session->id));
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
							$action->setDetail("Cambiado de  " . $request->getContactEmail() .
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
						$result['request'] = $this->utils->reqToArray($request);
						$this->requests->generateRequestDocument($request);
						$em->flush();
						$result['message'] = "success";
					}
				}
			} catch (Exception $e) {
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
				$document = $em->find('\Entity\Document', $data['id']);
				$request = $document->getBelongingRequest();
				if (!$this->requests->isRequestValidated($request) || $this->requests->isRequestClosed($request)) {
					// request must be validated and not yet closed.
					throw new Exception('Esta solicitud no puede ser modificada.');
				} else {
					// Register History
					if ($document->getDescription() != $data['description']) {
						$history = new \Entity\History();
						$history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
						$history->setUserResponsible($this->users->getUser($this->session->id));
						$history->setTitle($this->utils->getHistoryActionCode('update'));
						$request = $document->getBelongingRequest();
						$history->setOrigin($request);
						$request->addHistory($history);
						$em->merge($request);
						// Register it's corresponding action
						$action = new \Entity\HistoryAction();
						$action->setSummary("Descripción del documento '" . $document->getName() . "' actualizada.");
						$action->setDetail("Nueva descripción: " . $data['description']);
						$changes = "<li>Descripción del document '" . $document->getName() . "' actualizada. " .
								   "Nueva descripción: " . $data['description'] . "</li>";
						$action->setBelongingHistory($history);
						$history->addAction($action);
						$em->persist($action);
						$em->persist($history);
						// Update doc description
						$document->setDescription($data['description']);
						$em->merge($document);
						$this->load->model('emailModel', 'email');
						$this->email->sendRequestUpdateEmail($request->getId(), $changes);
						$em->flush();
						$result['request'] = $this->utils->reqToArray($request);
					}
					$result['message'] = "success";
				}
			} catch (Exception $e) {
				$result['message'] = $this->utils->getErrorMsg($e);
			}

			echo json_encode($result);
		}
	}
}
