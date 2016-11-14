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
			$this->load->view('managerHome');
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
							$result['report']['data'][$rKey] = array(
								$rKey+1,
								$request->getId(),
								$request->getCreationDate()->format('d/m/Y'),
								$request->getStatusByText(),
								$request->getReunion(),
								$request->getRequestedAmount(),
								$request->getApprovedAmount(),
								$request->getComment()
							);
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
						$applicant = $user->getId() . ' - ' .$user->getName() . ' ' . $user->getLastName();
						$now = (new DateTime('now', new DateTimeZone('America/Barbados')))->format('d/m/Y - h:i:sa');
						$result['report']['header'] = array(
							array("SGDP - IPAPEDI"),
							array("FECHA Y HORA DE GENERACIÓN DE REPORTE: " . $now)
						);
						$result['report']['dataTitle'] = "SOLICITUDES DEL AFILIADO " . strtoupper($applicant);
						$result['report']['filename'] = $result['report']['dataTitle'];
						$result['report']['dataHeader'] = array(
							'Nro.', 'Identificador', 'Fecha de creación', 'Estatus', 'Nro. de Reunión',
							 'Monto solicitado (Bs)', 'Monto aprobado (Bs)', 'Comentario'
						 );
						$result['report']['total'] = array(
							array("Monto solicitado total", ""),
							array("Monto aprobado total", "")
						);
						$result['report']['stats']['title'] = "ESTADÍSTICAS DE SOLICITUDES DEL AFILIADO";
						$result['report']['stats']['dataHeader'] = array(
							'Estatus', 'Cantidad', 'Porcentaje'
						 );
						$result['report']['stats']['data'][0] = array(
							"Recibida",  "", ""
						);
						$result['report']['stats']['data'][1] = array(
							"Aprobada",  "", ""
						);
						$result['report']['stats']['data'][2] = array(
							"Rechazada",  "", ""
						);
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
						$result['report']['data'][$rKey] = array(
							$rKey+1,
							$request->getId(),
							$request->getCreationDate()->format('d/m/Y'),
						);
						if ($_GET['status'] !== "Recibida") {
							array_push($result['report']['data'][$rKey], $request->getReunion());
						}
						array_push($result['report']['data'][$rKey], $request->getRequestedAmount());
						if ($_GET['status'] === "Aprobada") {
							array_push($result['report']['data'][$rKey], $request->getApprovedAmount());
						}
						array_push($result['report']['data'][$rKey], $request->getComment());

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
						'Nro.', 'Identificador', 'Fecha de creación'
					 );
					 if ($_GET['status'] !== "Recibida") {
						 array_push($dataHeader, 'Nro. de Reunión');
					 }
					 array_push($dataHeader, 'Monto solicitado (Bs)');
					if ($_GET['status'] === "Aprobada") {
						array_push($dataHeader, 'Monto aprobado (Bs)');
					}
					array_push($dataHeader, 'Comentario');

					$now = (new DateTime('now', new DateTimeZone('America/Barbados')))->format('d/m/Y - h:i:sa');
					$result['report']['header'] = array(
						array("SGDP - IPAPEDI"),
						array("FECHA Y HORA DE GENERACIÓN DE REPORTE: " . $now)
					);
					$result['report']['dataTitle'] = "SOLICITUDES EN ESTATUS '" . strtoupper($_GET['status'] . "'");
					$result['report']['dataHeader'] = $dataHeader;
					$result['report']['total'] = array(
						array("Monto solicitado total", "")
					);
					if ($_GET['status'] === "Aprobada") {
						array_push($result['report']['total'], array(
							"Monto aprobado total", "")
						);
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
						$result['report']['data'][$rKey] = array(
							$rKey+1,
							$request->getId(),
							$request->getCreationDate()->format('d/m/Y'),
							$request->getStatusByText(),
							$request->getReunion(),
							$request->getRequestedAmount(),
							$request->getApprovedAmount(),
							$request->getComment()
						);
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
					$now = (new DateTime('now', new DateTimeZone('America/Barbados')))->format('d/m/Y - h:i:sa');
					$result['report']['header'] = array(
						array("SGDP - IPAPEDI"),
						array("FECHA Y HORA DE GENERACIÓN DE REPORTE: " . $now)
					);
					$interval = $days > 0 ? "DEL " . $from->format('d/m/Y') . " HASTA EL " . $to->format('d/m/Y') : "EL " . $to->format('d/m/Y');
					$filename =  $days > 0 ? "DEL " . $from->format('d-m-Y') . " HASTA EL " . $to->format('d-m-Y') : "EL " . $to->format('d-m-Y');
					$result['report']['filename'] = "SOLICITUDES REALIZADAS " . $filename;
					$result['report']['dataTitle'] = "SOLICITUDES REALIZADAS " . $interval;
					$result['report']['dataHeader'] = array(
						'Nro.', 'Identificador', 'Fecha de creación', 'Estatus', 'Nro. de Reunión',
						 'Monto solicitado (Bs)', 'Monto aprobado (Bs)', 'Comentario'
					 );
					$result['report']['total'] = array(
						array("Monto solicitado total", ""),
						array("Monto aprobado total", "")
					);
					$result['report']['stats']['title'] = "ESTADÍSTICAS DE SOLICITUDES DEL AFILIADO";
					$result['report']['stats']['dataHeader'] = array(
						'Estatus', 'Cantidad', 'Porcentaje'
					 );
					$result['report']['stats']['data'][0] = array(
						"Recibida",  "", ""
					);
					$result['report']['stats']['data'][1] = array(
						"Aprobada",  "", ""
					);
					$result['report']['stats']['data'][2] = array(
						"Rechazada",  "", ""
					);
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
                $qb = $em->createQueryBuilder();
				$qb->select(array('h'))
					->from('\Entity\History', 'h')
					->where($qb->expr()->andX(
						$qb->expr()->eq('h.title', '?1'),
						$qb->expr()->between('h.date', '?2', '?3')
					));
                $qb->setParameter(1, 4);
				$qb->setParameter(2, $from);
                $qb->setParameter(3, $to);
                $history = $qb->getQuery()->getResult();
				$result['approvedAmount'] = $count = 0;
				foreach ($history as $h) {
					$request = $h->getOrigin();
					if ($request->getStatusByText() === "Aprobada") {
						if (!isset($evaluated[$request->getId()])) {
							// Perform all approved amount's computation
							$evaluated[$request->getId()] = true;
							$count++;
							if ($request->getApprovedAmount() !== null) {
								$result['approvedAmount'] += $request->getApprovedAmount();
							}
						}
					}
				}
                if (!$count) {
                    $interval = $from->diff($to);
                    $days = $interval->format("%a");
                    if ($days > 0) {
                        $result['error'] = "No se han encontrado solicitudes aprobadas en el rango de fechas especificado";
                    } else {
                        $result['error'] = "No se han encontrado solicitudes aprobadas en la fecha especificada";
                    }
                } else {
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

	public function getApprovedReportByDateInterval() {
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
				$qb = $em->createQueryBuilder();
				$qb->select(array('h'))
					->from('\Entity\History', 'h')
					->where($qb->expr()->andX(
						$qb->expr()->eq('h.title', '?1'),
						$qb->expr()->between('h.date', '?2', '?3')
					));
				$qb->setParameter(1, 4); // 4 = close
				$qb->setParameter(2, $from);
				$qb->setParameter(3, $to);
				$history = $qb->getQuery()->getResult();
				$count = 0;
				$evaluated = [];
				foreach ($history as $h) {
					$request = $h->getOrigin();
					if (!isset($evaluated[$request->getId()])) {
						// Gather up report information
						$evaluated[$request->getId()] = true;
						$count++;
						$result['report']['data'][$count] = array(
							$count,
							$request->getId(),
							$request->getCreationDate()->format('d/m/Y'),
							$request->getStatusByText(),
							$h->getUserResponsable(),
							$request->getReunion(),
							$request->getRequestedAmount(),
							$request->getApprovedAmount(),
							$request->getComment()
						);
						// Add report generation action to history
						$newLog = new \Entity\History();
						$newLog->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
						$newLog->setUserResponsable($_SESSION['name'] . ' ' . $_SESSION['lastName']);
						// 6 = report generation
						$newLog->setTitle(6);
						$newLog->setOrigin($request);
						$request->addHistory($newLog);
						// Register it's corresponding action
						$action = new \Entity\HistoryAction();
						$action->setSummary("Generación de reporte de solcitudes cerradas");
						$action->setDetail("Solicitudes cerradas entre " . $from->format('d/m/Y') . " y " . $to->format('d/m/Y'));
						$action->setBelongingHistory($newLog);
						$newLog->addAction($action);
						$em->persist($action);
						$em->persist($newLog);
						$em->merge($request);
					}
				}
				$em->flush();
				$em->clear();
				if (!$count) {
					$interval = $from->diff($to);
					$days = $interval->format("%a");
					if ($days > 0) {
						$result['error'] = "No se han encontrado solicitudes cerradas en el rango de fechas especificado";
					} else {
						$result['error'] = "No se han encontrado solicitudes cerradas en la fecha especificada";
					}
				} else {
					// Fill up report information
					$now = (new DateTime('now', new DateTimeZone('America/Barbados')))->format('d/m/Y - h:i:sa');
					$user = strtoupper($_SESSION['name'] . " " . $_SESSION['lastName']);
					$result['report']['header'] = array(
						array("SGDP - IPAPEDI"),
						array("REPORTE GENERADO POR: " . $user . ". FECHA Y HORA: " . $now)
					);
					$interval = "DEL " . $from->format('d/m/Y') . " HASTA EL " . $to->format('d/m/Y');
					$filenameInterval = "DEL " . $from->format('d-m-Y') . " HASTA EL " . $to->format('d-m-Y');
					$dataTitle = "SOLICITUDES APROBADAS " . $interval;
					$filename =  "SOLICITUDES APROBADAS " . $filenameInterval;
					$result['report']['filename'] = $filename;
					$result['report']['dataTitle'] = $dataTitle;
					$result['report']['dataHeader'] = array(
						'Nro.', 'Identificador', 'Fecha de creación', 'Estatus', 'Cerrada por',
						 'Nro. de Reunión', 'Monto solicitado (Bs)', 'Monto aprobado (Bs)', 'Comentario'
					 );
					$result['report']['total'] = array(
						array("Monto solicitado total", ""),
						array("Monto aprobado total", "")
					);
					$result['message'] = "success";
				}
			} catch (Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = "error";
			}
			echo json_encode($result);
		}
	}

	public function getApprovedReportByCurrentWeek() {
		if ($_SESSION['type'] != 2) {
			$this->load->view('errors/index.html');
		} else {
			try {
				$em = $this->doctrine->em;
				// start first day of week, end last day of week
				if (date('D') == 'Mon') {
					$start = date('d/m/Y');
				} else {
					$start = date('d/m/Y', strtotime('last monday'));
				}
				if (date('D') == 'Sun') {
					$end = date('d/m/Y');
				} else {
					$end = date('d/m/Y', strtotime('next sunday'));
				}
				// from first second of the day
				$from = date_create_from_format(
					'd/m/Y H:i:s',
					$start . ' ' . '00:00:00',
					new DateTimeZone('America/Barbados')
				);
				// to last second of the day
				$to = date_create_from_format(
					'd/m/Y H:i:s',
					$end . ' ' . '23:59:59',
					new DateTimeZone('America/Barbados')
				);
				$em = $this->doctrine->em;
				$qb = $em->createQueryBuilder();
				$qb->select(array('h'))
					->from('\Entity\History', 'h')
					->where($qb->expr()->andX(
						$qb->expr()->eq('h.title', '?1'),
						$qb->expr()->between('h.date', '?2', '?3')
					));
				$qb->setParameter(1, 4); // 4 = close
				$qb->setParameter(2, $from);
				$qb->setParameter(3, $to);
				$history = $qb->getQuery()->getResult();
				$count = 0;
				$evaluated = [];
				foreach ($history as $h) {
					$request = $h->getOrigin();
					if (!isset($evaluated[$request->getId()])) {
						// Gather up report information
						$evaluated[$request->getId()] = true;
						$count++;
						$result['report']['data'][$count] = array(
							$count,
							$request->getId(),
							$request->getCreationDate()->format('d/m/Y'),
							$request->getStatusByText(),
							$h->getUserResponsable(),
							$request->getReunion(),
							$request->getRequestedAmount(),
							$request->getApprovedAmount(),
							$request->getComment()
						);
						// Add report generation action to history
						$newLog = new \Entity\History();
						$newLog->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
						$newLog->setUserResponsable($_SESSION['name'] . ' ' . $_SESSION['lastName']);
						// 6 = report generation
						$newLog->setTitle(6);
						$newLog->setOrigin($request);
						$request->addHistory($newLog);
						// Register it's corresponding action
						$action = new \Entity\HistoryAction();
						$action->setSummary("Generación de reporte de solcitudes cerradas");
						$action->setDetail("Solicitudes cerradas entre " . $from->format('d/m/Y') . " y " . $to->format('d/m/Y'));
						$action->setBelongingHistory($newLog);
						$newLog->addAction($action);
						$em->persist($action);
						$em->persist($newLog);
						$em->merge($request);
					}
				}
				$em->flush();
				if (!$count) {
					$result['error'] = "No se han detectado cierres de solicitudes esta semana.";
				} else {
					// Fill up report information
					$now = (new DateTime('now', new DateTimeZone('America/Barbados')))->format('d/m/Y - h:i:sa');
					$user = $_SESSION['name'] . " " . $_SESSION['lastName'];
					$result['report']['header'] = array(
						array("SGDP - IPAPEDI"),
						array("REPORTE GENERADO POR: " . $user . ". FECHA Y HORA: " . $now)
					);
					$interval = "DEL " . $from->format('d/m/Y') . " HASTA EL " . $to->format('d/m/Y');
					$filenameInterval = "DEL " . $from->format('d-m-Y') . " HASTA EL " . $to->format('d-m-Y');
					$dataTitle = "SOLICITUDES APROBADAS " . $interval;
					$filename =  "SOLICITUDES APROBADAS " . $filenameInterval;
					$result['report']['filename'] = $filename;
					$result['report']['dataTitle'] = $dataTitle;
					$result['report']['dataHeader'] = array(
						'Nro.', 'Identificador', 'Fecha de creación', 'Estatus', 'Cerrada por',
						 'Nro. de Reunión', 'Monto solicitado (Bs)', 'Monto aprobado (Bs)', 'Comentario'
					 );
					$result['report']['total'] = array(
						array("Monto solicitado total", ""),
						array("Monto aprobado total", "")
					);
					$result['message'] = "success";
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
