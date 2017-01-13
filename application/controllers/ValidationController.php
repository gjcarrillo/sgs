<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class ValidationController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

    public function index() {
        $this->load->view('validation');
    }

    public function validate() {
        try {
            $urlDecoded = JWT::urlsafeB64Decode($_GET['token']);
            $decoded = JWT::decode($urlDecoded, SECRET_KEY);

            $uid = $decoded->uid;
            $rid = $decoded->rid;
            $em = $this->doctrine->em;
            $request = $em->find('\Entity\Request', $rid);
            if ($request->getUserOwner()->getId() !== $uid) {
                $result['message'] = "Token Inválido.";
            } else {
                if (!isset($_SESSION['id']) || $_SESSION['id'] !== $uid) {
                    $result['message'] = "Esta solicitud no le pertenece.";
                } else {
                    if ($request->getValidationDate() !== null) {
                        $result['message'] = "Esta solicitud ya ha sido validada.";
                    } else {
                        $request->setValidationDate(new DateTime('now', new DateTimeZone('America/Barbados')));
                        $this->registerValidation($em, $request);
                        $em->merge($request);
                        $em->flush();
                        $em->clear();
                        $result['message'] = "success";
                    }
                }
            }
        } catch (Exception $e) {
            \ChromePhp::log($e);
            $result['message'] = "Token Inválido.";
        }
        echo json_encode($result);
    }

    private function registerValidation($em, $request) {
        // Register History
        $history = new \Entity\History();
        $history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
        $history->setUserResponsable($_SESSION['name'] . ' ' . $_SESSION['lastName']);
        // Register it's corresponding actions
        // 7 = Validation
        $history->setTitle(7);
        $history->setOrigin($request);
        $action = new \Entity\HistoryAction();
        $action->setSummary("Validación de solicitud");
        $action->setDetail("Solicitud validada a través del correo electrónico " . $request->getContactEmail());
        $action->setBelongingHistory($history);
        $history->addAction($action);
        $em->persist($action);
        $em->persist($history);
    }
}
