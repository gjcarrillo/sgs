<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

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
		if ($_POST['userId'] != $_SESSION['id'] && $_SESSION['type'] > 1) {
			// Only agents can upload documents that aren't their own
			$this->load->view('errors/index.html');
		} else {
			$uploadfile = DropPath . $_POST['userId'] . '.' .
				$_POST['requestNumb'] . '.' . basename($_FILES['file']['name']);
	        move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);

	        $result['lpath'] = $_POST['userId'] . '.' . $_POST['requestNumb'] .
				'.' . basename($_FILES['file']['name']);

	        echo json_encode($result);
		}
    }

    public function createRequest() {
		$data = json_decode(file_get_contents('php://input'), true);
		\ChromePhp::log($data);
		if ($data['userId'] != $_SESSION['id'] && $_SESSION['type'] > 1) {
			// Only agents can create requests for other people
			$this->load->view('errors/index.html');
		} else {
	        try {
	            $em = $this->doctrine->em;
	            // New request
	            $request = new \Entity\Request();
				// Register History first
				$history = new \Entity\History();
				$history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
				$history->setUserResponsable($_SESSION['name'] . ' ' . $_SESSION['lastName']);
				// 1 = Creation
				$history->setTitle(1);
				$history->setOrigin($request);
				$request->addHistory($history);
				// Register it's corresponding actions
				$action = new \Entity\HistoryAction();
				$action->setSummary("Solicitud creada.");
				$action->setDetail("Estado de la solicitud: Recibida.\n" .
					"Monto solicitado: Bs " . number_format($data['reqAmount'], 2));
				$action->setBelongingHistory($history);
				$history->addAction($action);
				$em->persist($action);
				$em->persist($history);
	            // 1 = Waiting
	            $request->setStatus(1);
	            $request->setCreationDate(new DateTime('now', new DateTimeZone('America/Barbados')));
				$request->setRequestedAmount($data['reqAmount']);
				$request->setLoanType($data['loanType']);
				$request->setPaymentDue($data['due']);
				$request->setContactNumber($data['tel']);
	            $user = $em->find('\Entity\User', $data['userId']);
	            $request->setUserOwner($user);
	            $user->addRequest($request);
	            $em->persist($request);
	            $em->merge($user);
	            $em->flush();
				// Create the new request doc.
				$this->generateRequestDocument($data, $user, $request);
				$this->createDocuments($request, $history->getId(), $data['docs']);
	            $result['message'] = "success";
	        } catch (Exception $e) {
	            \ChromePhp::log($e);
	            $result['message'] = "error";
	        }

	        echo json_encode($result);
		}
    }

	// Helper function that creates a set of docs in database.
	private function createDocuments($request, $historyId, $docs) {
		try {
			$em = $this->doctrine->em;
			foreach ($docs as $data) {
				$doc = $em->getRepository('\Entity\Document')->findOneBy(array(
					"lpath" => $data['lpath']
				));
				if ($doc !== null) {
					// doc already exists, so just merge. Otherwise we'll have
					// 'duplicates' in database, because document name is not primary key
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

	// Helper function that generates the new request's pdf document.
	private function generateRequestDocument($data, $user, $request) {
		$pdfFilePath = DropPath . $data['docs'][0]['lpath'];
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

	public function camera() {
		if ($_SESSION['type'] != 1) {
			$this->load->view('errors/index.html');
		} else {
			$this->load->view('camera');
		}
	}

	public function uploadBase64Images() {
		if ($_SESSION['type'] != 1) {
			$this->load->view('errors/index.html');
		} else {
			$data = json_decode(file_get_contents('php://input'), true);
			$imageData = $data['imageData'];
			$imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i',
			 	'', $imageData));

			$filepath = DropPath . $data['userId'] . "." . $data['requestNumb'] .
			 	"." . $data['docName'] . ".png";
			file_put_contents($filepath, $imageData);

			$result['message'] = "success";
			$result['lpath'] = $data['userId'] . "." . $data['requestNumb'] .
			 	"." . $data['docName'] . ".png";
			echo json_encode($result);
		}
	}

	private function mapLoanType($code) {
		return $code == 40 ? "PRÉSTAMO PERSONAL" : ($code == 31 ? "VALE DE CAJA" : $code);
	}
}
