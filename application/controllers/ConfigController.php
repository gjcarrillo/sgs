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

    public function index() {
        if ($_SESSION['type'] != 2) {
            $this->load->view('errors/index.html');
        } else {
            $this->load->view('systemConfig');
        }
    }

    /** Requests' status configuration **/

    public function getStatuses() {
        if ($_SESSION['type'] != 2) {
            $this->load->view('errors/index.html');
        } else {
            $result['statuses'] = [];
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
    }

    public function getStatusesForConfig() {
        if ($_SESSION['type'] != 2) {
            $this->load->view('errors/index.html');
        } else {
            $result['statuses']['inUse'] = [];
            $result['statuses']['existing'] = [];
            try {
                $em = $this->doctrine->em;
                // Look for all configured statuses
                $config = $em->getRepository('\Entity\Config');
                $requestsRepo = $em->getRepository('\Entity\Request');
                $statuses = $config->findBy(array("key" => 'STATUS'));
                foreach ($statuses as $status) {
                    if (count($requestsRepo->findBy(array('status' => $status->getValue()))) > 0) {
                        // Being used.
                        array_push($result['statuses']['inUse'], $status->getValue());
                    } else {
                        array_push($result['statuses']['existing'], $status->getValue());
                    }
                }
                $result['message'] = 'success';
            } catch (Exception $e) {
                \ChromePhp::log($e);
                $result['message'] = 'error';
            }
            echo json_encode($result);
        }
    }

    public function saveStatuses() {
        if ($_SESSION['type'] != 2) {
            $this->load->view('errors/index.html');
        } else {
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
                $em->flush();
                // Create all the statuses.
                foreach ($data['statuses'] as $newStatus) {
                    // Inserting with doctrine produces syntax errors!...
                    $newData = array(
                        'key' => 'STATUS',
                        'value' => $newStatus
                    );
                    $this->db->insert('config', $newData);
                }
                $result['message'] = 'success';
            } catch (Exception $e) {
                \ChromePhp::log($e);
                $result['message'] = 'error';
            }
            echo json_encode($result);
        }
    }

    /** Max. requested amount configuration **/
    public function getMaxReqAmount() {
        if ($_SESSION['type'] != 2) {
            $this->load->view('errors/index.html');
        } else {
            try {
                $em = $this->doctrine->em;
                // Look for all configured statuses
                $config = $em->getRepository('\Entity\Config');
                // Get the max. request amount
                $maxAmount = $config->findOneBy(array("key" => 'MAX_AMOUNT'));
                $result['maxAmount'] = $maxAmount === null ? null : $maxAmount->getValue();
                $result['message'] = 'success';
            } catch (Exception $e) {
                \ChromePhp::log($e);
                $result['message'] = 'error';
            }
            echo json_encode($result);
        }
    }

    /** Min.  requested amount configuration **/
    public function getMinReqAmount() {
        if ($_SESSION['type'] != 2) {
            $this->load->view('errors/index.html');
        } else {
            try {
                $em = $this->doctrine->em;
                // Look for all configured statuses
                $config = $em->getRepository('\Entity\Config');
                // Get the min. request amount
                $minAmount = $config->findOneBy(array("key" => 'MIN_AMOUNT'));
                $result['minAmount'] = $minAmount === null ? null : $minAmount->getValue();
                $result['message'] = 'success';
            } catch (Exception $e) {
                \ChromePhp::log($e);
                $result['message'] = 'error';
            }
            echo json_encode($result);
        }
    }

    /**
     * Sets both min. and max. request amount.
     */
    public function setReqAmount() {
        if ($_SESSION['type'] != 2) {
            $this->load->view('errors/index.html');
        } else {
            $data = json_decode(file_get_contents('php://input'), true);
            try {
                $em = $this->doctrine->em;
                // Look for all configured statuses
                $config = $em->getRepository('\Entity\Config');
                // Get the max. request amount
                $maxAmount = $config->findOneBy(array("key" => 'MAX_AMOUNT'));
                $minAmount = $config->findOneBy(array("key" => 'MIN_AMOUNT'));
                $newMaxData = array(
                    'key' => 'MAX_AMOUNT',
                    'value' => $data['maxAmount']
                );
                $newMinData = array(
                    'key' => 'MIN_AMOUNT',
                    'value' => $data['minAmount']
                );
                if ($maxAmount === null) {
                    // Create it.
                    $this->db->insert('config', $newMaxData);
                } else {
                    // Update it.
                    $this->db->where('id', $maxAmount->getId());
                    $this->db->update('config', $newMaxData);
                }
                if ($minAmount === null) {
                    // Create it.
                    $this->db->insert('config', $newMinData);
                } else {
                    // Update it.
                    $this->db->where('id', $minAmount->getId());
                    $this->db->update('config', $newMinData);
                }
                $result['message'] = 'success';
            } catch (Exception $e) {
                \ChromePhp::log($e);
                $result['message'] = 'error';
            }
            echo json_encode($result);
        }
    }
}
