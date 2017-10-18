<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: Kristopher
 * Date: 2/4/2017
 * Time: 8:31 PM
 */
class EmailModel extends CI_Model
{
    public function __construct() {
        $this->load->library('email');
        parent::__construct();
    }

    public function sendNewRequestEmail($reqId) {
        try {
            $em = $this->doctrine->em;
            $request = $em->find('\Entity\Request', $reqId);
            $this->load->model('requestsModel', 'requests');
            if (!$this->requests->isRequestValidated($request)) {
                throw new Exception('Esta solicitud no ha sido validada.');
            } else {
                $this->sendNewReqEmail($request);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function sendRequestUpdateEmail($reqId, $conceptStr, $changes) {
        try {
            $em = $this->doctrine->em;
            $request = $em->find('\Entity\Request', $reqId);
            $mailData['updates'] = $changes;
            $mailData['homeUrl'] = $this->config->base_url();
            $mailData['reqId'] = $reqId;
            $mailData['conceptStr'] = $conceptStr;
            $mailData['email'] = $request->getContactEmail();
            $mailData['subject'] = 'Actualización de Solicitud';
            $html = $this->load->view('templates/emailTemplates/updateEmail', $mailData, true); // render the view into HTML
            $this->sendEmail($mailData['email'], $mailData['subject'], $html);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function sendNewReqEmail($request) {
        try {
            $loanTypes = $this->configModel->getLoanTypes();
            $mailData['reqId'] = $request->getId();
            $user = $request->getUserOwner();
            $mailData['username'] = $user->getFirstName() . ' ' . $user->getLastName();
            $mailData['userId'] = $user->getId();
            $mailData['creationDate'] = $request->getCreationDate()->format('d/m/Y');
            $mailData['reqAmount'] = $request->getRequestedAmount();
            $mailData['tel'] = $request->getContactNumber();
            $mailData['email'] = $request->getContactEmail();
            $mailData['loanTypeString'] = $loanTypes[$request->getLoanType()]->DescripcionDelPrestamo;
            $mailData['due'] = $request->getPaymentDue();
            $mailData['paymentFee'] = $this->utils->calculatePaymentFee($mailData['reqAmount'], $mailData['due'], 12);
            $mailData['subject'] = 'Nueva Solicitud de Préstamo';
            $html = $this->load->view('templates/emailTemplates/newReqMail', $mailData, true); // render the view into HTML
            $this->sendEmail($mailData['email'], $mailData['subject'], $html);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function sendEmail ($to, $subject, $html) {
        try {
            $config['mailtype'] = 'html';
            $this->email->initialize($config);
            //Especificamos la direccion
            $this->email->from('<noreply@ipapedi.com>', 'IPAPEDI');
            $this->email->to($to);
            //Cuerpo del correo
            $this->email->subject($subject);
            $this->email->message($html);
            //Finalizamos
            $this->email->send();
        } catch (Exception $e) {
            throw $e;
        }
    }

}