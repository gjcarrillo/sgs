<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');
use Mailgun\Mailgun;
use Ramsey\Uuid\Uuid;

class NewRequestController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
		// Everyone can create requests
		$this->load->view('newRequest');
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
		if ($_GET['userId'] != $_SESSION['id'] && $_SESSION['type'] != AGENT) {
			$this->load->view('errors/index.html');
		} else {
			$this->db->select('*');
			$this->db->from('db_dt_personales');
			$this->db->where('cedula', $_GET['userId']);
			$query = $this->db->get();
			if (empty($query->result())) {
				// User info not found! Set concurrence to max.
				$result['concurrence'] = 100;
			} else {
				$result['concurrence'] = $query->result()[0]->concurrencia;
				$result['message'] = "success";
			}
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
					$this->db->select('*');
					$this->db->from('db_dt_prestamos');
					$this->db->where('cedula', $_GET['userId']);
					$this->db->where('concepto', $type);
					$query = $this->db->order_by('otorg_fecha',"desc")->get();
					if (empty($query->result())) {
						// Seems like this is their first request. Grant permission to create!
						$result['granting']['allow'][$type] = true;
					} else {
						$granting = date_create_from_format('d/m/Y', $query->result()[0]->otorg_fecha);
						$currentDate = new DateTime('now', new DateTimeZone('America/Barbados'));
						$interval = $granting->diff($currentDate);
						$monthsPassed = $interval->format("%m");
						$monthsLeft = $span - $monthsPassed;
						$result['granting']['allow'][$type] = $monthsLeft <= 0;
					}
					$result['message'] = 'success';
				}
			} catch (Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = 'error';
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
					$this->db->select('*');
					$this->db->from('db_dt_prestamos');
					$this->db->where('cedula', $_GET['userId']);
					$this->db->where('concepto', $type);
					// get last granting date for corresponding request type.
					$query = $this->db->order_by('otorg_fecha',"desc")->get();
					if (empty($query->result())) {
						// Seems like this is their first request. Grant permission to create!
						$result['granting']['allow'][$type] = true;
					} else {
						$granting = date_create_from_format('d/m/Y', $query->result()[0]->otorg_fecha);
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
				$this->db->select('*');
				$this->db->from('db_dt_personales');
				$this->db->where('cedula', $_GET['userId']);
				$query = $this->db->get();
				if (empty($query->result())) {
					// User info not found! Set concurrence to max.
					$result['concurrence'] = 100;
				} else {
					$result['concurrence'] = $query->result()[0]->concurrencia;
				}
				$result['message'] = 'success';
			} catch (Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = 'error';
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
	        try {
				if (!$this->utils->checkPreviousRequests($data['userId'], $data['loanType'])) {
					$result['message'] = 'Usted ya posee una solicitud del tipo ' .
										 $this->utils->mapLoanType($data['loanType']) . ' en transcurso.';
				} else {
					$em = $this->doctrine->em;
					// New request
					$request = new \Entity\Request();
					// Register History first
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
					$em->flush();
					// Create the new request doc.
					$this->load->model('requestsModel', 'requests');
					$this->requests->addDocuments($request, $history->getId(), $data['docs']);
					$this->requests->generateRequestDocument($request);
					// Send request validation token.
					$this->sendValidation($request->getId());
					$result['message'] = "success";
				}
	        } catch (Exception $e) {
	             \ChromePhp::log($e);
	            $result['message'] = "Ha ocurrido un error al crear su solicitud. " .
									 $e->getCode() . ": " . $e->getMessage();
	        }

	        echo json_encode($result);
		}
    }

	//public function uploadBase64Images() {
	//	if ($_SESSION['type'] != APPLICANT) {
	//		$this->load->view('errors/index.html');
	//	} else {
	//		$data = json_decode(file_get_contents('php://input'), true);
	//		$imageData = $data['imageData'];
	//		$imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i',
	//		 	'', $imageData));
	//		// Generate a version 4 (random) UUID object
	//		$uuid4 = Uuid::uuid4();
	//		$code = $uuid4->toString(); // i.e. 25769c6c-d34d-4bfe-ba98-e0ee856f3e7a
	//		$filepath = DropPath . $data['userId'] . "." . $code .
	//		 	"." . $data['docName'] . ".png";
	//		file_put_contents($filepath, $imageData);
    //
	//		$result['message'] = "success";
	//		$result['lpath'] = $data['userId'] . "." . $code .
	//		 	"." . $data['docName'] . ".png";
	//		echo json_encode($result);
	//	}
	//}

	private function sendValidation($reqId) {
		$this->load->model('emailModel', 'email');
		$this->email->sendNewRequestEmail($reqId);
		$this->load->model('historyModel', 'history');
		$this->history->registerValidationSending($reqId);
	}
}
