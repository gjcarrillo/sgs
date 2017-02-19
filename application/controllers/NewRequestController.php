<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');
use Ramsey\Uuid\Uuid;

class NewRequestController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
		if ($this->session->type == MANAGER) {
			$this->load->view('errors/index.html');
		} else {
			// Managers can't create requests
			$this->load->view('templates/newRequest');
		}
	}

    public function upload() {
		if ($_SESSION['type'] != AGENT) {
			// Only agents can upload documents.
			$this->load->view('errors/index.html');
		} else {
			// Generate a version 4 (random) UUID object
			$uuid4 = Uuid::uuid4();
			$code = $uuid4->toString(); // i.e. 25769c6c-d34d-4bfe-ba98-e0ee856f3e7a
			$uploadfile = DropPath . $_POST['userId'] . '.' .
				$code . '.' . basename($_FILES['file']['name']);
	        move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);

	        $result['lpath'] = $_POST['userId'] . '.' . $code .
				'.' . basename($_FILES['file']['name']);

	        echo json_encode($result);
		}
    }

	/**
	 * Gets a specific user's concurrence percentage.
	 */
	public function getUserConcurrence() {
		$result['message'] = "error";
		try {
			if ($_GET['userId'] != $_SESSION['id'] && $_SESSION['type'] != AGENT) {
				$this->load->view('errors/index.html');
			} else {
				$this->ipapedi_db = $this->load->database('ipapedi_db', true);
				$this->ipapedi_db->select('*');
				$this->ipapedi_db->from('db_dt_personales');
				$this->ipapedi_db->where('cedula', $_GET['userId']);
				$query = $this->ipapedi_db->get();
				if (empty($query->result())) {
					// User info not found! Set concurrence to max.
					$result['concurrence'] = 100;
				} else {
					$result['concurrence'] = $query->result()[0]->concurrencia;
					$result['message'] = "success";
				}
			}
		} catch (Exception $e) {
			$result['message'] = $this->utils->getErrorMsg($e);
		}

		echo json_encode($result);
	}

	/**
	 * Gets a specific user's last requests granting, which indicates whether user can
	 * request the same type of request or not.
	 */
	public function getLastRequestsGranting() {
		$result['message'] = "error";
		if ($_GET['userId'] != $_SESSION['id'] && $_SESSION['type'] != AGENT) {
			$this->load->view('errors/index.html');
		} else {
			try {
				$em = $this->doctrine->em;
				$span = $em->getRepository('\Entity\Config')->findOneBy(array("key" => 'SPAN'))->getValue();
				$result['granting']['span'] = $span;
				$loanTypes = LOAN_TYPES;
				foreach ($loanTypes as $type) {
					$this->ipapedi_db = $this->load->database('ipapedi_db', true);
					$this->ipapedi_db->select('*');
					$this->ipapedi_db->from('db_dt_prestamos');
					$this->ipapedi_db->where('cedula', $_GET['userId']);
					$this->ipapedi_db->where('concepto', $type);
					$query = $this->ipapedi_db->order_by('otorg_fecha',"desc")->get();
					if (empty($query->result())) {
						// Seems like this is their first request. Grant permission to create!
						$result['granting']['allow'][$type] = true;
					} else {
						$granting = date_create_from_format('d/m/Y', $query->result()[0]->otorg_fecha);
						if (!$granting) {
							// No granting date found in granting entry. Perhaps it was rejected?
							// Go ahead and allow this request type creation
							// TODO: CONFIRM IF THIS IS THE ACTION TO TAKE IN THIS CASE
							$result['granting']['allow'][$type] = true;
						} else {
							$currentDate = new DateTime('now', new DateTimeZone('America/Barbados'));
							$interval = $granting->diff($currentDate);
							$monthsPassed = $interval->format("%m");
							$monthsLeft = $span - $monthsPassed;
							$result['granting']['allow'][$type] = $monthsLeft <= 0;
						}
					}
					$result['message'] = 'success';
				}
			} catch (Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = $this->utils->getErrorMsg($e);
			}
		}

		echo json_encode($result);
	}

	/**
	 * Gets a user's availability data (i.e. conditions for creating new requests)
	 */
	public function getAvailabilityData() {
		$result['message'] = "error";
		if ($_GET['userId'] != $_SESSION['id'] && $_SESSION['type'] != AGENT) {
			$this->load->view('errors/index.html');
		} else {
			try {
				$em = $this->doctrine->em;
				$span = $em->getRepository('\Entity\Config')->findOneBy(array("key" => 'SPAN'))->getValue();
				$result['granting']['span'] = $span;
				$result['granting']['allDenied'] = true;
				$loanTypes = LOAN_TYPES;
				foreach ($loanTypes as $type) {
					$this->ipapedi_db = $this->load->database('ipapedi_db', true);
					$this->ipapedi_db->select('*');
					$this->ipapedi_db->from('db_dt_prestamos');
					$this->ipapedi_db->where('cedula', $_GET['userId']);
					$this->ipapedi_db->where('concepto', $type);
					// get last granting date for corresponding request type.
					$query = $this->ipapedi_db->order_by('otorg_fecha',"desc")->get();
					if (empty($query->result())) {
						// Seems like this is their first request. Grant permission to create!
						$result['granting']['allow'][$type] = true;
					} else {
						$granting = date_create_from_format('d/m/Y', $query->result()[0]->otorg_fecha);
						\ChromePhp::log($query->result()[0]);
						\ChromePhp::log($granting);
						if (!$granting) {
							// No granting date found in granting entry. Perhaps it was rejected?
							// Go ahead and allow this request type creation
							// TODO: CONFIRM IF THIS IS THE ACTION TO TAKE IN THIS CASE
							$result['granting']['allow'][$type] = true;
						} else {
							$currentDate = new DateTime('now', new DateTimeZone('America/Barbados'));
							$interval = $granting->diff($currentDate);
							$monthsPassed = $interval->format("%m");
							$monthsLeft = $span - $monthsPassed;
							if ($monthsLeft <= 0) {
								$result['granting']['allow'][$type] = $monthsLeft <= 0;
								$result['granting']['allDenied'] = false;
							}
						}
					}
				}
				$this->ipapedi_db = $this->load->database('ipapedi_db', true);
				$this->ipapedi_db->select('*');
				$this->ipapedi_db->from('db_dt_personales');
				$this->ipapedi_db->where('cedula', $_GET['userId']);
				$query = $this->ipapedi_db->get();
				if (empty($query->result())) {
					// User info not found! Set concurrence to max.
					$result['concurrence'] = 100;
				} else {
					$result['concurrence'] = $query->result()[0]->concurrencia;
				}
				$result['message'] = 'success';
			} catch (Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = $this->utils->getErrorMsg($e);
			}
		}
		echo json_encode($result);
	}

    public function createRequest() {
		$data = json_decode(file_get_contents('php://input'), true);
		if ($data['userId'] != $_SESSION['id'] && $_SESSION['type'] != AGENT) {
			// Only agents can create requests for other people
			$this->load->view('errors/index.html');
		} else {
			// Validate incoming data.
			try {
				$em = $this->doctrine->em;
				$this->load->model('requestsModel', 'requests');
				$this->load->model('configModel');
				$maxAmount = $this->configModel->getMaxReqAmount();
				$minAmount = $this->configModel->getMinReqAmount();
				$terms = REQUESTS_TERMS;
				$loanTypes = LOAN_TYPES;
				if (!$this->utils->checkPreviousRequests($data['userId'], $data['loanType'])) {
					// Another request of same type is already open.
					$result['message'] = 'Usted ya posee una solicitud del tipo ' .
										 $this->utils->mapLoanType($data['loanType']) . ' en transcurso.';
				} else if ($this->requests->getSpanLeft($data['userId'], $data['loanType']) > 0) {
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
				} else if (!in_array($data['loanType'], $loanTypes)) {
					$result['message'] = 'Tipo de préstamo inválido.';
				} else {
					// Register History first
					$request = new \Entity\Request();
					$history = new \Entity\History();
					$history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
					$history->setUserResponsable($_SESSION['name'] . ' ' . $_SESSION['lastName']);
					$history->setTitle($this->utils->getHistoryActionCode('creation'));
					$history->setOrigin($request);
					$request->addHistory($history);
					// Register it's corresponding actions
					$action = new \Entity\HistoryAction();
					$action->setSummary("Estatus de la solicitud: " . RECEIVED . ".");
					$action->setBelongingHistory($history);
					$history->addAction($action);
					$em->persist($action);
					$action = new \Entity\HistoryAction();
					$action->setSummary("Monto solicitado: Bs " . number_format($data['reqAmount'], 2));
					$action->setBelongingHistory($history);
					$history->addAction($action);
					$em->persist($action);
					$action = new \Entity\HistoryAction();
					$action->setSummary("Número de contacto: " . $data['tel']);
					$action->setBelongingHistory($history);
					$history->addAction($action);
					$em->persist($action);
					$action = new \Entity\HistoryAction();
					$action->setSummary("Dirección de correo: " . $data['email']);
					$action->setBelongingHistory($history);
					$history->addAction($action);
					$em->persist($action);
					$action = new \Entity\HistoryAction();
					$action->setSummary("Plazo para pagar: " . $data['due'] . " meses.");
					$action->setBelongingHistory($history);
					$history->addAction($action);
					$em->persist($action);
					$action = new \Entity\HistoryAction();
					$action->setSummary("Tipo de préstamo: " . $this->utils->mapLoanType($data['loanType']));
					$action->setBelongingHistory($history);
					$em->persist($action);
					$history->addAction($action);
					$em->persist($history);
					$request->setStatus(RECEIVED);
					$request->setCreationDate(new DateTime('now', new DateTimeZone('America/Barbados')));
					$request->setRequestedAmount($data['reqAmount']);
					$request->setLoanType($data['loanType']);
					$request->setPaymentDue($data['due']);
					$request->setContactNumber($data['tel']);
					$request->setContactEmail($data['email']);
					$user = $em->find('\Entity\User', $data['userId']);
					$request->setUserOwner($user);
					$user->addRequest($request);
					$em->persist($request);
					$em->merge($user);
					// Create the new request doc.
					$this->load->model('requestsModel', 'requests');
					$this->requests->addDocuments($request, $history, $data['docs']);
					$em->persist($history);
					$em->flush();
					$this->requests->generateRequestDocument($request);
					// Send request validation token.
					$this->sendValidation($request->getId());
					$result['message'] = "success";
				}
	        } catch (Exception $e) {
	             \ChromePhp::log($e);
				$result['message'] = $this->utils->getErrorMsg($e);
	        }

	        echo json_encode($result);
		}
    }

	private function sendValidation($reqId) {
		try {
			$this->load->model('emailModel', 'email');
			$this->email->sendNewRequestEmail($reqId);
			$this->load->model('historyModel', 'history');
			$this->history->registerValidationSending($reqId);
		} catch (Exception $e) {
			throw $e;
		}
	}
}
