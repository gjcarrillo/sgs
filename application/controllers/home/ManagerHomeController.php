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
