<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class LoginController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

    public function index() {
        $this->load->view('templates/login');
    }

    public function authenticate() {
        $data = json_decode($this->input->raw_input_stream, true);
        $result['message'] = 'error';
       try {
           $em = $this->doctrine->em;
           $user = $em->find('\Entity\User', $data['id']);
           if($user != null) {
               // User in our database. See if passwords match & allow access.
               $result = $this->authenticateUser($user, $data);
           } else {
               // Look for user in ipapedi database.
               $oldUser = $this->users->findIpapediUser($data['id']);
               if ($oldUser == null) {
                   $result['message'] = "La cédula ingresada no se encuentra registrada";
               } else {
                   $result = $this->authenticateIpapediUser($oldUser, $data);
               }
           }

       }catch(Exception $e){
           $result['message'] = $this->utils->getErrorMsg($e);
       }
       echo json_encode($result);
    }

    private function authenticateUser($user, $data) {
        try {
            if($user->getPassword() == base64_encode($data['password'])) {
                if (trim($user->getStatus()) === "ACTIVO" || trim($user->getStatus()) === "activo") {
                    $result['id'] = $user->getId();
                    $result['type'] = $user->getType();
                    $result['name'] = $user->getFirstName();
                    $result['lastName'] = $user->getLastName();

                    $dataSession = array(
                        "id" => $data['id'],
                        "name" => $user->getFirstName(),
                        "lastName" =>  $user->getLastName(),
                        "type" => $user->getType(),
                        "logged" => true,
                    );
                    $this->session->set_userdata($dataSession);
                    $result['message'] = "success";
                } else {
                    $result['message'] = "Usuario INACTIVO. Por favor contacte a un administrador.";
                }
            } else {
                $result['message'] = "Contraseña incorrecta";
            }
            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function authenticateIpapediUser($oldUser, $data) {
        try {
            if ($oldUser->contrasena != base64_encode($data['password'])) {
                $result['message'] = "Contraseña incorrecta";
            } else {
                if (trim($oldUser->estado) == "ACTIVO" || trim($oldUser->estado) == "activo") {
                    $nameParts = explode(" ", $oldUser->nombre);
                    $data['firstName'] = $nameParts[0];
                    array_shift($nameParts);
                    $data['lastName'] = implode(" ", $nameParts);
                    $data['status'] = trim($oldUser->estado);
                    $data['type'] = APPLICANT;
                    $data['phone'] = $oldUser->telefono;
                    $data['email'] = $oldUser->correo;
                    $this->users->createUser($data);

                    $result['id'] = $data['id'];
                    $result['type'] = APPLICANT;
                    $result['name'] = $data['firstName'];
                    $result['lastName'] = $data['lastName'];
                    $dataSession = array(
                        "id" => $data['id'],
                        "name" => $data['firstName'],
                        "lastName" =>  $data['lastName'],
                        "type" => APPLICANT,
                        "logged" => true,
                    );
                    $this->session->set_userdata($dataSession);

                    $result['message'] = "success";
                } else {
                    $result['message'] = "Usuario INACTIVO. Por favor contacte a un administrador.";
                }
            }
            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function authenticateIpapediAdmin($oldUser, $data) {
        try {
            if ($oldUser->contrasena != base64_encode($data['password'])) {
                $result['message'] = "Contraseña incorrecta";
            } else {
                if (trim($oldUser->estado) == "ACTIVO" || trim($oldUser->estado) == "activo") {
                    $data['firstName'] = $oldUser->nombre;
                    $data['lastName'] = "";
                    $data['status'] = trim($oldUser->estado);
                    $data['type'] = MANAGER;
                    $data['phone'] = null;
                    $data['email'] = null;
                    $this->users->createUser($data);

                    $result['id'] = $data['id'];
                    $result['type'] = MANAGER;
                    $result['name'] = $data['firstName'];
                    $result['lastName'] = $data['lastName'];
                    $dataSession = array(
                        "id" => $data['id'],
                        "name" => $data['firstName'],
                        "lastName" =>  $data['lastName'],
                        "type" => MANAGER,
                        "logged" => true,
                    );
                    $this->session->set_userdata($dataSession);

                    $result['message'] = "success";
                } else {
                    $result['message'] = "Usuario INACTIVO. Por favor contacte a un administrador.";
                }
            }
            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function logout() {
        $this->session->sess_destroy();
    }

    public function updateSession () {
        $result['message'] = 'error';
        $data = json_decode(file_get_contents('php://input'), true);
        $dataSession = array(
            "id" => $_SESSION['id'],
            "name" => $_SESSION['name'],
            "lastName" =>  $_SESSION['lastName'],
            "type" => $data['newType'],
            "logged" => true,
        );
        // Applicant must NEVER be allowed to upgrade it's session.
        if ($this->session->type == AGENT && $data['newType'] == APPLICANT) {
            $this->session->set_userdata($dataSession);
        } else if ($this->session->type == MANAGER && $data['newType'] == APPLICANT) {
            $this->session->set_userdata($dataSession);
        } else {
            $result['message'] = 'forbidden';
        }
        $result['message'] = 'success';
        echo json_encode($result);
    }

    public function transition() {
        $this->load->view('templates/transition');
    }

    /**
     * Used for IPAPEDI en linea to SGS transition for user authentication.
     */
    public function verifyUser () {
        $result['message'] = 'error';
        try {
            $data = json_decode($this->input->raw_input_stream, true);
            $urlDecoded = JWT::urlsafeB64Decode($data['token']);
            $decoded = JWT::decode($urlDecoded, JWT_SECRET_KEY);
            $data['id'] = $decoded->uid;
            $data['password'] = $decoded->psw;
            $em = $this->doctrine->em;
            $user = $em->find('\Entity\User', $data['id']);
            if($user != null) {
                // User in our database. See if passwords match & allow access.
                $result = $this->authenticateUser($user, $data);
            } else {
                // Look for user in ipapedi database.
                if ($decoded->type == "applicant") {
                    // applicant
                    $oldUser = $this->users->findIpapediUser($data['id']);
                    if ($oldUser == null) {
                        $result['message'] = "La cédula ingresada no se encuentra registrada";
                    } else {
                        $result = $this->authenticateIpapediUser($oldUser, $data);
                    }
                } else if ($decoded->type == "admin") {
                    // admin
                    $oldUser = $this->users->findIpapediAdmin($data['id']);
                    if ($oldUser == null) {
                        $result['message'] = "El administrador especificado no se encuentra registrado";
                    } else {
                        $result = $this->authenticateIpapediAdmin($oldUser, $data);
                    }
                } else if ($decoded->type == "agent") {
                    // Agent. See if passwords match & allow access.
                    $em = $this->doctrine->em;
                    $user = $em->find('\Entity\User', $data['id']);
                    if($user == null) {
                        $result['message'] = "El usuario " . $user->getId() . " no se encuentra registrado";
                    } else {
                        $result = $this->authenticateUser($user, $data);
                    }
                }
            }

        } catch(Exception $e){
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        echo json_encode($result);
    }
}
