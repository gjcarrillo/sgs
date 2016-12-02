<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class pdf {

    public function _construct()
    {
        $CI = & get_instance();
        log_message('Debug', 'mPDF class is loaded.');
    }

    public function load()
    {
        include_once APPPATH.'/third_party/mpdf/mpdf.php';

        return new mPDF('utf-8', 'Letter', 12, 'Calibri', 20, 20, 20, 20, 16, 16);
    }
}
