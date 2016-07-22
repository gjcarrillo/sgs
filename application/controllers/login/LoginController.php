<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class LoginController extends CI_Controller {

    public function index() {
        $this->load->view('login/login');
    }

    public function authenticate() {

       try {
           $em = $this->doctrine->em;
           $user = $em->getRepository('\Entity\User')->findOneBy(array("id"=>$_GET['id']));
           if($user != null) {
               if($user->getPassword() == $_GET['password']) {
                   $result['message'] ="success";
                   $result['type'] =$user->getType();
                   // TODO: Create session variable used to authenticate user
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
}
