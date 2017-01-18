<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');
/**
 * Created by PhpStorm.
 * User: Kristopher
 * Date: 1/17/2017
 * Time: 11:41 PM
 */
class ConfigController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
    }

    /** Requests' status configuration **/

    public function getStatuses() {
        try {
            $em = $this->doctrine->em;
            // Look for all configured statuses
            $config = $em->getRepository('\Entity\Config');
            $statuses = $config->findBy(array("key" => 'STATUS'));
            foreach ($statuses as $cKey => $status) {
                $result['statuses'][$cKey] = $status->getValue();
            }
            $result['message'] = 'success';
        } catch (Exception $e) {
            \ChromePhp::log($e);
            $result['message'] = 'error';
        }
        echo json_encode($result);
    }

    public function setStatuses() {
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $em = $this->doctrine->em;
            // Look for all configured statuses
            $config = $em->getRepository('\Entity\Config');
            $statuses = $config->findBy(array("key" => 'STATUS'));
            // Remove all existing statuses.
            foreach ($statuses as $status) {
                $em->remove($status);
            }
            // Create all the statuses.
            foreach ($data['statuses'] as $newStatus) {
                $statusEntity = new \Entity\Config();
                $statusEntity->setKey('STATUS');
                $statusEntity->setValue($newStatus);
                $em->persist($statusEntity);
            }
            $em->flush();
            $result['message'] = 'success';
        } catch (Exception $e) {
            \ChromePhp::log($e);
            $result['message'] = 'error';
        }
        echo json_encode($result);
    }

    /** Max. requested amount configuration **/
    public function getMaxReqAmount() {
        try {
            $em = $this->doctrine->em;
            // Look for all configured statuses
            $config = $em->getRepository('\Entity\Config');
            // Get the max. request amount
            $result['maxAmount'] = $config->findOneBy(array("key" => 'MAX_AMOUNT'))->getValue();
            $result['message'] = 'success';
        } catch (Exception $e) {
            \ChromePhp::log($e);
            $result['message'] = 'error';
        }
        echo json_encode($result);
    }

    public function setMaxReqAmount() {
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $em = $this->doctrine->em;
            // Look for all configured statuses
            $config = $em->getRepository('\Entity\Config');
            // Get the max. request amount
            $maxAmount = $config->findOneBy(array("key" => 'MAX_AMOUNT'));
            // Update it.
            $maxAmount->setValue($data['maxAmount']);
            $em->merge($maxAmount);
            $result['message'] = 'success';
        } catch (Exception $e) {
            \ChromePhp::log($e);
            $result['message'] = 'error';
        }
        echo json_encode($result);
    }

    /** Min.  requested amount configuration **/
    public function getMinReqAmount() {
        try {
            $em = $this->doctrine->em;
            // Look for all configured statuses
            $config = $em->getRepository('\Entity\Config');
            // Get the min. request amount
            $result['minAmount'] = $config->findOneBy(array("key" => 'MIN_AMOUNT'))->getValue();
            $result['message'] = 'success';
        } catch (Exception $e) {
            \ChromePhp::log($e);
            $result['message'] = 'error';
        }
        echo json_encode($result);
    }

    public function setMinReqAmount() {
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $em = $this->doctrine->em;
            // Look for all configured statuses
            $config = $em->getRepository('\Entity\Config');
            // Get the min. request amount
            $minAmount = $config->findOneBy(array("key" => 'MIN_AMOUNT'));
            // Update it.
            $minAmount->setValue($data['minAmount']);
            $em->merge($minAmount);
            $result['message'] = 'success';
        } catch (Exception $e) {
            \ChromePhp::log($e);
            $result['message'] = 'error';
        }
        echo json_encode($result);
    }
}
