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
            // Get the request entity.
            $request = $em->find('\Entity\Request', $rid);
            // Get the configured span between requests.
            $span = $em->getRepository('\Entity\Config')->findOneBy(array('key' => 'SPAN'))->getValue();
            // Get this user's last granting for this type of request.
            $monthsLeft = $this->getSpanLeft($span, $uid, $request);
            if ($request->getUserOwner()->getId() !== $uid) {
                $result['message'] = "Token Inválido.";
            } else if (!isset($_SESSION['id']) || $_SESSION['id'] !== $uid) {
                $result['message'] = "Esta solicitud no le pertenece.";
            } else if ($request->getValidationDate() !== null) {
                $result['message'] = "Esta solicitud ya ha sido validada.";
            } else if ($this->getUserConcurrence($uid) >= 45) {
                $result['message'] = "Usted ya no posee un nivel de concurrencia apropiado,
                    por lo cual deja de cumplir con las condiciones para validar esta solicitud.";
            } else if ($monthsLeft > 0) {
                $result['message'] = "No ha" . ($span == 1 ? "" : "n") .
                    " transcurrido al menos " . $span . ($span == 1 ? " mes " : " meses ") .
                    "desde su última otorgación de préstamo del tipo: " .
                    $this->mapLoanType($request->getLoanType());
            } else if ($decoded->reqAmount != $request->getRequestedAmount() ||
                       $decoded->tel != $request->getContactNumber() ||
                       $decoded->email != $request->getContactEmail() ||
                       $decoded->due != $request->getPaymentDue() ||
                       $decoded->loanType != $request->getLoanType()) {
                $result['message'] = "La información de su solicitud ha cambiado y esta URL de validación " .
                                     "ha caducado.
                                     Por favor utilice la URL de validación del correo enviado con información
                                     actualizada.
                                     Si no ha recibido dicho correo luego de 10 minutos, puede solicitar reenvío del
                                     mismo a través del sistema.";
            } else {
                $request->setValidationDate(new DateTime('now', new DateTimeZone('America/Barbados')));
                $this->registerValidation($em, $request);
                $em->merge($request);
                $em->flush();
                $em->clear();
                $result['message'] = "success";
            }
        } catch (Exception $e) {
            \ChromePhp::log($e);
            $result['message'] = "Token Inválido.";
        }
        echo json_encode($result);
    }

    private function getSpanLeft ($span, $uid, $request) {
        $this->db->select('*');
        $this->db->from('db_dt_prestamos');
        $this->db->where('cedula', $uid);
        $this->db->where('concepto', $request->getLoanType());
        $query = $this->db->order_by('otorg_fecha',"desc")->get();
        if (empty($query->result())) {
            return $span;
        } else {
            $granting = date_create_from_format('d/m/Y', $query->result()[0]->otorg_fecha);
            $currentDate = new DateTime('now', new DateTimeZone('America/Barbados'));
            $interval = $granting->diff($currentDate);
            $monthsPassed = $interval->format("%m");
            return $span - $monthsPassed;
        }
    }

    /**
     * Obtains a specific user's concurrence level percentage.
     *
     * @param $userId - user's id.
     * @return int - user's concurrence level percentage.
     */
    private function getUserConcurrence($userId) {
        $this->db->select('*');
        $this->db->from('db_dt_personales');
        $this->db->where('cedula', $userId);
        $query = $this->db->get();
        if (empty($query->result())) {
            // User info not found! Set concurrence to max.
            return 100;
        } else {
            return $query->result()[0]->concurrencia;
        }
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

    private function mapLoanType($code) {
        return $code == 40 ? "PRÉSTAMO PERSONAL" : ($code == 31 ? "VALE DE CAJA" : $code);
    }
}
