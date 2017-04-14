<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');
use Ramsey\Uuid\Uuid;

class NewRequestController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
		$this->load->model('requestsModel', 'requests');
    }

	public function index() {
		$this->load->view('templates/dialogs/newRequest');
	}

    public function upload() {
		if ($this->session->type == APPLICANT) {
			$result['message'] = 'forbidden';
		} else {
			// Generate a version 4 (random) UUID object
			$uuid4 = Uuid::uuid4();
			$code = $uuid4->toString(); // i.e. 25769c6c-d34d-4bfe-ba98-e0ee856f3e7a
			$uploadfile = DropPath . $_POST['userId'] . '.' .
				$code . '.' . basename($_FILES['file']['name']);
	        move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);

	        $result['lpath'] = $_POST['userId'] . '.' . $code .
				'.' . basename($_FILES['file']['name']);
		}
		echo json_encode($result);
	}

	/**
	 * Gets a user's availability data (i.e. conditions for creating new request of specific concept). This is:
	 */
	public function getAvailabilityData() {
		$result['message'] = "error";
		if ($this->input->get('userId') != $this->session->id && $this->session->type == APPLICANT) {
			$result['message'] = 'forbidden';
		} else {
			try {
				switch (intval($this->input->get('concept'), 10)) {
					case CASH_VOUCHER:
						$result = $this->requests->getCashVoucherAvailabilityData($this->input->get('userId'));
						break;
					case PERSONAL_LOAN:
						$result = $this->requests->getPersonalLoanAvailabilityData($this->input->get('userId'));
						break;
				}
				$em = $this->doctrine->em;
				// Get user's phone and email
				$user = $em->find('Entity\User', $this->input->get('userId'));
				$result['userPhone'] = $user->getPhone();
				$result['userEmail'] = $user->getEmail();
				$result['message'] = 'success';
			} catch (Exception $e) {
				$result['message'] = $this->utils->getErrorMsg($e);
			}
		}
		echo json_encode($result);
	}

    public function createRequest() {
		$data = json_decode(file_get_contents('php://input'), true);
		if ($data['userId'] != $this->session->id && $this->session->type != AGENT) {
			// Only agents can create requests for other people
			$result['message'] = 'forbidden';
		} else {
			// Validate incoming data.
			try {
				$em = $this->doctrine->em;
				$loanTypes = $this->configModel->getLoanTypes();
				$userData = $this->users->getPersonalData($data['userId']);
				$lastLoan = $this->requests->getLastLoanInfo($data['userId'], $data['loanType']);
				$allLoans = $this->requests->getAllLoansInfo($data['userId']);
				$newConcurrence = $this->users->calculateNewConcurrence(
					$allLoans,
					$userData->sueldo,
					$this->utils->calculatePaymentFee($data['reqAmount'],$data['due'],$loanTypes[$data['loanType']]->InteresAnual)
				);
				$diff = $this->utils->getDateInterval(
					new DateTime('now', new DateTimeZone('America/Barbados')),
					date_create_from_format('d/m/Y', $userData->ingreso)
				);
				$terms = $this->utils->extractLoanTerms($loanTypes[$data['loanType']]);
				if ($userData->concurrencia > 40) {
					$result['message'] = "Concurrencia muy alta (mayor a 40%)";
				} else if ($data['loanType'] != CASH_VOUCHER && $newConcurrence > 40) {
					$result['message'] = "Su concurrencia con el nuevo préstamo excede el 40%. Su concurrencia " .
										 "actual le permite una cuota máxima de Bs. " .
										 number_format($this->users->calculateMaxFeeByConcurrence($allLoans, $userData->sueldo), 2);
				} else if ($data['loanType'] == PERSONAL_LOAN && ($diff['months'] + ($diff['years'] * 12) < 6)) {
					$result['message'] = "Deben transcurrir seis meses desde su fecha de ingreso.";
				} else if (!$this->utils->checkPreviousRequests($data['userId'], $data['loanType'])) {
					// Another request of same type is already open.
					$result['message'] = 'Usted ya posee una solicitud del tipo ' .
										 $loanTypes[$data['loanType']]->description . ' en transcurso.';
				} else if ($this->requests->getSpanLeft($data['userId'], $data['loanType']) > 0 &&
						   ($lastLoan != null && $lastLoan->saldo_edo > 0)) {
					// Span between requests of same type not yet through and debts still not paid.
					$span = $em->getRepository('\Entity\Config')->findOneBy(array('key' => 'SPAN' . $data['loanType']))->getValue();
					$result['message'] = "No ha" . ($span == 1 ? "" : "n") .
										 " transcurrido al menos " . $span . ($span == 1 ? " mes " : " meses ") .
										 "desde su última otorgación de préstamo del tipo: " .
										 $loanTypes[$data['loanType']]->DescripcionDelPrestamo;
				} else if (!$this->users->isReqAmountValid($data['reqAmount'], $data['loanType'], $userData)) {
					$result['message'] = 'Monto solicitado no válido.';
				} else if (!in_array($data['due'], $terms)) {
					$result['message'] = 'Plazo de pago no válido.';
				} else if (!$this->utils->isRequestTypeValid($loanTypes, $data['loanType'])) {
					$result['message'] = 'Tipo de préstamo inválido.';
				} else {
					// Register History first
					$request = new \Entity\Request();
					$history = new \Entity\History();
					$history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
					$history->setUserResponsible($this->users->getUser($this->session->id));
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
					$action->setSummary("Tipo de préstamo: " . $loanTypes[$data['loanType']]->DescripcionDelPrestamo);
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
					$this->requests->addDocuments($request, $history, $data['docs'], true);
					$em->persist($history);
					$em->flush();
					$result['request'] = $this->utils->reqToArray($request);
					$this->requests->generateRequestDocument($request);
					$result['message'] = "success";
				}
	        } catch (Exception $e) {
				$result['message'] = $this->utils->getErrorMsg($e);
	        }
		}
		echo json_encode($result);
	}
}
