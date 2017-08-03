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
		$this->load->view('templates/dialogs/editRequest');
	}

	public function editionDialog() {
		$this->load->view('templates/dialogs/editDocDescription');
	}

	/**
	 * Update request for Agent users (i.e. comment & files attachment)
	 */
	public function updateRequest() {
		if ($this->session->type != AGENT && $this->session->type != REVISER) {
			$result['message'] = 'forbidden';
		} else {
			$data = json_decode(file_get_contents('php://input'), true);
			try {
                $loanTypes = $this->configModel->getLoanTypes();
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
					if (isset($data['newDocs'])) {
						$changes = $changes . $this->requests->addDocuments($request, $history, $data['newDocs'], false);
					}
					$em->persist($history);
                    $em->flush();
                    $result['request'] = $this->utils->reqToArray($request);
                    $this->load->model('emailModel');
                    $this->emailModel->sendRequestUpdateEmail(
                        $request->getId(),
                        $loanTypes[$request->getLoanType()]->DescripcionDelPrestamo,
                        $changes
                    );
                    $result['message'] = "success";
				}
			} catch (Exception $e) {
				$result['message'] = $this->utils->getErrorMsg($e);
			}
		}
		echo json_encode($result);
	}

	public function editRequest() {
		$data = json_decode(file_get_contents('php://input'), true);
		if ($data['userId'] != $this->session->id && $this->session->type != AGENT) {
			// Only agents can edit requests for other people
			$result['message'] = 'forbidden';
		} else {
			try {
				$em = $this->doctrine->em;
				$loanTypes = $this->configModel->getLoanTypes();
				// Validate incoming data.
				switch (intval($data['loanType'], 10)) {
					case CASH_VOUCHER:
						$this->requests->validateCashVoucherCreation($data, true);
						break;
					case PERSONAL_LOAN:
						$this->requests->validatePersonalLoanCreation($data, true);
						break;
				}
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
						$action->setDetail("Cambiado de " . $loanTypes[$request->getLoanType()]->DescripcionDelPrestamo .
										   " a " . $loanTypes[$data['loanType']]->DescripcionDelPrestamo);
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
					if (!$request->getAdditionalDeductions()->isEmpty() || !isset($data['deductions'])) {
						console.log($data['deductions']);
						// Delete all deductions from request.
						$this->deleteDeductions($request, $history);
					} else if (isset($data['deductions'])) {
						// Update original deductions (and add new ones).
						$this->updateDeductions($request, $data['deductions'], $history);
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
			} catch (Exception $e) {
				$result['message'] = $this->utils->getErrorMsg($e);
			}
		}
		echo json_encode($result);
	}

	public function updateDocDescription() {
		if ($this->session->type == APPLICANT) {
			$result['message'] = 'forbidden';
		} else {
            $data = json_decode(file_get_contents('php://input'), true);
			try {
                $loanTypes = $this->configModel->getLoanTypes();
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
						$this->load->model('emailModel');
                        $this->emailModel->sendRequestUpdateEmail(
                            $request->getId(),
                            $loanTypes[$request->getLoanType()]->DescripcionDelPrestamo,
                            $changes
                        );
						$em->flush();
						$result['request'] = $this->utils->reqToArray($request);
					}
					$result['message'] = "success";
				}
			} catch (Exception $e) {
				$result['message'] = $this->utils->getErrorMsg($e);
			}
		}
		echo json_encode($result);
	}

	private function addDeductions ($request, $deductions, $history) {
		try {
			switch ($request->getLoanType()) {
				case PERSONAL_LOAN:
					$this->requests->addDeductions($request, $deductions, $history);
					break;
			}
		} catch (Exception $e) {
			throw $e;
		}
	}

	private function updateDeductions ($request, $deductions, $history) {
		try {
			switch ($request->getLoanType()) {
				case PERSONAL_LOAN:
					$this->requests->updateDeductions($request, $deductions, $history);
					break;
			}
		} catch (Exception $e) {
			throw $e;
		}
	}

	private function deleteDeductions ($request, $history) {
		try {
			switch ($request->getLoanType()) {
				case PERSONAL_LOAN:
					$this->requests->deleteDeductions($request, $history);
					break;
			}
		} catch (Exception $e) {
			throw $e;
		}
	}
}
