<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class ManageRequestController extends CI_Controller {
	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
		if ($_SESSION['type'] != MANAGER) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('manageRequest');
		}
	}

	public function updateRequest() {
		if ($_SESSION['type'] != MANAGER) {
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
				$history->setTitle($this->utils->getHistoryActionCode('update'));
				$history->setOrigin($request);
				$request->addHistory($history);
				// Register it's corresponding actions
				if (isset($data['status'])) {
					if ($data['status'] === APPROVED || $data['status'] === REJECTED ) {
						$history->setTitle($this->utils->getHistoryActionCode('closure'));
						$action = new \Entity\HistoryAction();
						$action->setSummary("Cierre de solicitud.");
						if ($data['status'] === APPROVED) {
							$approvedAmount = number_format($data['approvedAmount'], 2);
							$action->setDetail(
								"Sugerencia: " . $this->utils->statusToVerb($data['status']) .
								" solicitud. Monto aprobado: Bs " . $approvedAmount
							);
						} else {
							$action->setDetail("Nuevo estatus: " . $data['status']);
						}
						$action->setBelongingHistory($history);
						$history->addAction($action);
						$em->persist($action);
					} else {
						$action = new \Entity\HistoryAction();
						$action->setSummary("Cambio de estatus.");
						$action->setDetail("Nuevo estatus: " . $data['status']);
						$action->setBelongingHistory($history);
						$history->addAction($action);
						$em->persist($action);
					}
				}
				if (isset($data['comment']) && $request->getComment() !== $data['comment']) {
					$action = new \Entity\HistoryAction();
					$action->setSummary("Comentario acerca de la solicitud.");
					$action->setDetail("Comentario realizado: " . $data['comment']);
					$action->setBelongingHistory($history);
					$history->addAction($action);
					$em->persist($action);
				}
                if (isset($data['reunion']) && $request->getReunion() !== $data['reunion']) {
                    $action = new \Entity\HistoryAction();
                    $action->setSummary("Número de reunión especificado.");
                    $action->setDetail("Reunión #" . $data['reunion']);
                    $action->setBelongingHistory($history);
                    $history->addAction($action);
                    $em->persist($action);
                }
				$em->persist($history);

				if ($data['status'] == APPROVED && isset($data['approvedAmount'])) {
					$request->setApprovedAmount($data['approvedAmount']);
				}
				if (isset($data['reunion'])) {
					$request->setReunion($data['reunion']);
				}
				$request->setStatus($data['status']);
				if (isset($data['comment'])) {
					$request->setComment($data['comment']);
				}
				$em->merge($request);
				$em->flush();
				$result['message'] = "success";
			} catch (Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = "error";
			}

			echo json_encode($result);
		}
	}
}
