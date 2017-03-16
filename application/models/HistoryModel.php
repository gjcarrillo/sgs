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
        try {
            $em = $this->doctrine->em;
            $request = $em->find('\Entity\Request', $reqId);
            $this->load->model('requestsModel', 'requests');
            if ($this->requests->isRequestValidated($request)) {
                throw new Exception('Esta solicitud ya ha sido validada.');
            } else {
                // Register History
                $history = new \Entity\History();
                $history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
                $history->setUserResponsible($this->users->getUser($this->session->id));
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
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function registerValidationSending($reqId) {
        try {
            $em = $this->doctrine->em;
            $request = $em->find('\Entity\Request', $reqId);
            $this->load->model('requestsModel', 'requests');
            if ($this->requests->isRequestValidated($request)) {
                throw new Exception('Esta solicitud ya ha sido validada.');
            } else {
                // Register History
                $history = new \Entity\History();
                $history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
                $history->setUserResponsible($this->users->getUser($this->session->id));
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
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function registerValidation($reqId) {
        try {
            // Register History
            $em = $this->doctrine->em;
            $request = $em->find('\Entity\Request', $reqId);
            $this->load->model('requestsModel', 'requests');
            if ($this->requests->isRequestValidated($request)) {
                throw new Exception('Esta solicitud ya ha sido validada.');
            } else {
                $history = new \Entity\History();
                $history->setDate(new DateTime('now', new DateTimeZone('America/Barbados')));
                $history->setUserResponsible($this->users->getUser($this->session->id));
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
        } catch (Exception $e) {
            throw $e;
        }
    }

}