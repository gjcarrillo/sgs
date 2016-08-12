<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class ManagerHomeController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
		if ($_SESSION['type'] != 2) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('home/managerHome');
		}
	}

	// Obtain all requests with with all their documents.
	// NOTICE: sensitive information
	public function getUserRequests() {
		if ($_GET['fetchId'] != $_SESSION['id'] && $_SESSION['type'] > 2) {
			// if fetch id is not the same as logged in user, must be an
			// agent or manager to be able to execute query!
			$this->load->view('errors/index.html');
		} else {
			try {
				$em = $this->doctrine->em;
				$user = $em->find('\Entity\User', $_GET['fetchId']);
				if ($user === null) {
					$result['error'] = "La cédula ingresada no se encuentra en la base de datos";
				} else {
					$requests = $user->getRequests();
					if ($requests->isEmpty()) {
						$result['error'] = "El usuario no posee solicitudes";
					} else {
						$received = $approved = $rejected = $totalRequested = $totalApproved = 0;
						foreach ($requests as $rKey => $request) {
							$result['requests'][$rKey]['id'] = $request->getId();
							$result['requests'][$rKey]['creationDate'] = $request->getCreationDate()->format('d/m/y');
							$result['requests'][$rKey]['comment'] = $request->getComment();
							$result['requests'][$rKey]['reqAmount'] = $request->getRequestedAmount();
							$result['requests'][$rKey]['approvedAmount'] = $request->getApprovedAmount();
							$result['requests'][$rKey]['reunion'] = $request->getReunion();
							$result['requests'][$rKey]['status'] = $request->getStatusByText();
							$docs = $request->getDocuments();
							foreach ($docs as $dKey => $doc) {
								$result['requests'][$rKey]['docs'][$dKey]['id'] = $doc->getId();
								$result['requests'][$rKey]['docs'][$dKey]['name'] = $doc->getName();
								$result['requests'][$rKey]['docs'][$dKey]['description'] = $doc->getDescription();
								$result['requests'][$rKey]['docs'][$dKey]['lpath'] = $doc->getLpath();
							}
							// Gather pie chart information
							if ($request->getStatusByText() === "Recibida") {
								$received++;
							} else if ($request->getStatusByText() === "Aprobada") {
								$approved++;
							} else if ($request->getStatusByText() === "Rechazada") {
								$rejected++;
							}
							// Gather up report information
							$totalRequested += $result['requests'][$rKey]['reqAmount'];
							$totalApproved = (
								$result['requests'][$rKey]['approvedAmount'] === null ? $totalApproved + 0 : $totalApproved + $result['requests'][$rKey]['approvedAmount']
							);
							$result['report'][$rKey]['id'] = sprintf('%06d', $request->getId());
							$result['report'][$rKey]['creationDate'] = $request->getCreationDate()->format('d/m/y');
							$result['report'][$rKey]['comment'] = $request->getComment();
							$result['report'][$rKey]['reqAmount'] = number_format(
								$request->getRequestedAmount(), 2
							);
							$result['report'][$rKey]['approvedAmount'] = number_format(
								$request->getApprovedAmount(), 2
							);
							$result['report'][$rKey]['reunion'] = $request->getReunion();
							$result['report'][$rKey]['status'] = $request->getStatusByText();
						}
						// Fill up pie chart information
						$result['pie']['title'] = "Estadísticas de solicitudes para el afiliado";
						$result['pie']['labels'][0] = "Recibidas";
						$result['pie']['labels'][1] = "Aprobadas";
						$result['pie']['labels'][2] = "Rechazadas";
						$total = $received + $approved + $rejected;
						$result['pie']['data'][0] = round($received * 100 / $total, 2);
						$result['pie']['data'][1] = round($approved * 100 / $total, 2);
						$result['pie']['data'][2] = round($rejected * 100 / $total, 2);
						$result['pie']['backgroundColor'][0] = "#FFD740"; // A200 amber
						$result['pie']['backgroundColor'][1] = "#00C853"; // A700 green
						$result['pie']['backgroundColor'][2] = "#FF5252"; // A200 red
						$result['pie']['hoverBackgroundColor'][0] = "#FFC107"; // 500 amber
						$result['pie']['hoverBackgroundColor'][1] = "#00E676"; // A400 green
						$result['pie']['hoverBackgroundColor'][2] = "#F44336"; // 500 red
						// Fill up report information
						$dataHeader = array(
							'Identificador', 'Fecha de creación', 'Comentario', 'Monto solicitado (Bs)',
							 'Monto aprobado (Bs)', 'Reunión', 'Estatus'
						 );
						array_unshift($result['report'], $dataHeader);
						$applicant = $user->getId() . ' - ' .$user->getName() . ' ' . $user->getLastName();
						array_unshift($result['report'], array(""));
						array_unshift($result['report'], array("Solicitudes del solicitante: " . $applicant));
						$now = (new DateTime('now', new DateTimeZone('America/Barbados')))->format('d/m/Y - h:i:sa');
						array_unshift($result['report'], array(
							"Fecha y hora de generación de reporte: " . $now
						));
						array_push($result['report'], array(""));
						array_push($result['report'], array(
							"Monto solicitado total: Bs " . number_format($totalRequested, 2))
						);
						array_push($result['report'], array(
							"Monto aprobado total: Bs " . number_format($totalApproved, 2))
						);
						array_push($result['report'], array(""));
						array_push($result['report'],
							array("Solicitudes con estatus Recibida: " . $received . " (" . $result['pie']['data'][0] . "%)"));
						array_push($result['report'],
							array("Solicitudes con estatus Aprobada: " . $approved . " (" . $result['pie']['data'][1] . "%)"));
						array_push($result['report'],
							array("Solicitudes con estatus Rechazada: " . $rejected . " (" . $result['pie']['data'][2] . "%)"));
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

    public function fetchRequestsByStatus() {
        if ($_SESSION['type'] != 2) {
            $this->load->view('errors/index.html');
        } else {
            try {
                $em = $this->doctrine->em;
                // Look for all requests with the specified status
                $status = $this->getStatusByText($_GET['status']);
				$requestsRepo = $em->getRepository('\Entity\Request');
                $requests = $requestsRepo->findBy(array("status" => $status));
                if (empty($requests)) {
                    $result['error'] = "No se encontraron solicitudes con estatus " . $_GET['status'];
                } else {
					$totalRequested = $totalApproved = 0;
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
						// Gather up report information
						$totalRequested += $result['requests'][$rKey]['reqAmount'];
						$result['report'][$rKey]['id'] = sprintf('%06d', $request->getId());
						$result['report'][$rKey]['applicantId'] = $result['requests'][$rKey]['userOwner'];
						$result['report'][$rKey]['creationDate'] = $request->getCreationDate()->format('d/m/y');
						$result['report'][$rKey]['comment'] = $request->getComment();
						$result['report'][$rKey]['reqAmount'] = number_format(
							$request->getRequestedAmount(), 2
						);
						if ($_GET['status'] === "Aprobada") {
							$result['report'][$rKey]['approvedAmount'] = number_format(
								$request->getApprovedAmount(), 2
							);
							$totalApproved = (
								$result['requests'][$rKey]['approvedAmount'] === null ? $totalApproved + 0 : $totalApproved + $result['requests'][$rKey]['approvedAmount']
							);
						}
						if ($_GET['status'] !== "Recibida") {
							$result['report'][$rKey]['reunion'] = $request->getReunion();
						}
                    }
					// Fill up pie chart information
					$received = $_GET['status'] === "Recibida" ? count($requests) : (
						count($requestsRepo->findBy(array("status" => 1)))
					);
					$approved = $_GET['status'] === "Approved" ? count($requests) : (
						count($requestsRepo->findBy(array("status" => 2)))
					);
					$rejected = $_GET['status'] === "Rechazada" ? count($requests) : (
						count($requestsRepo->findBy(array("status" => 3)))
					);
					$result['pie']['title'] = "Estadísticas de solicitudes del sistema";
					$result['pie']['labels'][0] = "Recibidas";
					$result['pie']['labels'][1] = "Aprobadas";
					$result['pie']['labels'][2] = "Rechazadas";
					$total = $received + $approved + $rejected;
					$result['pie']['data'][0] = round($received * 100 / $total, 2);
					$result['pie']['data'][1] = round($approved * 100 / $total, 2);
					$result['pie']['data'][2] = round($rejected * 100 / $total, 2);
					$result['pie']['backgroundColor'][0] = "#FFD740"; // A200 amber
					$result['pie']['backgroundColor'][1] = "#00C853"; // A700 green
					$result['pie']['backgroundColor'][2] = "#FF5252"; // A200 red
					$result['pie']['hoverBackgroundColor'][0] = "#FFC107"; // 500 amber
					$result['pie']['hoverBackgroundColor'][1] = "#00E676"; // A400 green
					$result['pie']['hoverBackgroundColor'][2] = "#F44336"; // 500 red
					// Fill up report information
					$dataHeader = array(
						'Identificador', 'Solicitante', 'Fecha de creación', 'Comentario', 'Monto solicitado (Bs)'
					 );
					if ($_GET['status'] === "Aprobada") {
						array_push($dataHeader, 'Monto aprobado (Bs)');
					}
					if ($_GET['status'] !== "Recibida") {
						array_push($dataHeader, 'Reunión');
					}
					array_unshift($result['report'], $dataHeader);
					array_unshift($result['report'], array(""));
					array_unshift($result['report'], array("Solicitudes en estatus " . $_GET['status']));
					$now = (new DateTime('now', new DateTimeZone('America/Barbados')))->format('d/m/Y - h:i:sa');
					array_unshift($result['report'], array(
						"Fecha y hora de generación de reporte: " . $now
					));
					array_push($result['report'], array(""));
					array_push($result['report'], array(
						"Monto solicitado total: Bs " . number_format($totalRequested, 2))
					);
					if ($_GET['status'] === "Aprobada") {
						array_push($result['report'], array(
							"Monto aprobado total: Bs " . number_format($totalApproved, 2))
						);
					}
					array_push($result['report'], array(""));
					array_push($result['report'], array("Total de solicitudes en el sistema"));
					array_push($result['report'],
						array("Solicitudes con estatus Recibida: " . $received . " (" . $result['pie']['data'][0] . "%)"));
					array_push($result['report'],
						array("Solicitudes con estatus Aprobada: " . $approved . " (" . $result['pie']['data'][1] . "%)"));
					array_push($result['report'],
						array("Solicitudes con estatus Rechazada: " . $rejected . " (" . $result['pie']['data'][2] . "%)"));
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
				// Days will be used below to determine pie title
				$interval = $from->diff($to);
				$days = $interval->format("%a");
                if (empty($requests)) {
                    if ($days > 0) {
                        $result['error'] = "No se han encontrado solicitudes para el rango de fechas especificado";
                    } else {
                        $result['error'] = "No se han encontrado solicitudes para la fecha especificada";
                    }
                } else {
					$received = $approved = $rejected = $totalRequested = $totalApproved = 0;
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
						// Gather pie chart information
						if ($request->getStatusByText() === "Recibida") {
							$received++;
						} else if ($request->getStatusByText() === "Aprobada") {
							$approved++;
						} else if ($request->getStatusByText() === "Rechazada") {
							$rejected++;
						}
						// Gather up report information
						$totalRequested += $result['requests'][$rKey]['reqAmount'];
						$totalApproved = (
							$result['requests'][$rKey]['approvedAmount'] === null ? $totalApproved + 0 : $totalApproved + $result['requests'][$rKey]['approvedAmount']
						);
						$result['report'][$rKey]['id'] = sprintf('%06d', $request->getId());
						$result['report'][$rKey]['applicantId'] = $result['requests'][$rKey]['userOwner'];
						$result['report'][$rKey]['creationDate'] = $request->getCreationDate()->format('d/m/y');
						$result['report'][$rKey]['comment'] = $request->getComment();
						$result['report'][$rKey]['reqAmount'] = number_format(
							$request->getRequestedAmount(), 2
						);
						$result['report'][$rKey]['approvedAmount'] = number_format(
							$request->getApprovedAmount(), 2
						);
						$result['report'][$rKey]['reunion'] = $request->getReunion();
						$result['report'][$rKey]['status'] = $request->getStatusByText();
                    }
					// Fill up pie chart information
					$result['pie']['title'] = $days > 0 ? (
						"Estadísticas de solicitudes para el intervalo de fechas especificado") : (
						"Estadísticas de solicitudes para la fecha especificada"
					);
					$result['pie']['labels'][0] = "Recibidas";
					$result['pie']['labels'][1] = "Aprobadas";
					$result['pie']['labels'][2] = "Rechazadas";
					$total = $received + $approved + $rejected;
					$result['pie']['data'][0] = round($received * 100 / $total, 2);
					$result['pie']['data'][1] = round($approved * 100 / $total, 2);
					$result['pie']['data'][2] = round($rejected * 100 / $total, 2);
					$result['pie']['backgroundColor'][0] = "#FFD740"; // A200 amber
					$result['pie']['backgroundColor'][1] = "#00C853"; // A700 green
					$result['pie']['backgroundColor'][2] = "#FF5252"; // A200 red
					$result['pie']['hoverBackgroundColor'][0] = "#FFC107"; // 500 amber
					$result['pie']['hoverBackgroundColor'][1] = "#00E676"; // A400 green
					$result['pie']['hoverBackgroundColor'][2] = "#F44336"; // 500 red
					// Fill up report information
					$dataHeader = array(
						'Identificador', 'Solicitante', 'Fecha de creación', 'Comentario', 'Monto solicitado (Bs)',
						 'Monto aprobado (Bs)', 'Reunión', 'Estatus'
					 );
					array_unshift($result['report'], $dataHeader);
					array_unshift($result['report'], array(""));
					array_unshift($result['report'], array(
						"Solicitudes realizadas desde: " . $from->format('d/m/Y - h:i:sa') . " hasta: " . $to->format('d/m/Y - h:i:sa')
					));
					$now = (new DateTime('now', new DateTimeZone('America/Barbados')))->format('d/m/Y - h:i:sa');
					array_unshift($result['report'], array(
						"Fecha y hora de generación de reporte: " . $now
					));
					array_push($result['report'], array(""));
					array_push($result['report'], array(
						"Monto solicitado total: Bs " . number_format($totalRequested, 2))
					);
					array_push($result['report'], array(
						"Monto aprobado total: Bs " . number_format($totalApproved, 2))
					);
					array_push($result['report'], array(""));
					array_push($result['report'],
						array("Solicitudes con estatus Recibida: " . $received . " (" . $result['pie']['data'][0] . "%)"));
					array_push($result['report'],
						array("Solicitudes con estatus Aprobada: " . $approved . " (" . $result['pie']['data'][1] . "%)"));
					array_push($result['report'],
						array("Solicitudes con estatus Rechazada: " . $rejected . " (" . $result['pie']['data'][2] . "%)"));
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
