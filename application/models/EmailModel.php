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
            $tokenData['uid'] = $request->getUserOwner()->getId();
            $tokenData['rid'] = $reqId;
            $tokenData['reqAmount'] = $request->getRequestedAmount();
            $tokenData['tel'] = $request->getContactNumber();
            $tokenData['email'] = $request->getContactEmail();
            $tokenData['due'] = $request->getPaymentDue();
            $tokenData['loanType'] = $request->getLoanType();
            $this->sendValidationToken($tokenData, $request);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function sendValidationToken($tokenData, $request) {
        try {
            $loanTypes = LOAN_TYPES_NAMES;
            $encodedURL = $this->createToken($tokenData);
            $mailData['reqId'] = $request->getId();
            $user = $request->getUserOwner();
            $mailData['username'] = $user->getFirstName() . ' ' . $user->getLastName();
            $mailData['userId'] = $user->getId();
            $mailData['creationDate'] = $request->getCreationDate()->format('d/m/Y');
            $mailData['reqAmount'] = $request->getRequestedAmount();
            $mailData['tel'] = $request->getContactNumber();
            $mailData['email'] = $request->getContactEmail();
            $mailData['loanTypeString'] = $loanTypes[$request->getLoanType()];
            $mailData['due'] = $request->getPaymentDue();
            $mailData['paymentFee'] = $this->utils->calculatePaymentFee($mailData['reqAmount'], $mailData['due'], 12);
            $mailData['subject'] = '[Solicitud ' . str_pad($mailData['reqId'], 6, '0', STR_PAD_LEFT) .
                                   '] Confirmación de Nueva Solicitud';
            $mailData['validationURL'] = $this->config->base_url() . '#validate/' . $encodedURL;
            $reqTokenData['rid'] = $request->getId();
            $mailData['deleteURL'] = $this->config->base_url() . '#delete/' . $this->createToken($reqTokenData);
            $html = $this->load->view('templates/validationMail', $mailData, true); // render the view into HTML
            $this->sendEmail($mailData['email'], $mailData['subject'], $html);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function createToken ($data) {
        try {
            $encoded = JWT::encode($data, JWT_SECRET_KEY);
            $urlEncoded = JWT::urlsafeB64Encode($encoded);
            return $urlEncoded;
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
            \ChromePhp::log("Message sent!");
        } catch (Exception $e) {
            throw $e;
        }
    }

}