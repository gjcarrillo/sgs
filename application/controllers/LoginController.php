<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class LoginController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

    public function index() {
        $this->load->view('login');
    }

    public function authenticate() {
        $result = null;
       try {
           $em = $this->doctrine->em;
           $user = $em->find('\Entity\User', $_GET['id']);
           if($user != null) {
               if($user->getPassword() == $_GET['password']) {
                   if ($user->getStatus() === "ACTIVE") {
                       $result['type'] = $user->getType();
                       $result['name'] = $user->getFirstName();
                       $result['lastName'] = $user->getLastName();

                       $dataSession = array(
                           "id" => $_GET['id'],
                           "name" => $user->getFirstName(),
                           "lastName" =>  $user->getLastName(),
                           "type" => $user->getType(),
                           "logged" => true,
                       );
                       $this->session->set_userdata($dataSession);

                       $result['message'] ="success";
                   } else {
                       $result['message'] = "Usuario INACTIVO. Por favor contacte a un administrador.";
                   }
               }
               else {
                   $result['message'] = "Contraseña incorrecta";
               }
           }
           else {
               $result['message'] = "La cédula ingresada no se encuentra registrada";
           }

       }catch(Exception $e){
           $result['message'] = "Error";
       }

       echo json_encode($result);
    }

    public function logout() {
        $this->session->sess_destroy();
    }

    public function updateSession () {
        $data = json_decode(file_get_contents('php://input'), true);
        $dataSession = array(
            "id" => $_SESSION['id'],
            "name" => $_SESSION['name'],
            "lastName" =>  $_SESSION['lastName'],
            "type" => $data['newType'],
            "logged" => true,
        );
        if ($_SESSION['type'] == 1 && $data['newType'] == 3) {
            $this->session->set_userdata($dataSession);
        } else if ($_SESSION['type'] == 2 && $data['newType'] == 3) {
            $this->session->set_userdata($dataSession);
        } else {
            $this->load->view('errors/index.html');
        }
    }
}
