<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');
use Ramsey\Uuid\Uuid;

class ManageRequestController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
		$this->load->view('templates/dialogs/manageRequest');
	}

	public function updateRequest() {
		if ($this->session->type != MANAGER) {
			$result['message'] = 'forbidden';
		} else {
			$data = json_decode(file_get_contents('php://input'), true);
			try {
                $loanTypes = $this->configModel->getLoanTypes();
				$em = $this->doctrine->em;
				// Update request
				$request = $em->find('\Entity\Request', $data['id']);
				$this->load->model('requestsModel', 'requests');
				if (!$this->requests->isRequestValidated($request) || $this->requests->isRequestClosed($request)) {
					// Request must be valid & not yet closed.
					$result['message'] = 'Esta solicitud no puede ser modificada.';
				} else {
					// Register History
					$history = new \Entity\History();
					$changes = '';
					$history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
					$history->setUserResponsible($this->users->getUser($this->session->id));
					$history->setTitle($this->utils->getHistoryActionCode('update'));
					$history->setOrigin($request);
					$request->addHistory($history);
					// Register it's corresponding actions
					if (isset($data['status']) && $data['status'] != $request->getStatus()) {
						$action = new \Entity\HistoryAction();
						$action->setSummary("Cambio de estatus.");
						if ($data['status'] === REJECTED) {
							$history->setTitle($this->utils->getHistoryActionCode('closure'));
							$action->setSummary("Cierre de solicitud.");
						} else if ($data['status'] === PRE_APPROVED) {
							$approvedAmount = number_format($data['approvedAmount'], 2);
							$action->setDetail(
								"Status: " . $data['status'] . ". Monto pre-aprobado: Bs " . $approvedAmount
							);
						} else {
							$action->setDetail("Nuevo estatus: " . $data['status']);
						}
						$action->setBelongingHistory($history);
						$history->addAction($action);
						$em->persist($action);
						$changes = $changes .
								   "<li>Cambio de estatus: <s>" . $request->getStatus() .
								   "</s> " . $data['status'] . "." . "</li>";
					}
					if (isset($data['comment']) && $request->getComment() !== $data['comment']) {
						$action = new \Entity\HistoryAction();
						$action->setSummary("Comentario acerca de la solicitud.");
						$action->setDetail("Comentario realizado: " . $data['comment']);
						$changes = $changes . '<li>Comentario realizado: ' . $data['comment'] . '</li>';
						$action->setBelongingHistory($history);
						$history->addAction($action);
						$em->persist($action);
					}
					if (isset($data['reunion']) && $request->getReunion() !== $data['reunion']) {
						$action = new \Entity\HistoryAction();
						$action->setSummary("Número de reunión especificado.");
						$action->setDetail("Reunión #" . $data['reunion']);
						$changes = $changes . '<li>Número de reunión especificado: ' . $data['reunion'] . '</li>';
						if ($data['status'] == PRE_APPROVED) {
							$changes = $changes . '<br/><div>El préstamo solicitado está siendo abonado. En menos de 24h ' .
									   'hábiles estaremos notificándole al respecto.</div>';
						}
						$action->setBelongingHistory($history);
						$history->addAction($action);
						$em->persist($action);
					}
                    // Add new additional documents (if any).
                    if (isset($data['newDocs'])) {
                        $changes = $changes . $this->requests->addDocuments($request, $history, $data['newDocs'], false);
                    }
					$em->persist($history);

					if ($data['status'] === PRE_APPROVED && isset($data['approvedAmount'])) {
						$request->setApprovedAmount($data['approvedAmount']);
						// Add approval document
						$uuid4 = Uuid::uuid4();
						$code = $uuid4->toString(); // i.e. 25769c6c-d34d-4bfe-ba98-e0ee856f3e7a
						$docs = array();
						$doc = array(
							'docName' => 'Monto abonado',
							'description' => 'Detalles del monto abonado',
							'lpath' => $request->getUserOwner()->getId() . '.' . $code . '.' . 'Monto abonado.pdf'
						);
						array_push($docs, $doc);
						$changes = $changes . $this->requests->addDocuments($request, $history, $docs, true);
						$this->requests->generateApprovalDocument($request, $doc);
						// ALERT: FOR TESTING PURPOSES ONLY!!!! DELETE LATER!
						$this->requests->addGrantingDate($request);
					}
					if (isset($data['reunion'])) {
						$request->setReunion($data['reunion']);
					}
					$request->setStatus($data['status']);
					if (isset($data['comment'])) {
						$request->setComment($data['comment']);
					}
					$em->merge($request);
					$this->load->model('emailModel', 'email');
					$this->email->sendRequestUpdateEmail(
                        $request->getId(),
                        $loanTypes[$request->getLoanType()]->DescripcionDelPrestamo,
                        $changes
                    );
					$em->flush();
                    $result['request'] = $this->utils->reqToArray($request);
					$result['message'] = "success";
				}
			} catch (Exception $e) {
				$result['message'] = $this->utils->getErrorMsg($e);
			}
		}
		echo json_encode($result);
	}
}
