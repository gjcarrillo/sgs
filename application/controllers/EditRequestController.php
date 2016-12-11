<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class EditRequestController extends CI_Controller {
	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
		if ($_SESSION['type'] != 1) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('editRequest');
		}
	}

	public function editionDialog() {
		if ($_SESSION['type'] != 1) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('editDocDescription');
		}
	}

	public function emailEditionDialog() {
		\ChromePhp::log($_SESSION['type']);
		if ($_SESSION['type'] != 3) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('editEmail');
		}
	}

	public function updateRequest() {
		if ($_SESSION['type'] != 1) {
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
				// 2 = Addition (in case edition was only documents addition)
				$history->setTitle(2);
				$history->setOrigin($request);
				$request->addHistory($history);
				// Register it's corresponding actions
				if (isset($data['comment']) && $request->getComment() !== $data['comment']) {
					// 3 = Modification
					$history->setTitle(3);
					$action = new \Entity\HistoryAction();
					$action->setSummary("Comentario acerca de la solicitud.");
					$action->setDetail("Comentario realizado: " . $data['comment']);
					$action->setBelongingHistory($history);
					$history->addAction($action);
					$em->persist($action);
				}
				$em->persist($history);
				$request->setStatusByText($data['status']);
				if (isset($data['comment'])) {
					$request->setComment($data['comment']);
				}
				$em->merge($request);
				$em->flush();
				$this->addDocuments($request, $history->getId(), $data['newDocs']);
				$em->clear();
				$result['message'] = "success";
			} catch (Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = "error";
			}

			echo json_encode($result);
		}
	}

	// Helper function that adds a set of docs to a request in database.
	private function addDocuments($request, $historyId, $docs) {
		try {
			$em = $this->doctrine->em;
			foreach ($docs as $data) {
				$doc = $em->getRepository('\Entity\Document')->findOneBy(array(
					"lpath" => $data['lpath']
				));
				if ($doc !== null) {
					// doc already exists, so just merge. Otherwise we'll have
					// 'duplicates' in database, because document name is not unique
					if (isset($data['description'])) {
						$doc->setDescription($data['description']);
						$em->merge($doc);
					}
				} else {
					// New document
					$doc = new \Entity\Document();
					$doc->setName($data['docName']);
					if (isset($data['description'])) {
						$doc->setDescription($data['description']);
					}
					$doc->setLpath($data['lpath']);
					$doc->setBelongingRequest($request);
					$request->addDocument($doc);

					$em->persist($doc);
					$em->merge($request);
				}
				// Set History action for this request's corresponding history
				$history =  $em->find('\Entity\History', $historyId);
				$action = new \Entity\HistoryAction();
				$action->setSummary("Adición del documento '" . $data['docName'] . "'.");
				if (isset($data['description']) && $data['description'] !== "") {
					$action->setDetail("Descripción: " . $data['description']);
				}
				$action->setBelongingHistory($history);
				$history->addAction($action);
				$em->persist($action);
				$em->merge($history);
			}
			$em->flush();
		} catch (Exception $e) {
			\ChromePhp::log($e);
			$result['message'] = "error";
		}
	}

	public function updateDocDescription() {
		if ($_SESSION['type'] != 1) {
			$this->load->view('errors/index.html');
		} else {
			$data = json_decode(file_get_contents('php://input'), true);
			try {
				$em = $this->doctrine->em;
				// Update document's description
				$document = $em->find('\Entity\Document', $data['id']);
				// Register History
				if ($document->getDescription() != $data['description']) {
					$history = new \Entity\History();
					$history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
					$history->setUserResponsable($_SESSION['name'] . ' ' . $_SESSION['lastName']);
					// 3 = Modification
					$history->setTitle(3);
					$request = $document->getBelongingRequest();
					$history->setOrigin($request);
					$request->addHistory($history);
					$em->merge($request);
					// Register it's corresponding action
					$action = new \Entity\HistoryAction();
					$action->setSummary("Descripción del documento '" . $document->getName() . "' modificada.");
					$action->setDetail("Nueva descripción: " . $data['description']);
					$action->setBelongingHistory($history);
					$history->addAction($action);
					$em->persist($action);
					$em->persist($history);
					// Update description
					$document->setDescription($data['description']);
					$em->merge($document);
					$em->flush();
				}
				$result['message'] = "success";
			} catch (Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = "error";
			}

			echo json_encode($result);
		}
	}

	public function updateEmail() {
		if ($_SESSION['type'] != 3) {
			$this->load->view('errors/index.html');
		} else {
			$data = json_decode(file_get_contents('php://input'), true);
			try {
				$em = $this->doctrine->em;
				// Update request
				$request = $em->find('\Entity\Request', $data['reqId']);
				$request->setContactEmail($data['newAddress']);
				// Register History
				$history = new \Entity\History();
				$history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
				$history->setUserResponsable($_SESSION['name'] . ' ' . $_SESSION['lastName']);
				// Register it's corresponding actions
				// 3 = Modification
				$history->setTitle(3);
				$history->setOrigin($request);
				$action = new \Entity\HistoryAction();
				$action->setSummary("Cambio de correo electrónico.");
				$action->setDetail("Nuevo correo electrónico: " . $data['newAddress']);
				$action->setBelongingHistory($history);
				$history->addAction($action);
				$em->persist($action);
				$em->persist($history);
				$em->merge($request);
				$em->flush();
				$em->clear();
				$this->updateRequestDocument($request, $request->getUserOwner());
				$result['message'] = "success";
			} catch (Exception $e) {
				\ChromePhp::log($e);
				$result['message'] = "error";
			}

			echo json_encode($result);
		}
	}

	private function updateRequestDocument ($request, $user) {
		$data['reqAmount'] = $request->getRequestedAmount();
		$data['tel'] = $request->getContactNumber();
		$data['email'] = $request->getContactEmail();
		$data['due'] = $request->getPaymentDue();
		$data['userId'] = $user->getId();
		$data['loanType'] = $request->getLoanType();
		$data['lpath'] = $request->getDocuments()[0]->getLpath();
		$this->generateRequestDocument($data, $user, $request);
	}

	private function generateRequestDocument($data, $user, $request) {
		$pdfFilePath = DropPath . $data['lpath'];
		// Get extra data for the pdf template.
		$data['username'] = $user->getFirstName() . ' ' . $user->getLastName();
		$data['requestId'] = str_pad($request->getId(), 6, '0', STR_PAD_LEFT);
		$data['date'] = new DateTime('now', new DateTimeZone('America/Barbados'));
		$data['loanTypeString'] = $this->mapLoanType($data['loanType']);
		// Generate the document.
		\ChromePhp::log("Genrating pdf...");
		$html = $this->load->view('templates/requestPdf', $data, true); // render the view into HTML
		$this->load->library('pdf');
		$pdf = $this->pdf->load();
		$pdf->WriteHTML($html); // write the HTML into the PDF
		// Set footer
		$pdf->SetHTMLFooter (
			'<p style="font-size: 14px">
			* Cuotas y plazo de pago sujetos a cambios en base a solicitudes posteriores
			del afiliado en cuestión.
		</p>');
		$pdf->Output($pdfFilePath, 'F'); // save to file
		\ChromePhp::log("PDF generation success!");
	}

	private function mapLoanType($code) {
		return $code == 40 ? "PRÉSTAMO PERSONAL" : ($code == 31 ? "VALE DE CAJA" : $code);
	}
}
