<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class LoginController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

    public function index() {
        $this->load->view('login/login');
    }

    public function authenticate() {

       try {
           $em = $this->doctrine->em;
           $user = $em->find('\Entity\User', $_GET['id']);
           if($user != null) {
               if($user->getPassword() == $_GET['password']) {
                   $result['type'] = $user->getType();
                   $result['name'] = $user->getName();
                   $result['lastName'] = $user->getLastName();

                   $dataSession = array(
                        "id" => $_GET['id'],
                        "name" => $user->getName(),
                        "lastName" =>  $user->getLastName(),
                        "type" => $user->getType(),
                        "logged" => true,
                    );
                    $this->session->set_userdata($dataSession);

                   $result['message'] ="success";
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
}
