<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class DocumentGenerator extends CI_Controller {
	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

    public function generatePdf() {
        // As PDF creation takes a bit of memory, we're saving the created file in /downloads/reports/
        $pdfFilePath = DropPath . "contract.pdf";
        \ChromePhp::log($pdfFilePath);
        $data['page_title'] = 'Hello world'; // pass data to the view

        if (file_exists($pdfFilePath) == FALSE)
        {
            \ChromePhp::log("File doesn't exist. Genrating pdf...");
            // ini_set('memory_limit','32M'); // boost the memory limit if it's low <img class="emoji" draggable="false" alt="" src="https://s.w.org/images/core/emoji/72x72/1f609.png">
            $html = $this->load->view('templates/pdfTemplate', $data, true); // render the view into HTML
            \ChromePhp::log("View loaded");
            $this->load->library('pdf');
            \ChromePhp::log("PDF library init");
            $pdf = $this->pdf->load();
            \ChromePhp::log("PDF library loaded");
            // $pdf->SetFooter($_SERVER['HTTP_HOST'].'|{PAGENO}|'.date(DATE_RFC822)); // Add a footer for good measure <img class="emoji" draggable="false" alt="" src="https://s.w.org/images/core/emoji/72x72/1f609.png">
            $pdf->WriteHTML($html); // write the HTML into the PDF
            $pdf->Output($pdfFilePath, 'F'); // save to file because we can
            \ChromePhp::log("PDF generation success!");
        }

        // redirect("/downloads/reports/$filename.pdf");
    }
}
