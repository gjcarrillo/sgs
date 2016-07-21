<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class LoginController extends CI_Controller {

    public function index() {
        $this->load->view('login/login');
    }

    public function authenticate() {

       try {
           $em = $this->doctrine->em;
           $user = $em->getRepository('\Entity\Users')->findOneBy(array("id"=>$_GET['id']));
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

    public function getQuestion () {
        // TODO: Get user data only if it's an AJAX request
        try {
           $em = $this->doctrine->em;
           $user = $em->getRepository('\Entity\Users')->findOneBy(array("id"=>$_GET['id']));
           if($user !== null){
                $result['question']= $user->getQuestionText();
                $result['answer']= $user->getAnswer();
                $result['message'] = "success";
           }else{
                $result['message'] = "Error";
           }
       } catch(Exception $e) {
           \ChromePhp::log($e);
           $result['message'] = "Error";
       }
       echo json_encode($result);
    }

    public function setPassword () {
        // TODO: Get user data only if it's an AJAX request
        try {
           $em = $this->doctrine->em;
           $user = $em->getRepository('\Entity\Users')->findOneBy(array("id"=>$_GET['id']));
           if($user !== null) {
                $user->setPassword( $_GET['newPassword']);
                $em->merge($user);
                $em->persist($user);
                $em->flush();
                $result['message'] = "success";
           } else {
                $result['message'] = "Error";
           }
       } catch(Exception $e) {
           \ChromePhp::log($e);
           $result['message'] = "Error";
       }
       echo json_encode($result);
    }
}
