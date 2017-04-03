<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use Mailgun\Mailgun;
/**
 * Created by PhpStorm.
 * User: Kristopher
 * Date: 2/4/2017
 * Time: 8:31 PM
 */
class EmailModel extends CI_Model
{
    public function __construct() {
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

    public function sendRequestUpdateEmail($reqId, $changes) {
        try {
            $em = $this->doctrine->em;
            $request = $em->find('\Entity\Request', $reqId);
            $mailData['updates'] = $changes;
            $mailData['homeUrl'] = $this->config->base_url();
            $mailData['reqId'] = $reqId;
            $mailData['email'] = $request->getContactEmail();
            $mailData['subject'] = 'Actualización de Solicitud';
            $html = $this->load->view('templates/updateEmail', $mailData, true); // render the view into HTML
            $this->sendEmail($mailData['email'], $mailData['subject'], $html);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function sendNewReqEmail($request) {
        try {
            $this->load->model('configModel');
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
            $html = $this->load->view('templates/newReqMail', $mailData, true); // render the view into HTML
            $this->sendEmail($mailData['email'], $mailData['subject'], $html);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function sendEmail ($to, $subject, $html) {
        try {
            $mgClient = new Mailgun(MAILGUN_PRIVATE_KEY, new \Http\Adapter\Guzzle6\Client());
            $email = array(
                'from'    => MAILGUN_SENDER,
                'to'      => $to,
                'subject' => $subject,
                'html'    => $html
            );
            $mgClient->sendMessage(MAILGUN_SENDER_DOMAIN, $email);
        } catch (Exception $e) {
            throw $e;
        }
    }

}