<?php

/**
 * Created by PhpStorm.
 * User: Kristopher
 * Date: 2/4/2017
 * Time: 11:33 PM
 */
class ConfigModel extends CI_Model
{
    public function __construct() {
        parent::__construct();
    }

    public function getStatuses() {
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
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        return json_encode($result);
    }

    public function getStatusesForConfig() {
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
                    // Being used. Cannot be edited.
                    array_push($result['statuses']['inUse'], $status->getValue());
                } else {
                    // Unused status. Can be edited.
                    array_push($result['statuses']['existing'], $status->getValue());
                }
            }
            $result['message'] = 'success';
        } catch (Exception $e) {
            \ChromePhp::log($e);
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        return json_encode($result);
    }

    public function saveStatuses() {
        $data = json_decode($this->input->raw_input_stream, true);
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
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        return json_encode($result);
    }

    /** Max. requested amount configuration **/
    public function getMaxReqAmount() {
        try {
            $em = $this->doctrine->em;
            // Look for all configured statuses
            $config = $em->getRepository('\Entity\Config');
            // Get the max. request amount
            $maxAmount = $config->findOneBy(array("key" => 'MAX_AMOUNT'));
            return $maxAmount === null ? null : $maxAmount->getValue();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /** Min. requested amount configuration **/
    public function getMinReqAmount() {
        try {
            $em = $this->doctrine->em;
            // Look for all configured statuses
            $config = $em->getRepository('\Entity\Config');
            // Get the min. request amount
            $minAmount = $config->findOneBy(array("key" => 'MIN_AMOUNT'));
            return $minAmount === null ? null : $minAmount->getValue();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Sets both min. and max. request amount.
     */
    public function setReqAmount() {
        $data = json_decode($this->input->raw_input_stream, true);
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
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        return json_encode($result);
    }

    /** Requests month span for applying to same type of loan configuration **/

    public function getRequestsSpan() {
        try {
            $em = $this->doctrine->em;
            // Look for all configured statuses
            $config = $em->getRepository('\Entity\Config');
            // Get the configured span.
            $span = $config->findOneBy(array("key" => 'SPAN'));
            $result['span'] = $span === null ? null : $span->getValue();
            $result['message'] = 'success';
        } catch (Exception $e) {
            \ChromePhp::log($e);
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        return json_encode($result);
    }



    // Updates the month requests span, required to applying to same type of loan.
    public function updateRequestsSpan() {
        $data = json_decode($this->input->raw_input_stream, true);
        try {
            $em = $this->doctrine->em;
            // Look for all configured statuses
            $config = $em->getRepository('\Entity\Config');
            // Get the configured span.
            $span = $config->findOneBy(array("key" => 'SPAN'));
            $entry = array('key' => 'SPAN', 'value' => $data['span']);
            if ($span === null) {
                // Create it.
                $this->db->insert('config', $entry);
            } else {
                // Update it.
                $this->db->where('id', $span->getId());
                $this->db->update('config', $entry);
            }
            $result['message'] = 'success';
        } catch (Exception $e) {
            \ChromePhp::log($e);
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        return json_encode($result);
    }

}