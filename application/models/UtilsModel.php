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
}