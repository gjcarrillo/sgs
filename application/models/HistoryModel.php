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
        $actions = HISTORY_ACTIONS_CODES;
        // Register History
        $history = new \Entity\History();
        $history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
        $history->setUserResponsable($this->session->name . ' ' . $this->session->name);
        // Register it's corresponding actions
        $history->setTitle($actions['validation']);
        $history->setOrigin($request);
        $request->addHistory($history);
        $action = new \Entity\HistoryAction();
        $action->setSummary("Reenv?o de correo de validaci?n.");
        $action->setDetail("Enviado nuevo correo de validaci?n por solicitud de reenv?o, a " .
                           "la direcci?n de correo " . $request->getContactEmail());
        $action->setBelongingHistory($history);
        $history->addAction($action);
        $em->persist($action);
        $em->persist($history);
        $em->merge($request);
        $em->flush();
        $em->clear();
    }

}