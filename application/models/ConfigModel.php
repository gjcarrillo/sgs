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
            $result['message'] = $this->utils->getErrorMsg($e);
        }
        return json_encode($result);
    }

    public function getStatusesForConfig() {
        $result['inUse'] = [];
        $result['existing'] = [];
        try {
            $em = $this->doctrine->em;
            // Look for all configured statuses
            $config = $em->getRepository('\Entity\Config');
            $requestsRepo = $em->getRepository('\Entity\Request');
            $statuses = $config->findBy(array("key" => 'STATUS'));
            foreach ($statuses as $status) {
                if (count($requestsRepo->findBy(array('status' => $status->getValue()))) > 0) {
                    // Being used. Cannot be edited.
                    array_push($result['inUse'], $status->getValue());
                } else {
                    // Unused status. Can be edited.
                    array_push($result['existing'], $status->getValue());
                }
            }
            return $result;
        } catch (Exception $e) {
            throw $e;
        }
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
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getLoanTypes() {
        $type[PERSONAL_LOAN] = $this->getPersonalLoan(1);
        $type[CASH_VOUCHER] = $this->getShortTermLoan(1);

        return $type;
    }

    private function getPersonalLoan($id){
        $this->ipapedi_db = $this->load->database('ipapedi_db', true);
        $this->ipapedi_db->select('*');
        $this->ipapedi_db->from('prestamospersonales');
        $this->ipapedi_db->where('id', $id);
        $query = $this->ipapedi_db->get();
        if (empty($query->result())) {
            return null;
        } else {
            $loanResult = $query->result()[0];
            return $loanResult;
        }
    }

    private function getShortTermLoan($id) {
        $this->ipapedi_db = $this->load->database('ipapedi_db', true);
        $this->ipapedi_db->select('*');
        $this->ipapedi_db->from('prestamosacortoplazo');
        $this->ipapedi_db->where('id', $id);
        $query = $this->ipapedi_db->get();
        if (empty($query->result())) {
            return null;
        } else {
            return $query->result()[0];
        }
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
        } catch (Exception $e) {
            throw $e;
        }
    }

    /** Requests month span for applying to same type of loan configuration **/

    public function getRequestsSpan() {
        try {
            // Get loan types.
            $loanTypes = $this->getLoanTypes();
            $em = $this->doctrine->em;
            // Look for this loan type's configured span.
            $config = $em->getRepository('\Entity\Config');
            foreach ($loanTypes as $lKey => $loanType) {
                // Get this loan type's configured span. Key = SPAN + loan's concept
                $span = $config->findOneBy(array("key" => "SPAN" . $lKey));
                $loanType->span = $span === null ? null : intval($span->getValue(), 10);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return $loanTypes;
    }

    /**
     * Obtains the configured loan types.
     *
     * @return mixed - loan types info with their configured payment terms.
     * @throws Exception
     */
    public function getRequestsTerms() {
        try {
            // Get loan types.
            $loanTypes = $this->getLoanTypes();
            $em = $this->doctrine->em;
            // Look for this loan type's configured terms.
            $config = $em->getRepository('\Entity\Config');
            foreach ($loanTypes as $lKey => $loanType) {
                // Get this loan type's configured span. Key = TERMS + loan's concept
                $termEntities = $config->findBy(array("key" => "TERMS" . $lKey));
                $terms = array();
                foreach ($termEntities as $term) {
                    array_push($terms, intval($term->getValue(), 10));
                }
                $loanTypes[$lKey]->terms = $terms;
            }
        } catch (Exception $e) {
            throw $e;
        }
        return $loanTypes;
    }

    /**
     * Gets a specified request concept's configured loan terms.
     *
     * @param $concept - request concept.
     * @return array containing the configured terms.
     * @throws Exception
     */
    public function getRequestTerms($concept) {
        try {
            $loanTypes = $this->getLoanTypes();
            $em = $this->doctrine->em;
            // Look for this loan type's configured terms.
            $config = $em->getRepository('\Entity\Config');
            $termEntity = $config->findBy(array("key" => "TERMS" . $concept));
            $terms = array();
            foreach ($termEntity as $term) {
                array_push($terms, intval($term->getValue(), 10));
            }
            array_push($terms, $loanTypes[$concept]->PlazoEnMeses);
        } catch (Exception $e) {
            throw $e;
        }
        return $terms;
    }

    /**
     * Gets a specific loan type's month span for applying to same type of loan.
     *
     * @param $concept - loan's concept.
     * @return int|null {@code int} with the configured span.
     * {@code null} if there is no configured span for this type of loan.
     * @throws Exception
     */

    public function getRequestSpan($concept) {
        try {
            // Get loan types.
            $em = $this->doctrine->em;
            // Look for this loan type's configured span.
            $config = $em->getRepository('\Entity\Config');
            // Get this loan type's configured span. Key = SPAN + loan's concept
            $span = $config->findOneBy(array("key" => "SPAN" . $concept));
            $span = $span === null ? null : intval($span->getValue(), 10);
        } catch (Exception $e) {
            throw $e;
        }
        return $span;
    }

    // Updates the month requests span, required to applying to same type of loan.
    public function updateRequestsSpan($loanTypes) {
        try {
            $em = $this->doctrine->em;
            // Look for all configured statuses
            $config = $em->getRepository('\Entity\Config');
            // Get the configured span.
            foreach ($loanTypes as $lKey => $loanType) {
                if ($loanType['span'] === null) continue;
                $span = $config->findOneBy(array("key" => "SPAN" . $lKey));
                $entry = array('key' => "SPAN" . $lKey, 'value' => $loanType['span']);
                if ($span === null) {
                    // Create it.
                    $this->db->insert('config', $entry);
                } else if ($span->getValue() != $loanType['span']){
                    // Update it.
                    $this->db->where('id', $span->getId());
                    $this->db->update('config', $entry);
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Updates the requests terms for each loan type.
    public function updateRequestsTerms($loanTypes) {
        try {
            $em = $this->doctrine->em;
            // Look for all configured statuses
            $config = $em->getRepository('\Entity\Config');
            // Get the configured span.
            foreach ($loanTypes as $lKey => $loanType) {
                // Look for all configured terms for this loan type
                $termEntities = $config->findBy(array("key" => "TERMS" . $lKey));
                // Remove all existing terms.
                foreach ($termEntities as $entity) {
                    $em->remove($entity);
                }
                $em->flush();
                // Re create all terms.
                foreach ($loanType['terms'] as $term) {
                    $entry = array('key' => "TERMS" . $lKey, 'value' => $term);
                    $this->db->insert('config', $entry);
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

}