<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class AgentHomeController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

	public function index() {
        $this->load->view('templates/agentHome');
    }

    public function validateUser() {
        $result['message'] = "error";
        try {
            if ($this->session->type != AGENT) {
                $result['message'] = 'forbidden';
            } else {
                $uid = $this->input->get('uid');
                $this->load->model('userModel');
                $user = $this->userModel->getUser($uid);
                if ($user !== null) {
                    // User exists.
                    $result['message'] = "success";
                } else {
                    $oldUser = $this->userModel->findIpapediUser($uid);
                    if ($oldUser === null) {
                        // User does not exist. Throw error.
                        throw new Exception('El usuario ' . $uid . ' no se encuentra registrado.');
                    } else {
                        // User exists in IPAPEDI DB. Register in our DB.
                        if (trim($oldUser->estado) == "ACTIVO" || trim($oldUser->estado) == "activo") {
                            $nameParts = explode(" ", $oldUser->nombre);
                            $data['firstName'] = $nameParts[0];
                            array_shift($nameParts);
                            $data['lastName'] = implode(" ", $nameParts);
                            $data['id'] = $uid;
                            $data['password'] = base64_decode($oldUser->contrasena);
                            $data['status'] = trim($oldUser->estado);
                            $data['type'] = APPLICANT;
                            $data['phone'] = $oldUser->telefono;
                            $data['email'] = $oldUser->correo;
                            $this->userModel->createUser($data);
                            $result['message'] = "success";
                        } else {
                            // Inactive user. Throw error.
                            $result['message'] = "Usuario INACTIVO. Por favor contacte a un administrador.";
                        }
                        // Create in our DB
                        $result['message'] = "success";
                    }
                }
            }
        } catch (Exception $e) {
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        echo json_encode($result);
    }
}
