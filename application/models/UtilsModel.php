<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: Kristopher
 * Date: 2/4/2017
 * Time: 9:37 PM
 */
class UtilsModel extends CI_Model
{
    public function __construct() {
        parent::__construct();
    }

    /**
     * Calculates the monthly payment fee the applicant must pay.
     *
     * @param $reqAmount - the amount of money the applicant is requesting.
     * @param $paymentDue - number in months the applicant chose to pay his debt.
     * @param $interest - payment interest (percentage).
     * @return float - monthly payment fee.
     */
    public function calculatePaymentFee($reqAmount, $paymentDue, $interest) {
        $rate = $interest / 100 ;
        // monthly payment.
        $nFreq = 12;
        // calculate the interest as a factor
        $interestFactor = $rate / $nFreq;
        // calculate the monthly payment fee
        return $reqAmount / ((1 - pow($interestFactor +1, $paymentDue * -1)) / $interestFactor);
    }

    /**
     * Maps the loan type's code to it' corresponding string name.
     *
     * @param $typeCode - integer representing request type's code.
     * @return string representing the corresponding loan type.
     */
    public function mapLoanType($typeCode) {
       $loanTypes = LOAN_TYPES_NAMES;
        return $loanTypes[$typeCode];
    }

    /**
     * Obtains the history action code corresponding to a specified action.
     *
     * @param $action - action string.
     * @return integer - integer representing the specified history action.
     */
    public function getHistoryActionCode($action) {
        $actions = HISTORY_ACTIONS_CODES;
        return $actions[$action];
    }

    /**
     * Obtains the history action as string.
     *
     * @param $action - action code as integer.
     * @return integer - string representing the specified history action.
     */
    public function getHistoryActionName($action) {
        $actions = HISTORY_ACTIONS_NAMES;
        return $actions[$action];
    }

    /**
     * Maps the specified status to it' verb equivalent.
     *
     * @param $status - status string.
     * @return string - status string as verb.
     */
    public function statusToVerb($status) {
        $verbs = STATUSES_VERB;
        return $verbs[$status];
    }

    /**
     * Obtains all the requests statuses.
     *
     * @return array with all the statuses.
     * @throws Exception
     */
    public function getAllStatuses () {
        $theStatuses = STATUSES;
        try {
            $em = $this->doctrine->em;
            $statuses = $em->getRepository('\Entity\Config')->findBy(array('key' => 'STATUS'));
            foreach ($statuses as $status) {
                array_push($theStatuses, $status->getValue());
            }
        } catch (Exception $e) {
            throw $e;
        }
        return $theStatuses;
    }

    /**
     * Obtains the additional (configurable) requests statuses.
     *
     * @return array with all the additional statuses.
     * @throws Exception
     */
    public function getAdditionalStatuses () {
        $theStatuses = [];
        try {
            $em = $this->doctrine->em;
            $statuses = $em->getRepository('\Entity\Config')->findBy(array('key' => 'STATUS'));
            foreach ($statuses as $status) {
                array_push($theStatuses, $status->getValue());
            }
        } catch (Exception $e) {
            throw $e;
        }
        return $theStatuses;
    }

    /**
     * Randomly generates a new hexadecimal color.
     *
     * @param $existing - array with existing colors.
     * @return string - randomly (hex) color that is not present in $existing array.
     */
    private function rand_color($existing) {
        do {
            $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        } while (in_array($color, $existing));
        return $color;
    }

    /**
     * Generates a hexadecimal color code for a specified status.
     *
     * @param $status - current status to generate a color for.
     * @param $colors - already used colors (that can't be repeated).
     * @return string - (hex) color for the specified status.
     */
    public function generatePieBgColor($status, $colors) {
        switch ($status) {
            case RECEIVED: return '#FFD740'; // A200 amber
            case APPROVED: return '#00C853'; // A700 green
            case REJECTED: return '#FF5252'; // A200 red
            default: return $this->rand_color($colors);
        }
    }

    public function generatePieHoverColor($colour) {
        $brightness = -0.9; // 10% darker
        return($this->colourBrightness($colour,$brightness));
    }

    private function colourBrightness($hex, $percent) {
        // Work out if hash given
        $hash = '';
        if (stristr($hex,'#')) {
            $hex = str_replace('#','',$hex);
            $hash = '#';
        }
        /// HEX TO RGB
        $rgb = array(hexdec(substr($hex,0,2)), hexdec(substr($hex,2,2)), hexdec(substr($hex,4,2)));
        //// CALCULATE
        for ($i=0; $i<3; $i++) {
            // See if brighter or darker
            if ($percent > 0) {
                // Lighter
                $rgb[$i] = round($rgb[$i] * $percent) + round(255 * (1-$percent));
            } else {
                // Darker
                $positivePercent = $percent - ($percent*2);
                $rgb[$i] = round($rgb[$i] * $positivePercent) + round(0 * (1-$positivePercent));
            }
            // In case rounding up causes us to go to 256
            if ($rgb[$i] > 255) {
                $rgb[$i] = 255;
            }
        }
        //// RBG to Hex
        $hex = '';
        for($i=0; $i < 3; $i++) {
            // Convert the decimal digit to hex
            $hexDigit = dechex($rgb[$i]);
            // Add a leading zero if necessary
            if(strlen($hexDigit) == 1) {
                $hexDigit = "0" . $hexDigit;
            }
            // Append to the hex string
            $hex .= $hexDigit;
        }
        return $hash.$hex;
    }

    /**
     * Looks for a specific loan type still opened request from the specified user.
     *
     * @param $uid - user's id.
     * @param $loanType - loan type.
     * @return bool {@code true} if said request type has an opened request.
     * @throws Exception
     */
    public function checkPreviousRequests ($uid, $loanType) {
        try {
            $em = $this->doctrine->em;
            $user = $em->find('\Entity\User', $uid);
            $requests = $user->getRequests();
            $canCreate = true;
            foreach ($requests as $request) {
                if ($request->getLoanType() === $loanType &&
                    $request->getStatus() !== APPROVED &&
                    $request->getStatus() !== REJECTED) {
                    // There is another request of the same type still opened.
                    $canCreate = false;
                }
            }
            return $canCreate;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Extracts loan terms of the specified loan type.
     *
     * @param $loanType - loan type object.
     * @return array containing the loan terms.
     */
    public function extractLoanTerms ($loanType) {
        $terms = array();
        $term = intval($loanType->PlazoEnMeses, 10);
        while ($term > 0) {
            array_push($terms, $term);
            $term -= 12;
        }
        return $terms;
    }

    /**
     * Verifies if specified loan type matches any of the existing loan types.
     *
     * @param $loanTypes - loan types from config
     * @param $loanType - loan type to match.
     * @return bool - {@code true} if loan Type is valid.
     */
    public function isRequestTypeValid($loanTypes, $loanType) {
        $exists = false;
        foreach ($loanTypes as $tKey => $types) {
            if ($tKey == $loanType) {
                $exists = true;
            }
        }
        return $exists;
    }

    /**
     * Gets the annual interest rate of a specific loan type.
     *
     * @param $loanType - the loan type.
     * @return int - annual interest rate.
     */
    public function getInterestRate ($loanType) {
        switch ($loanType) {
            case PERSONAL_LOAN:
                return 12;
            case CASH_VOUCHER:
                return 2;
            default:
                return 12;
        }
    }

    /**
     * Provides an error message based on exception object.
     *
     * @param $e - exception object.
     * @return string - string containing error message.
     */
    public function getErrorMsg($e) {
        return "Ha ocurrido un error en el sistema: " . $e->getMessage();
    }
}