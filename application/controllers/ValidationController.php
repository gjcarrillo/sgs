<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class ValidationController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

    public function index() {
        $this->load->view('templates/validation');
    }

    public function validate() {
        try {
            $urlDecoded = JWT::urlsafeB64Decode($_GET['token']);
            $decoded = JWT::decode($urlDecoded, JWT_SECRET_KEY);
            $uid = $decoded->uid;
            $rid = $decoded->rid;
            $em = $this->doctrine->em;
            // Get the request entity.
            $request = $em->find('\Entity\Request', $rid);
            // Get the configured span between requests.
            $span = $em->getRepository('\Entity\Config')->findOneBy(array('key' => 'SPAN'))->getValue();
            // Get this user's last granting for this type of request.
            $this->load->model('requestsModel', 'requests');
            $monthsLeft = $this->requests->getSpanLeft($request->getUserOwner()->getId(), $request->getLoanType());
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
                                     $this->utils->mapLoanType($request->getLoanType());
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
                $this->load->model('historyModel', 'history');
                $this->history->registerValidation($request->getId());
                $request->setValidationDate(new DateTime('now', new DateTimeZone('America/Barbados')));
                $em->merge($request);
                $em->flush();
                $em->clear();
                $result['message'] = "success";
            }
        } catch (Exception $e) {
            \ChromePhp::log($e);
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        echo json_encode($result);
    }

    /**
     * Obtains a specific user's concurrence level percentage.
     *
     * @param $userId - user's id.
     * @return int - user's concurrence level percentage.
     * @throws Exception
     */
    private function getUserConcurrence($userId) {
        try {
            $this->ipapedi_db = $this->load->database('ipapedi_db', true);
            $this->ipapedi_db->select('*');
            $this->ipapedi_db->from('db_dt_personales');
            $this->ipapedi_db->where('cedula', $userId);
            $query = $this->ipapedi_db->get();
            if (empty($query->result())) {
                // User info not found! Set concurrence to max.
                return 100;
            } else {
                return $query->result()[0]->concurrencia;
            }
        } catch (Exception $e) {
            throw $e;
        }

    }
}
