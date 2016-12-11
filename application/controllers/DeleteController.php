<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class DeleteController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

    public function index() {
        $this->load->view('deleteRequest');
    }

    public function deleteRequest() {
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $em = $this->doctrine->em;
            $urlDecoded = JWT::urlsafeB64Decode($data['rid']);
            $decoded = JWT::decode($urlDecoded, SECRET_KEY);

            $request = $em->find('\Entity\Request', $decoded->rid);
            if ($request == null) {
                $result['message'] = 'No existe dicha solicitud.';
            } else if ($request->getUserOwner()->getId() != $_SESSION['id']) {
                $result['message'] = 'Esta solicitud no le pertenece.';
            } else if ($request->getValidationDate() !== null) {
                $result['message'] = 'Esta solicitud no puede ser eliminada.';
            } else {
                // Must delete all documents belonging to this request first
                $docs = $request->getDocuments();
                foreach($docs as $doc) {
                    unlink(DropPath . $doc->getLpath());
                }
                // Now we can remove the current request (and docs on cascade)
                $em->remove($request);
                // Persist the changes in database.
                $em->flush();
                $result['message'] = "success";
            }
        } catch (Exception $e) {
            $result['message'] = "Token inválido.";
            \ChromePhp::log($e);
        }
        echo json_encode($result);
    }
}
