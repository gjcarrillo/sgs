<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class DocumentGenerator extends CI_Controller {
	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

    public function index() {
        $this->load->view('generator/DocumentGenerator');
    }

    public function generatePdf() {
        // As PDF creation takes a bit of memory, we're saving the created file in /downloads/reports/
        $pdfFilePath = DropPath . "contract.pdf";
        \ChromePhp::log("Genrating pdf...");
        // ini_set('memory_limit','32M'); // boost the memory limit if it's low <img class="emoji" draggable="false" alt="" src="https://s.w.org/images/core/emoji/72x72/1f609.png">
        // $stylesheet = file_get_contents('css/materialize.min.css');
        $html = $this->load->view('templates/pdfTemplate', $_GET, true); // render the view into HTML
        $this->load->library('pdf');
        $pdf = $this->pdf->load();
        // $pdf->WriteHTML($stylesheet, 1);
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($pdfFilePath, 'F'); // save to file
        \ChromePhp::log("PDF generation success!");

		$result['docName'] = "contract.pdf";
		$result['message'] = "success";
		echo json_encode($result);
    }

	public function download() {
		header('Content-Disposition: attachment; filename=' . $_GET['docName']);
		// The document source
		readfile(DropPath . $_GET['docName']);
	}
}
