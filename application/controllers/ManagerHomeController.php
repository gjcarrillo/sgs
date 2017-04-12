<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class ManagerHomeController extends CI_Controller {

    private $loanTypes = null;

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->loanTypes = $this->configModel->getLoanTypes();
    }

	public function index() {
        $this->load->view('templates/managerHome');
	}

    public function getRequestById() {
        if ($this->session->type != MANAGER) {
            $result['message'] = 'forbidden';
        } else {
            try {
                $result['message'] = 'Ha ocurrido un error en el servidor. Por favor intente más tarde.';
                $em = $this->doctrine->em;
                $request = $em->find('\Entity\Request', $_GET['rid']);
                if ($request === null) {
                    $result['message'] = 'No se ha encontrado solicitud con ID ' .
                                         str_pad($_GET['rid'], 6, '0', STR_PAD_LEFT);
                } else if ($request->getValidationDate() === null) {
                    $result['message'] = 'Esta solicitud no ha sido validada';
                } else {
                    $result['request'] = $this->utils->reqToArray($request);
                    $result['message'] = 'success';
                }
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

	// Obtain all valid requests from a user with with all their documents.
	public function getUserRequests() {
        if ($this->session->type != MANAGER) {
            $result['message'] = 'forbidden';
        } else {
			try {
                $result = null;
				$em = $this->doctrine->em;
				$user = $em->find('\Entity\User', $_GET['fetchId']);
				if ($user === null) {
					$result['message'] = "La cédula ingresada no se encuentra en la base de datos";
				} else {
					$requests = $user->getRequests();
					if ($requests->isEmpty()) {
						$result['message'] = "El usuario no posee solicitudes";
					} else {
						$rKey = 0;
                        $statuses = $this->utils->getAllStatuses();
                        $statusCounter = array();
                        foreach ($statuses as $status) {
                            // Initialize counter array
                            $statusCounter[$status] = 0;
                        }
						foreach ($requests as $request) {
							if ($request->getValidationDate() === null) continue;
                            $result['requests'][$rKey] = $this->utils->reqToArray($request);
							// Gather pie chart information
                            $statusCounter[$request->getStatus()] ++;
							// Gather up report information
							$result['report']['data'][$rKey] = array(
								$rKey+1,
								$request->getId(),
								$this->loanTypes[$request->getLoanType()]->DescripcionDelPrestamo,
								$request->getCreationDate()->format('d/m/Y'),
								$request->getStatus(),
								$request->getReunion(),
								$request->getRequestedAmount(),
								$request->getApprovedAmount(),
								$request->getComment()
							);
							$rKey++;
						}
						if ($result['requests'] == null) {
							$result['message'] = 'Este asociado no tiene solicitudes validadas';
						} else {
                            // Fill up pie chart information
                            $result['pie']['title'] = "Estadísticas de solicitudes para el asociado";
                            $total = array_sum($statusCounter);
                            $result['pie']['backgroundColor'] = [];
                            foreach ($statuses as $sKey => $status) {
                                $result['pie']['labels'][$sKey] = $status;
                                $result['pie']['data'][$sKey] = round($statusCounter[$status] * 100 / $total, 2);
                                $result['pie']['backgroundColor'][$sKey] =
                                    $this->utils->generatePieBgColor($status, $result['pie']['backgroundColor']);
                                $result['pie']['hoverBackgroundColor'][$sKey] =
                                    $this->utils->generatePieHoverColor($result['pie']['backgroundColor'][$sKey]);
                            }
                            // Fill up report information
                            $applicant = $user->getId() . ' - ' . $user->getFirstName() . ' ' . $user->getLastName();
                            $now =
                                (new DateTime('now', new DateTimeZone('America/Barbados')))->format('d/m/Y - h:i:sa');
                            $result['report']['header'] = array(
                                array("SGDP - IPAPEDI"),
                                array("FECHA Y HORA DE GENERACIÓN DE REPORTE: " . $now)
                            );
                            $result['report']['dataTitle'] = "SOLICITUDES DEL asociado " . strtoupper($applicant);
                            $result['report']['filename'] = $result['report']['dataTitle'];
                            $result['report']['dataHeader'] = array(
                                'Nro.',
                                'Identificador',
                                'Tipo',
                                'Fecha de creación',
                                'Estatus',
                                'Nro. de Reunión',
                                'Monto solicitado (Bs)',
                                'Monto aprobado (Bs)',
                                'Comentario'
                            );
                            $result['report']['total'] = array(
                                array("Monto solicitado total", ""),
                                array("Monto aprobado total", "")
                            );
                            $result['report']['stats']['title'] = "ESTADÍSTICAS DE SOLICITUDES DEL asociado";
                            $result['report']['stats']['dataHeader'] = array('Estatus', 'Cantidad', 'Porcentaje');
                            foreach ($statuses as $sKey => $status) {
                                $result['report']['stats']['data'][$sKey] = array($status, '', '');
                            }
                            $result['message'] = "success";
                        }
					}
				}
			} catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
		}
        echo json_encode($result);
    }

    public function fetchPendingRequests() {
        if ($this->session->type != MANAGER) {
            $result['message'] = 'forbidden';
        } else {
            $result = null;
            try {
                $em = $this->doctrine->em;
                // Look for all requests with the specified status
                $requestsRepo = $em->getRepository('\Entity\Request');
                $statuses = $this->utils->getAdditionalStatuses();
                array_push($statuses, RECEIVED);
                $requests = $requestsRepo->findBy(array("status" => $statuses));
                if (empty($requests)) {
                    $result['message'] = "No se encontraron solicitudes pendientes.";
                } else {
                    $rKey = 0;
                    $statuses = $this->utils->getAllStatuses();
                    $statusCounter = array();
                    foreach ($statuses as $status) {
                        // Initialize counter array
                        $statusCounter[$status] = 0;
                    }
                    foreach ($requests as $request) {
                        if ($request->getValidationDate() === null) continue;
                        $user = $request->getUserOwner();
                        $result['requests'][$rKey] = $this->utils->reqToArray($request);
                        // Gather pie chart information
                        $statusCounter[$request->getStatus()] ++;
                        // Gather up report information
                        $result['report']['data'][$rKey] = array(
                            $rKey+1,
                            $request->getId(),
                            $user->getId() . ' - ' . $user->getFirstName() . ' ' . $user->getLastName(),
                            $request->getCreationDate()->format('d/m/Y'),
                            $request->getStatus(),
                            $request->getReunion(),
                            $request->getRequestedAmount(),
                            $request->getApprovedAmount(),
                            $request->getComment()
                        );
                        $rKey++;
                    }
                    if ($result['requests'] == null) {
                        $result['message'] = 'No se encontraron solicitudes validadas';
                    } else {
                        // Fill up pie chart information
                        $result['pie']['title'] = "Solicitudes pendientes";
                        $total = array_sum($statusCounter);
                        $result['pie']['backgroundColor'] = [];
                        foreach ($statuses as $sKey => $status) {
                            $result['pie']['labels'][$sKey] = $status;
                            $result['pie']['data'][$sKey] = round($statusCounter[$status] * 100 / $total, 2);
                            $result['pie']['backgroundColor'][$sKey] =
                                $this->utils->generatePieBgColor($status, $result['pie']['backgroundColor']);
                            $result['pie']['hoverBackgroundColor'][$sKey] =
                                $this->utils->generatePieHoverColor($result['pie']['backgroundColor'][$sKey]);
                        }
                        // Fill up report information
                        $now = (new DateTime('now', new DateTimeZone('America/Barbados')))->format('d/m/Y - h:i:sa');
                        $result['report']['header'] = array(
                            array("SGDP - IPAPEDI"),
                            array("FECHA Y HORA DE GENERACIÓN DE REPORTE: " . $now)
                        );
                        $result['report']['filename'] = "SOLICITUDES PENDIENTES";
                        $result['report']['dataTitle'] = $result['report']['filename'];
                        $result['report']['dataHeader'] = array(
                            'Nro.',
                            'Identificador',
                            'Solicitante',
                            'Fecha de creación',
                            'Estatus',
                            'Nro. de Reunión',
                            'Monto solicitado (Bs)',
                            'Monto aprobado (Bs)',
                            'Comentario'
                        );
                        $result['report']['total'] = array(
                            array("Monto solicitado total", ""),
                            array("Monto aprobado total", "")
                        );
                        $result['report']['stats']['title'] = "ESTADÍSTICAS DE SOLICITUDES";
                        $result['report']['stats']['dataHeader'] = array('Estatus', 'Cantidad', 'Porcentaje');
                        foreach ($statuses as $sKey => $status) {
                            $result['report']['stats']['data'][$sKey] = array($status, '', '');
                        }
                        $result['message'] = "success";
                    }
                }
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

    public function loadPendingRequests() {
        if ($this->session->type != MANAGER) {
            $result['message'] = 'forbidden';
        } else {
            $result['requests'] = array();
            try {
                $em = $this->doctrine->em;
                // Look for all requests with the specified status
                $requestsRepo = $em->getRepository('\Entity\Request');
                $statuses = $this->utils->getAdditionalStatuses();
                array_push($statuses, RECEIVED);
                $requests = $requestsRepo->findBy(array("status" => $statuses));
                $rKey = 0;
                foreach ($requests as $request) {
                    if ($request->getValidationDate() === null) continue;
                    $result['requests'][$rKey] = $this->utils->reqToArray($request);
                    $rKey++;
                }
                $result['message'] = 'success';
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

    public function fetchRequestsByStatus() {
        if ($this->session->type != MANAGER) {
            $result['message'] = 'forbidden';
        } else {
            $result = null;
            try {
                $em = $this->doctrine->em;
                // Look for all requests with the specified status
                $status = $_GET['status'];
				$requestsRepo = $em->getRepository('\Entity\Request');
                $requests = $requestsRepo->findBy(array("status" => $status));
                if (empty($requests)) {
                    $result['message'] = "No se encontraron solicitudes con estatus " . $_GET['status'];
                } else {
					$rKey = 0;
                    $statuses = $this->utils->getAllStatuses();
                    $statusCounter = array();
                    foreach ($statuses as $status) {
                        // Initialize counter array
                        $statusCounter[$status] = 0;
                    }
                    foreach ($requests as $request) {
						if ($request->getValidationDate() === null) continue;
						$user = $request->getUserOwner();
                        $result['requests'][$rKey] = $this->utils->reqToArray($request);
                        // Gather up report information
						$result['report']['data'][$rKey] = array(
							$rKey+1,
							$request->getId(),
                            $this->loanTypes[$request->getLoanType()]->DescripcionDelPrestamo,
							$user->getId() . ' - ' . $user->getFirstName() . ' ' . $user->getLastName(),
							$request->getCreationDate()->format('d/m/Y')
						);
						if ($_GET['status'] === APPROVED || $_GET['status'] === REJECTED) {
							array_push($result['report']['data'][$rKey], $request->getReunion());
						}
						array_push($result['report']['data'][$rKey], $request->getRequestedAmount());
						if ($_GET['status'] === APPROVED) {
							array_push($result['report']['data'][$rKey], $request->getApprovedAmount());
						}
						array_push($result['report']['data'][$rKey], $request->getComment());
						$rKey++;
                    }
                    if ($result['requests'] == null) {
                        $result['message'] = 'No se encontraron solicitudes validadas';
                    } else {
                        // Get requests status statistics.
                        foreach ($statuses as $status) {
                            $statusCounter[$status] = count($requestsRepo->findBy(array("status" => $status)));
                        }
                        // Fill up pie chart information
                        $result['pie']['title'] = "Estadísticas de solicitudes del sistema";
                        $total = array_sum($statusCounter);
                        $result['pie']['backgroundColor'] = [];
                        foreach ($statuses as $sKey => $status) {
                            $result['pie']['labels'][$sKey] = $status;
                            $result['pie']['data'][$sKey] = round($statusCounter[$status] * 100 / $total, 2);
                            $result['pie']['backgroundColor'][$sKey] =
                                $this->utils->generatePieBgColor($status, $result['pie']['backgroundColor']);
                            $result['pie']['hoverBackgroundColor'][$sKey] =
                                $this->utils->generatePieHoverColor($result['pie']['backgroundColor'][$sKey]);
                        }
                        // Fill up report information
                        $dataHeader = array(
                            'Nro.',
                            'Identificador',
                            'Tipo',
                            'Solicitante',
                            'Fecha de creación'
                        );
                        if ($_GET['status'] === APPROVED || $_GET['status'] === REJECTED) {
                            array_push($dataHeader, 'Nro. de Reunión');
                        }
                        array_push($dataHeader, 'Monto solicitado (Bs)');
                        if ($_GET['status'] === APPROVED) {
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
                        if ($_GET['status'] === APPROVED) {
                            array_push($result['report']['total'], array(
                                                                     "Monto aprobado total",
                                                                     "")
                            );
                        }
                        $result['message'] = "success";
                    }
				}
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

    public function fetchRequestsByDateInterval() {
        if ($this->session->type != MANAGER) {
            $result['message'] = 'forbidden';
        } else {
            $result = null;
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
                        $result['message'] = "No se han encontrado solicitudes para el rango de fechas especificado";
                    } else {
                        $result['message'] = "No se han encontrado solicitudes para la fecha especificada";
                    }
                } else {
					$rKey = 0;
                    $statuses = $this->utils->getAllStatuses();
                    $statusCounter = array();
                    foreach ($statuses as $status) {
                        // Initialize counter array
                        $statusCounter[$status] = 0;
                    }
                    foreach ($requests as $request) {
						if ($request->getValidationDate() === null) continue;
						$user = $request->getUserOwner();
                        $result['requests'][$rKey] = $this->utils->reqToArray($request);
                        // Gather pie chart information
                        $statusCounter[$request->getStatus()] ++;
						// Gather up report information
						$result['report']['data'][$rKey] = array(
							$rKey+1,
							$request->getId(),
                            $this->loanTypes[$request->getLoanType()]->DescripcionDelPrestamo,
							$user->getId() . ' - ' . $user->getFirstName() . ' ' . $user->getLastName(),
							$request->getCreationDate()->format('d/m/Y'),
							$request->getStatus(),
							$request->getReunion(),
							$request->getRequestedAmount(),
							$request->getApprovedAmount(),
							$request->getComment()
						);
						$rKey++;
                    }
                    if ($result['requests'] == null) {
                        $result['message'] = 'No se encontraron solicitudes validadas';
                    } else {
                        // Fill up pie chart information
                        $result['pie']['title'] = $days > 0 ? (
                        "Estadísticas de solicitudes para el intervalo de fechas especificado") : (
                        "Estadísticas de solicitudes para la fecha especificada"
                        );
                        $total = array_sum($statusCounter);
                        $result['pie']['backgroundColor'] = [];
                        foreach ($statuses as $sKey => $status) {
                            $result['pie']['labels'][$sKey] = $status;
                            $result['pie']['data'][$sKey] = round($statusCounter[$status] * 100 / $total, 2);
                            $result['pie']['backgroundColor'][$sKey] =
                                $this->utils->generatePieBgColor($status, $result['pie']['backgroundColor']);
                            $result['pie']['hoverBackgroundColor'][$sKey] =
                                $this->utils->generatePieHoverColor($result['pie']['backgroundColor'][$sKey]);
                        }
                        // Fill up report information
                        $now = (new DateTime('now', new DateTimeZone('America/Barbados')))->format('d/m/Y - h:i:sa');
                        $result['report']['header'] = array(
                            array("SGDP - IPAPEDI"),
                            array("FECHA Y HORA DE GENERACIÓN DE REPORTE: " . $now)
                        );
                        $interval = $days > 0 ? "DEL " . $from->format('d/m/Y') . " HASTA EL " . $to->format('d/m/Y') :
                            "EL " . $to->format('d/m/Y');
                        $filename = $days > 0 ? "DEL " . $from->format('d-m-Y') . " HASTA EL " . $to->format('d-m-Y') :
                            "EL " . $to->format('d-m-Y');
                        $result['report']['filename'] = "SOLICITUDES REALIZADAS " . $filename;
                        $result['report']['dataTitle'] = "SOLICITUDES REALIZADAS " . $interval;
                        $result['report']['dataHeader'] = array(
                            'Nro.',
                            'Identificador',
                            'Tipo',
                            'Solicitante',
                            'Fecha de creación',
                            'Estatus',
                            'Nro. de Reunión',
                            'Monto solicitado (Bs)',
                            'Monto aprobado (Bs)',
                            'Comentario'
                        );
                        $result['report']['total'] = array(
                            array("Monto solicitado total", ""),
                            array("Monto aprobado total", "")
                        );
                        $result['report']['stats']['title'] = "ESTADÍSTICAS DE SOLICITUDES";
                        $result['report']['stats']['dataHeader'] = array('Estatus', 'Cantidad', 'Porcentaje');
                        foreach ($statuses as $sKey => $status) {
                            $result['report']['stats']['data'][$sKey] = array($status, '', '');
                        }
                        $result['message'] = "success";
                    }
                }
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

	public function fetchRequestsByLoanType() {
        if ($this->session->type != MANAGER) {
            $result['message'] = 'forbidden';
        } else {
            $result = null;
			try {
				$em = $this->doctrine->em;
				// Look for all requests with the specified loan type.
				$loanType = $_GET['loanType'];
				$requestsRepo = $em->getRepository('\Entity\Request');
				$requests = $requestsRepo->findBy(array("loanType" => $loanType));
				if (empty($requests)) {
					$result['message'] = "No se encontraron solicitudes del tipo " .
                                         $this->loanTypes[$loanType]->DescripcionDelPrestamo;
				} else {
					$rKey = 0;
                    $statuses = $this->utils->getAllStatuses();
                    $statusCounter = array();
                    foreach ($statuses as $status) {
                        // Initialize counter array
                        $statusCounter[$status] = 0;
                    }
					foreach ($requests as $request) {
						if ($request->getValidationDate() === null) continue;
						$user = $request->getUserOwner();
                        $result['requests'][$rKey] = $this->utils->reqToArray($request);
                        // Gather pie chart information
                        $statusCounter[$request->getStatus()] ++;
                        // Gather up report information
						$result['report']['data'][$rKey] = array(
							$rKey+1,
							$request->getId(),
							$user->getId() . ' - ' . $user->getFirstName() . ' ' . $user->getLastName(),
							$request->getCreationDate()->format('d/m/Y'),
							$request->getStatus(),
							$request->getReunion(),
							$request->getRequestedAmount(),
							$request->getApprovedAmount(),
							$request->getComment()
						);
						$rKey++;
					}
                    if ($result['requests'] == null) {
                        $result['message'] = 'No se encontraron solicitudes validadas';
                    } else {
                        // Fill up pie chart information
                        $result['pie']['title'] = "Solicitudes de " . $this->loanTypes[$loanType]->DescripcionDelPrestamo;
                        $total = array_sum($statusCounter);
                        $result['pie']['backgroundColor'] = [];
                        foreach ($statuses as $sKey => $status) {
                            $result['pie']['labels'][$sKey] = $status;
                            $result['pie']['data'][$sKey] = round($statusCounter[$status] * 100 / $total, 2);
                            $result['pie']['backgroundColor'][$sKey] =
                                $this->utils->generatePieBgColor($status, $result['pie']['backgroundColor']);
                            $result['pie']['hoverBackgroundColor'][$sKey] =
                                $this->utils->generatePieHoverColor($result['pie']['backgroundColor'][$sKey]);
                        }
                        // Fill up report information
                        $now = (new DateTime('now', new DateTimeZone('America/Barbados')))->format('d/m/Y - h:i:sa');
                        $result['report']['header'] = array(
                            array("SGDP - IPAPEDI"),
                            array("FECHA Y HORA DE GENERACIÓN DE REPORTE: " . $now)
                        );
                        $result['report']['filename'] = "SOLICITUDES DE '" . $this->loanTypes[$loanType]->DescripcionDelPrestamo . "'";
                        $result['report']['dataTitle'] = $result['report']['filename'];
                        $result['report']['dataHeader'] = array(
                            'Nro.',
                            'Identificador',
                            'Solicitante',
                            'Fecha de creación',
                            'Estatus',
                            'Nro. de Reunión',
                            'Monto solicitado (Bs)',
                            'Monto aprobado (Bs)',
                            'Comentario'
                        );
                        $result['report']['total'] = array(
                            array("Monto solicitado total", ""),
                            array("Monto aprobado total", "")
                        );
                        $result['report']['stats']['title'] = "ESTADÍSTICAS DE SOLICITUDES";
                        $result['report']['stats']['dataHeader'] = array('Estatus', 'Cantidad', 'Porcentaje');
                        foreach ($statuses as $sKey => $status) {
                            $result['report']['stats']['data'][$sKey] = array($status, '', '');
                        }
                        $result['message'] = "success";
                    }
				}
			} catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

    public function getApprovedAmountByDateInterval() {
        if ($this->session->type != MANAGER) {
            $result['message'] = 'forbidden';
        } else {
            try {
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
                $qb->setParameter(1, $this->utils->getHistoryActionCode('closure'));
				$qb->setParameter(2, $from);
                $qb->setParameter(3, $to);
                $history = $qb->getQuery()->getResult();
				$result['approvedAmount'] = $count = 0;
				foreach ($history as $h) {
					$request = $h->getOrigin();
					if ($request->getStatus() === APPROVED) {
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
                        $result['message'] = "No se han encontrado solicitudes aprobadas en el rango de fechas especificado";
                    } else {
                        $result['message'] = "No se han encontrado solicitudes aprobadas en la fecha especificada";
                    }
                } else {
					$result['message'] = "success";
				}
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

    public function getApprovedAmountById() {
        if ($this->session->type != MANAGER) {
            $result['message'] = 'forbidden';
        } else {
            try {
                $em = $this->doctrine->em;
                $user = $em->find('\Entity\User', $_GET['userId']);
                if ($user === null) {
                    $result['message'] = "La cédula ingresada no se encuentra en la base de datos";
                } else {
                    $requests = $user->getRequests();
                    if ($requests->isEmpty()) {
                        $result['message'] = "El usuario especificado no posee solicitudes";
                    } else {
                        // Perform all approved amount's computation
                        $result['approvedAmount'] = 0;
                        $result['username'] = $user->getFirstName() . ' ' . $user->getLastName();
                        foreach ($requests as $rKey => $request) {
                            if ($request->getApprovedAmount() !== null) {
                                $result['approvedAmount'] += $request->getApprovedAmount();
                            }
                        }
                        $result['message'] = "success";
                    }
                }
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

	public function getClosedReportByDateInterval() {
        if ($this->session->type != MANAGER) {
            $result['message'] = 'forbidden';
        } else {
			try {
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
				$qb->setParameter(1, $this->utils->getHistoryActionCode('closure'));
				$qb->setParameter(2, $from);
				$qb->setParameter(3, $to);
				$history = $qb->getQuery()->getResult();
				$count = 0;
				$evaluated = [];
				foreach ($history as $h) {
					$request = $h->getOrigin();
					$userOwner = $request->getUserOwner();
					if (!isset($evaluated[$request->getId()])) {
						// Gather up report information
						$evaluated[$request->getId()] = true;
						$count++;
						$result['report']['data'][$count] = array(
							$count,
							$request->getId(),
                            $this->loanTypes[$request->getLoanType()]->DescripcionDelPrestamo,
                            $userOwner->getId() . ' (' . trim($userOwner->getFirstName()) . ' ' . trim($userOwner->getLastName()) . ')',
							$request->getCreationDate()->format('d/m/Y'),
							$request->getStatus(),
							$h->getUserResponsible()->getId(),
							$request->getReunion(),
							$request->getRequestedAmount(),
							$request->getApprovedAmount(),
						);
					}
				}
                if (!$count) {
                    $interval = $from->diff($to);
					$days = $interval->format("%a");
					if ($days > 0) {
						$result['message'] = "No se han encontrado solicitudes cerradas en el rango de fechas especificado";
					} else {
						$result['message'] = "No se han encontrado solicitudes cerradas en la fecha especificada";
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
					$dataTitle = "SOLICITUDES CERRADAS " . $interval;
					$filename =  "SOLICITUDES CERRADAS " . $filenameInterval;
					$result['report']['filename'] = $filename;
					$result['report']['dataTitle'] = $dataTitle;
					$result['report']['dataHeader'] = array(
						'Nro.', 'Identificador', 'Tipo', 'Solicitante', 'Fecha de creación', 'Estatus', 'Cerrada por',
						 'Nro. de Reunión', 'Monto solicitado (Bs)', 'Monto aprobado (Bs)'
					 );
					$result['report']['total'] = array(
						array("Monto solicitado total", ""),
						array("Monto aprobado total", "")
					);
					$result['message'] = "success";
				}
			} catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }

	public function getClosedReportByCurrentWeek() {
        if ($this->session->type != MANAGER) {
            $result['message'] = 'forbidden';
        } else {
            try {
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
                $qb->setParameter(1, $this->utils->getHistoryActionCode('closure'));
                $qb->setParameter(2, $from);
                $qb->setParameter(3, $to);
                $history = $qb->getQuery()->getResult();
                $count = 0;
                $evaluated = [];
                foreach ($history as $h) {
                    $request = $h->getOrigin();
                    $userOwner = $request->getUserOwner();
                    if (!isset($evaluated[$request->getId()])) {
                        // Gather up report information
                        $evaluated[$request->getId()] = true;
                        $count++;
                        $result['report']['data'][$count] = array(
                            $count,
                            $request->getId(),
                            $this->loanTypes[$request->getLoanType()]->DescripcionDelPrestamo,
                            $userOwner->getId() . ' (' . trim($userOwner->getFirstName()) . ' ' . trim($userOwner->getLastName()) . ')',
                            $request->getCreationDate()->format('d/m/Y'),
                            $request->getStatus(),
                            $h->getUserResponsible()->getId(),
                            $request->getReunion(),
                            $request->getRequestedAmount(),
                            $request->getApprovedAmount(),
                        );
                        // Add report generation action to history
                        $newLog = new \Entity\History();
                        $newLog->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
                        $newLog->setUserResponsible($this->users->getUser($this->session->id));
                        $newLog->setTitle($this->utils->getHistoryActionCode('report'));
                        $newLog->setOrigin($request);
                        $request->addHistory($newLog);
                        // Register it's corresponding action
                        $action = new \Entity\HistoryAction();
                        $action->setSummary("Generación de reporte de solcitudes cerradas");
                        $action->setDetail("Solicitudes cerradas entre " . $from->format('d/m/Y') . " y " .
                                           $to->format('d/m/Y'));
                        $action->setBelongingHistory($newLog);
                        $newLog->addAction($action);
                        $em->persist($action);
                        $em->persist($newLog);
                        $em->merge($request);
                    }
                }
                $em->flush();
                if (!$count) {
                    $result['message'] = "No se han detectado cierres de solicitudes esta semana.";
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
                    $dataTitle = "SOLICITUDES CERRADAS " . $interval;
                    $filename = "SOLICITUDES CERRADAS " . $filenameInterval;
                    $result['report']['filename'] = $filename;
                    $result['report']['dataTitle'] = $dataTitle;
                    $result['report']['dataHeader'] = array(
                        'Nro.',
                        'Identificador',
                        'Tipo',
                        'Solicitante',
                        'Fecha de creación',
                        'Estatus',
                        'Cerrada por',
                        'Nro. de Reunión',
                        'Monto solicitado (Bs)',
                        'Monto aprobado (Bs)'
                    );
                    $result['report']['total'] = array(
                        array("Monto solicitado total", ""),
                        array("Monto aprobado total", "")
                    );
                    $result['message'] = "success";
                }
            } catch (Exception $e) {
                $result['message'] = $this->utils->getErrorMsg($e);
            }
        }
        echo json_encode($result);
    }
}
