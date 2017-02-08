<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: Kristopher
 * Date: 2/4/2017
 * Time: 9:18 PM
 */
class HistoryModel extends CI_Model
{
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

    public function registerValidationResend($reqId) {
        $em = $this->doctrine->em;
        $request = $em->find('\Entity\Request', $reqId);
        // Register History
        $history = new \Entity\History();
        $history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
        $history->setUserResponsable($this->session->name . ' ' . $this->session->name);
        // Register it's corresponding actions
        $history->setTitle($this->utils->getHistoryActionCode('validation'));
        $history->setOrigin($request);
        $request->addHistory($history);
        $action = new \Entity\HistoryAction();
        $action->setSummary("Reenvío de correo de validación.");
        $action->setDetail("Enviado nuevo correo de validación por solicitud de reenvío, a " .
                           "la dirección de correo " . $request->getContactEmail());
        $action->setBelongingHistory($history);
        $history->addAction($action);
        $em->persist($action);
        $em->persist($history);
        $em->merge($request);
        $em->flush();
        $em->clear();
    }

    public function registerValidationSending($reqId) {
        $em = $this->doctrine->em;
        $request = $em->find('\Entity\Request', $reqId);
        // Register History
        $history = new \Entity\History();
        $history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
        $history->setUserResponsable($_SESSION['name'] . ' ' . $_SESSION['lastName']);
        // Register it's corresponding actions
        $history->setTitle($this->utils->getHistoryActionCode('validation'));
        $history->setOrigin($request);
        $action = new \Entity\HistoryAction();
        $action->setSummary("Envío de correo de validación.");
        $action->setDetail("Enviado correo de validación a " .
                           "la dirección de correo " . $request->getContactEmail());
        $action->setBelongingHistory($history);
        $history->addAction($action);
        $em->persist($action);
        $em->persist($history);
        $em->merge($request);
        $em->flush();
    }

    public function registerValidation($reqId) {
        // Register History
        $em = $this->doctrine->em;
        $request = $em->find('\Entity\Request', $reqId);
        $history = new \Entity\History();
        $history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
        $history->setUserResponsable($_SESSION['name'] . ' ' . $_SESSION['lastName']);
        // Register it's corresponding actions
        $history->setTitle($this->utils->getHistoryActionCode('validation'));
        $history->setOrigin($request);
        $action = new \Entity\HistoryAction();
        $action->setSummary("Validación de solicitud");
        $action->setDetail("Solicitud validada a través del correo electrónico " . $request->getContactEmail());
        $action->setBelongingHistory($history);
        $history->addAction($action);
        $em->persist($action);
        $em->persist($history);
    }

}