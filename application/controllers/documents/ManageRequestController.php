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
						$action->setDetail("Nuevo estado: " . $_GET['status']);
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
                        $action->setSummary("Número de reunión especificado");
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

	public function fetchRequestsByStatus() {
		if ($_SESSION['type'] != 2) {
			$this->load->view('errors/index.html');
		} else {
			try {
				$em = $this->doctrine->em;
				// Look for all requests with the specified status
				$status = $this->getStatusByText($_GET['status']);
				$requests = $em->getRepository('\Entity\Request')->findBy(array("status" => $status));
				if (empty($requests)) {
					$result['error'] = "No se encontraron solicitudes con estatus " . $_GET['status'];
				} else {
					foreach ($requests as $rKey => $request) {
						$result['requests'][$rKey]['id'] = $request->getId();
						$result['requests'][$rKey]['creationDate'] = $request->getCreationDate()->format('d/m/y');
						$result['requests'][$rKey]['comment'] = $request->getComment();
						$result['requests'][$rKey]['reqAmount'] = $request->getRequestedAmount();
						$result['requests'][$rKey]['approvedAmount'] = $request->getApprovedAmount();
						$result['requests'][$rKey]['reunion'] = $request->getReunion();
						$result['requests'][$rKey]['status'] = $request->getStatusByText();
						$result['requests'][$rKey]['userOwner'] = $request->getUserOwner()->getId();
						$result['requests'][$rKey]['showList'] = false;
						$docs = $request->getDocuments();
						foreach ($docs as $dKey => $doc) {
							$result['requests'][$rKey]['docs'][$dKey]['id'] = $doc->getId();
							$result['requests'][$rKey]['docs'][$dKey]['name'] = $doc->getName();
							$result['requests'][$rKey]['docs'][$dKey]['description'] = $doc->getDescription();
							$result['requests'][$rKey]['docs'][$dKey]['lpath'] = $doc->getLpath();
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
	}

	public function fetchRequestsByDateInterval() {
		if ($_SESSION['type'] != 2) {
			$this->load->view('errors/index.html');
		} else {
			try {
				// from first second of the day
				$from = date_create_from_format(
					'd/m/Y H:i:s',
					$_GET['from'] . ' ' . '00:00:00',
					new DateTimeZone('America/Barbados')
				);
				// to last second of the day
				$to = date_create_from_format(
					'd/m/Y H:i:s',
					$_GET['to'] . ' ' . '23:59:59',
					new DateTimeZone('America/Barbados')
				);
				$em = $this->doctrine->em;
	            $query = $em->createQuery('SELECT t FROM \Entity\Request t WHERE t.creationDate BETWEEN ?1 AND ?2');
	            $query->setParameter(1, $from);
	            $query->setParameter(2, $to);
	            $requests = $query->getResult();
				if (empty($requests)) {
					$interval = $from->diff($to);
					$days = $interval->format("%a");
					if ($days > 0) {
						$result['error'] = "No se han encontrado solicitudes para el rango de fechas especificado";
					} else {
						$result['error'] = "No se han encontrado solicitudes para la fecha especificada";
					}
				} else {
					foreach ($requests as $rKey => $request) {
						$result['requests'][$rKey]['id'] = $request->getId();
						$result['requests'][$rKey]['creationDate'] = $request->getCreationDate()->format('d/m/y');
						$result['requests'][$rKey]['comment'] = $request->getComment();
						$result['requests'][$rKey]['reqAmount'] = $request->getRequestedAmount();
						$result['requests'][$rKey]['approvedAmount'] = $request->getApprovedAmount();
						$result['requests'][$rKey]['reunion'] = $request->getReunion();
						$result['requests'][$rKey]['status'] = $request->getStatusByText();
						$result['requests'][$rKey]['userOwner'] = $request->getUserOwner()->getId();
						$result['requests'][$rKey]['showList'] = false;
						$docs = $request->getDocuments();
						foreach ($docs as $dKey => $doc) {
							$result['requests'][$rKey]['docs'][$dKey]['id'] = $doc->getId();
							$result['requests'][$rKey]['docs'][$dKey]['name'] = $doc->getName();
							$result['requests'][$rKey]['docs'][$dKey]['description'] = $doc->getDescription();
							$result['requests'][$rKey]['docs'][$dKey]['lpath'] = $doc->getLpath();
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
	}

	public function getApprovedAmountByDateInterval() {
		if ($_SESSION['type'] != 2) {
			$this->load->view('errors/index.html');
		} else {
			try {
				$em = $this->doctrine->em;
				// Compute approved amount within specified time
				// from first second of the day
				$from = date_create_from_format(
					'd/m/Y H:i:s',
					$_GET['from'] . ' ' . '00:00:00',
					new DateTimeZone('America/Barbados')
				);
				// to last second of the day
				$to = date_create_from_format(
					'd/m/Y H:i:s',
					$_GET['to'] . ' ' . '23:59:59',
					new DateTimeZone('America/Barbados')
				);
				$em = $this->doctrine->em;
	            $query = $em->createQuery
					('SELECT t FROM \Entity\Request t WHERE t.creationDate BETWEEN ?1 AND ?2');
	            $query->setParameter(1, $from);
	            $query->setParameter(2, $to);
	            $requests = $query->getResult();
				if (empty($requests)) {
					$interval = $from->diff($to);
					$days = $interval->format("%a");
					if ($days > 0) {
						$result['error'] = "No se han encontrado solicitudes para el rango de fechas especificado";
					} else {
						$result['error'] = "No se han encontrado solicitudes para la fecha especificada";
					}
				} else {
					// Perform all approved amount's computation
					$result['approvedAmount'] = 0;
					foreach ($requests as $rKey => $request) {
						if ($request->getApprovedAmount() !== null) {
							$result['approvedAmount'] += $request->getApprovedAmount();
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
	}

	public function getApprovedAmountById() {
		if ($_SESSION['type'] != 2) {
			$this->load->view('errors/index.html');
		} else {
			try {
				$em = $this->doctrine->em;
				$user = $em->find('\Entity\User', $_GET['userId']);
				if ($user === null) {
					$result['error'] = "La cédula ingresada no se encuentra en la base de datos";
				} else {
					$requests = $user->getRequests();
					if ($requests->isEmpty()) {
						$result['error'] = "El usuario especificado no posee solicitudes";
					} else {
						// Perform all approved amount's computation
						$result['approvedAmount'] = 0;
						$result['username'] = $user->getName() . ' ' . $user->getLastName();
						foreach ($requests as $rKey => $request) {
							if ($request->getApprovedAmount() !== null) {
								$result['approvedAmount'] += $request->getApprovedAmount();
							}
						}
						$result['message'] = "success";
					}
				}
			} catch (Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = "error";
			}
			echo json_encode($result);
		}
	}

	public function getStatusByText($status) {
		if ($_SESSION['type'] != 2) {
			$this->load->view('errors/index.html');
		} else {
			return ($status == "Recibida" ? 1 : ($status == "Aprobada" ? 2 : 3));
		}
	}
}
