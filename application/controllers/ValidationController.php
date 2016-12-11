<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class ValidationController extends CI_Controller {

    public function __construct() {
        parent::__construct();
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
            if ($request->getUserOwner()->getId() === $uid) {
                $request->setValidationDate(new DateTime('now', new DateTimeZone('America/Barbados')));
                $em->merge($request);
                $em->flush();
                $em->clear();
                $result['message'] = "success";
            } else {
                $result['message'] = "Token Inválido.";
            }
        } catch (Exception $e) {
            \ChromePhp::log($e);
            $result['message'] = "Token Inválido.";
        }
        echo json_encode($result);
    }
}
