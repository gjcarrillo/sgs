<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class ManageRequestController extends CI_Controller {
	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
		if ($_SESSION['type'] != 2) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('documents/manageRequest');
		}
	}

	public function updateRequest() {
		if ($_SESSION['type'] != 2) {
			$this->load->view('errors/index.html');
		} else {
			try {
				$em = $this->doctrine->em;
				// Update request
				$request = $em->find('\Entity\Request', $_GET['id']);
				// // Register History
				if (isset($_GET['status']) && $request->getStatusByText() !== $_GET['status'] ||
					(isset($_GET['comment']) && $request->getComment() !== $_GET['comment']) ||
					(isset($_GET['reunion']) && $request->getReunion() !== $_GET['reunion'])) {
					// Register History
					$history = new \Entity\History();
					$history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
					$history->setUserResponsable($_SESSION['name'] . ' ' . $_SESSION['lastName']);
					// 3 = Modification
					$history->setTitle(3);
					$history->setOrigin($request);
					$request->addHistory($history);
					// Register it's corresponding actions
					if (isset($_GET['status']) && $request->getStatusByText() !== $_GET['status']) {
						// 4 = Close
						$history->setTitle(4);
						$action = new \Entity\HistoryAction();
						$action->setSummary("Cierre de solicitud.");
						if (isset($_GET['approvedAmount'])) {
							$approvedAmount = number_format($_GET['approvedAmount'], 2);
							$action->setDetail(
								"Sugerencia: " . $this->statusToVerb($_GET['status']) . " solicitud. Monto aprobado: Bs " . $approvedAmount
							);
						} else {
							$action->setDetail("Nuevo estado: " . $_GET['status']);
						}
						$action->setBelongingHistory($history);
						$history->addAction($action);
						$em->persist($action);
					}
					if (isset($_GET['comment']) && $request->getComment() !== $_GET['comment']) {
						$action = new \Entity\HistoryAction();
						$action->setSummary("Comentario acerca de la solicitud.");
						$action->setDetail("Comentario realizado: " . $_GET['comment']);
						$action->setBelongingHistory($history);
						$history->addAction($action);
						$em->persist($action);
					}
                    if (isset($_GET['reunion']) && $request->getReunion() !== $_GET['reunion']) {
                        $action = new \Entity\HistoryAction();
                        $action->setSummary("Número de reunión especificado.");
                        $action->setDetail("Reunión #" . $_GET['reunion']);
                        $action->setBelongingHistory($history);
                        $history->addAction($action);
                        $em->persist($action);
                    }
					$em->persist($history);
				}

				if ($_GET['status'] == "Aprobada" && isset($_GET['approvedAmount'])) {
					$request->setApprovedAmount($_GET['approvedAmount']);
				}
				if (isset($_GET['reunion'])) {
					$request->setReunion($_GET['reunion']);
				}
				$request->setStatusByText($_GET['status']);
				if (isset($_GET['comment'])) {
					$request->setComment($_GET['comment']);
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

	public function statusToVerb($status) {
		return ($status == "Aprobada" ? "aprobar" : "rechazar");
	}
}
