<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include (APPPATH. '/libraries/ChromePhp.php');

class DocumentGenerator extends CI_Controller {
	public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }

    public function index() {
        $this->load->view('DocumentGenerator');
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

	public function generateRequestsReport() {
		if ($_SESSION['type'] != 2) {
			$this->load->view('errors/index.html');
		} else {
			$data = json_decode(file_get_contents('php://input'), true);
			// load PHPExcel library
			$this->load->library('excel');
			// activate worksheet number 1
			$this->excel->setActiveSheetIndex(0);
			// name the worksheet
			$this->excel->getActiveSheet()->setTitle($data['sheetTitle']);
			// Fill the content
			$this->excel->getActiveSheet()->fromArray((array)$data['header'], NULL, 'A1');
			// Offset will give **starting** cell
			// Report header offset plus one empty row
			$offset = count($data['header']) + 2;
			// Set table title
			$this->excel->getActiveSheet()->setCellValue('A' . $offset, $data['dataTitle']);
			// data title offset plus one empty row
			$offset += 2;
			// Set table header
			$this->excel->getActiveSheet()->fromArray((array)$data['dataHeader'], NULL, 'A' . $offset);
			// dataHeader offset
			$offset++;
			// Set table data
			$this->excel->getActiveSheet()->fromArray((array)$data['data'], NULL, 'A' . $offset);
			// data offset
			$offset += count($data['data']);
			// Extend table for total amounts
			$this->excel->getActiveSheet()->fromArray((array)$data['total'], NULL, 'F' . $offset);
			$this->excel->getActiveSheet()->setCellValue('G' . $offset, '=SUM(F7:F' . ($offset-1) . ')');
			$this->excel->getActiveSheet()->setCellValue('G' . ($offset+1), '=SUM(G7:G' . ($offset-1) . ')');
			// Add extended table portion's offset and an empty row
			$offset += 2;
			// Add stats table
			$this->excel->getActiveSheet()->fromArray((array)$data['stats']['title'], NULL, 'A' . $offset);
			// Add stats table title offset & an empty row
			$offset += 2;
			$this->excel->getActiveSheet()->fromArray((array)$data['stats']['dataHeader'], NULL, 'A' . $offset);
			// Add stats dataheader offset
			$offset++;
			$this->excel->getActiveSheet()->fromArray((array)$data['stats']['data'], NULL, 'A' . $offset);
			// Add count stat formula
			$this->excel->getActiveSheet()->setCellValue('B' . $offset, '=COUNTIF(D7:D'. ($offset-6) . ',"Recibida")');
			$this->excel->getActiveSheet()->setCellValue('B' . ($offset+1), '=COUNTIF(D7:D'. ($offset-6) . ',"Aprobada")');
			$this->excel->getActiveSheet()->setCellValue('B' . ($offset+2), '=COUNTIF(D7:D'. ($offset-6) . ',"Rechazada")');
			// Add porcentage stat formula
			$this->excel->getActiveSheet()
				->setCellValue('C' . $offset, '=ROUND(B'. $offset .' * 100 / ROWS(D7:D' . ($offset-6) . '), 2)');
			$this->excel->getActiveSheet()
				->setCellValue('C' . ($offset+1), '=ROUND(B'. ($offset+1) .' * 100 / ROWS(D7:D' . ($offset-6) . '), 2)');
			$this->excel->getActiveSheet()
				->setCellValue('C' . ($offset+2), '=ROUND(B'. ($offset+2) .' * 100 / ROWS(D7:D' . ($offset-6) . '), 2)');

			// Merge header and dataTitle cells
			$this->excel->getActiveSheet()->mergeCells('A1:H1');
			$this->excel->getActiveSheet()->mergeCells('A2:H2');
			$this->excel->getActiveSheet()->mergeCells('A4:H4');
			$this->excel->getActiveSheet()->mergeCells('A' . ($offset-3) . ':C' . ($offset-3));
			// Align horizontally, as a title is supposed to be.
			$this->excel->getActiveSheet()->getStyle('A4')->getAlignment()
			->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$tableBorders = array(
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				),
			);
			$headerStyle = array(
				'fill' => array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'startcolor' => array(
						'rgb' => 'CCCCCC'
					)
				)
			);
			// Table offset
			$tableOffset = 6 + count($data['data']);
			$statTableOffset = $offset + 2;
			// Add table style
			$this->excel->getActiveSheet()->getStyle('A6:H' . $tableOffset)->applyFromArray($tableBorders);
			$this->excel->getActiveSheet()->getStyle('A' . ($offset-1) . ':C' . $statTableOffset)
				->applyFromArray($tableBorders);
			// Extend table style for requested & approved total amount
			$this->excel->getActiveSheet()->getStyle('F' . ($tableOffset+1) . ':G' . ($tableOffset+2))
				->applyFromArray($tableBorders);
			// Add table header style
			$this->excel->getActiveSheet()->getStyle('A6:H6')->applyFromArray($headerStyle);
			$this->excel->getActiveSheet()->getStyle('A' . ($offset-1) . ':C' . ($offset-1))
				->applyFromArray($headerStyle);
			// Add table data numbers separator
			$this->excel->getActiveSheet()->getStyle('F7:G' . ($tableOffset+2))->getNumberFormat()
				->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
			// Configure columns width
			$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
			$this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
			// (PATCH) Initialize cell selection, otherwise might get a bit crazy
			$this->excel->getActiveSheet()->setSelectedCells('A1');
			// Save our workbook as this file name
			$filename="REPORTE - " . $data['filename'] . ".xls";
			// save it to Excel5 format (excel 2003 .XLS file)
			$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
			// Create the excel
			$objWriter->save(DropPath . $filename);
			// Successful operation
			$result['lpath'] = $filename;
			$result['message'] = "success";
		}
		echo json_encode($result);
	}

	public function generateStatusRequestsReport() {
		if ($_SESSION['type'] != 2) {
			$this->load->view('errors/index.html');
		} else {
			$data = json_decode(file_get_contents('php://input'), true);
			// load PHPExcel library
			$this->load->library('excel');
			// activate worksheet number 1
			$this->excel->setActiveSheetIndex(0);
			// name the worksheet
			$this->excel->getActiveSheet()->setTitle('Reporte por estatus');
			// Fill the content
			$this->excel->getActiveSheet()->fromArray((array)$data['header'], NULL, 'A1');
			// Offset will give **starting** cell
			// Report header offset plus one empty row
			$offset = count($data['header']) + 2;
			// Set table title
			$this->excel->getActiveSheet()->setCellValue('A' . $offset, $data['dataTitle']);
			// data title offset plus one empty row
			$offset += 2;
			// Set table header
			$this->excel->getActiveSheet()->fromArray((array)$data['dataHeader'], NULL, 'A' . $offset);
			// dataHeader offset
			$offset++;
			// Set table data
			$this->excel->getActiveSheet()->fromArray((array)$data['data'], NULL, 'A' . $offset);
			// data offset
			$offset += count($data['data']);
			// Extend table for total amounts
			if ($data['status'] == "Recibida") {
				// Don't take reunion number into account
				$this->excel->getActiveSheet()->fromArray((array)$data['total'], NULL, 'C' . $offset);
				$totalStartCell = 'C';
				$requestedCell = $totalValCell = 'D';
				$lastCell = 'E';
			} else if ($data['status'] == "Rechazada") {
				// Take reunion number into account
				$this->excel->getActiveSheet()->fromArray((array)$data['total'], NULL, 'D' . $offset);
				$totalStartCell = 'D';
				$requestedCell = $totalValCell = 'E';
				$lastCell = 'F';
			} else {
				$this->excel->getActiveSheet()->fromArray((array)$data['total'], NULL, 'E' . $offset);
				$totalStartCell = $requestedCell = 'E';
				$totalValCell = 'F';
				$lastCell = 'G';
			}
			$this->excel->getActiveSheet()
				->setCellValue($totalValCell . $offset, '=SUM(' . $requestedCell . '7:' . $requestedCell . ($offset-1) . ')');
			if ($data['status'] == "Aprobada") {
				$this->excel->getActiveSheet()->setCellValue($totalValCell . ($offset+1), '=SUM(F7:F' . ($offset-1) . ')');
			}
			// Merge header and dataTitle cells
			$this->excel->getActiveSheet()->mergeCells('A1:' . $lastCell .'1');
			$this->excel->getActiveSheet()->mergeCells('A2:' . $lastCell . '2');
			$this->excel->getActiveSheet()->mergeCells('A4:' . $lastCell .'4');
			// Align horizontally, as a title is supposed to be.
			$this->excel->getActiveSheet()->getStyle('A4')->getAlignment()
			->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$tableBorders = array(
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				),
			);
			$headerStyle = array(
				'fill' => array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'startcolor' => array(
						'rgb' => 'CCCCCC'
					)
				)
			);
			// Table offset
			$tableOffset = 6 + count($data['data']);
			$tableExtensionEnd = $data['status'] == "Aprobada" ? $tableOffset+2 : $tableOffset+1;
			// Add table style
			$this->excel->getActiveSheet()->getStyle('A6:' . $lastCell . $tableOffset)->applyFromArray($tableBorders);
			// Extend table style for requested & approved total amount
			$this->excel->getActiveSheet()->getStyle($totalStartCell . ($tableOffset+1) . ':' . $totalValCell . $tableExtensionEnd)
				->applyFromArray($tableBorders);
			// Add table header style
			$this->excel->getActiveSheet()->getStyle('A6:' . $lastCell . '6')->applyFromArray($headerStyle);
			// Add table data numbers separator
			$this->excel->getActiveSheet()->getStyle($totalStartCell . 	'7:' . $totalValCell . ($tableOffset+2))->getNumberFormat()
				->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
			// Configure columns width
			$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
			$this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
			if ($lastCell >= 'F') {
				$this->excel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
			}
			if ($lastCell >= 'G') {
				$this->excel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
			}
			// (PATCH) Initialize cell selection, otherwise might get a bit crazy
			$this->excel->getActiveSheet()->setSelectedCells('A1');
			// Save our workbook as this file name
			$filename="REPORTE - " . $data['dataTitle'] . ".xls";
			// save it to Excel5 format (excel 2003 .XLS file)
			$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
			// Create the excel
			$objWriter->save(DropPath . $filename);
			// Successful operation
			$result['lpath'] = $filename;
			$result['message'] = "success";
		}
		echo json_encode($result);
	}

	public function generateApprovedRequestsReport() {
		if ($_SESSION['type'] != 2) {
			$this->load->view('errors/index.html');
		} else {
			$data = json_decode(file_get_contents('php://input'), true);
			// load PHPExcel library
			$this->load->library('excel');
			// activate worksheet number 1
			$this->excel->setActiveSheetIndex(0);
			// name the worksheet
			$this->excel->getActiveSheet()->setTitle("Solicitudes aprobadas");
			// Fill the content
			$this->excel->getActiveSheet()->fromArray((array)$data['header'], NULL, 'A1');
			// Offset will give **starting** cell
			// Report header offset plus one empty row
			$offset = count($data['header']) + 2;
			// Set table title
			$this->excel->getActiveSheet()->setCellValue('A' . $offset, $data['dataTitle']);
			// data title offset plus one empty row
			$offset += 2;
			// Set table header
			$this->excel->getActiveSheet()->fromArray((array)$data['dataHeader'], NULL, 'A' . $offset);
			// dataHeader offset
			$offset++;
			// Set table data
			$this->excel->getActiveSheet()->fromArray((array)$data['data'], NULL, 'A' . $offset);
			// data offset
			$offset += count($data['data']);
			// Extend table for total amounts
			$this->excel->getActiveSheet()->fromArray((array)$data['total'], NULL, 'G' . $offset);
			$this->excel->getActiveSheet()->setCellValue('H' . $offset, '=SUM(G7:G' . ($offset-1) . ')');
			$this->excel->getActiveSheet()->setCellValue('H' . ($offset+1), '=SUM(H7:H' . ($offset-1) . ')');
			// Total amounts offset plus a few empty row
			$offset += 6;
			// Draw three signatures input
			$WS = "                 ";
			$this->excel->getActiveSheet()->setCellValue('A' . $offset,
				"______________________________________" . $WS . $WS .
				"______________________________________" . $WS . $WS .
				"______________________________________"
			);
			$this->excel->getActiveSheet()->setCellValue('A' . ($offset+1),
				"PRESIDENTE" . $WS . $WS . $WS . $WS . $WS . "             " .
				"SECRETARIO" . $WS . $WS . $WS . $WS . $WS . "               " .
				"TESORERO"
			);
			$this->excel->getActiveSheet()->mergeCells('A' . $offset . ':I' . $offset);
			$this->excel->getActiveSheet()->mergeCells('A' . ($offset+1) . ':I' . ($offset+1));
			$this->excel->getActiveSheet()->getStyle('A' . $offset)->getAlignment()
				->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('A' . ($offset+1))->getAlignment()
				->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			// Merge header and dataTitle cells
			$this->excel->getActiveSheet()->mergeCells('A1:I1');
			$this->excel->getActiveSheet()->mergeCells('A2:I2');
			$this->excel->getActiveSheet()->mergeCells('A4:I4');
			$tableBorders = array(
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				),
			);
			$headerStyle = array(
				'fill' => array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'startcolor' => array(
						'rgb' => 'CCCCCC'
					)
				)
			);
			// Table offset
			$tableOffset = 6 + count($data['data']);
			// Add table style
			$this->excel->getActiveSheet()->getStyle('A6:I' . $tableOffset)->applyFromArray($tableBorders);
			// Extend table style for requested & approved total amount
			$this->excel->getActiveSheet()->getStyle('G' . ($tableOffset+1) . ':H' . ($tableOffset+2))
				->applyFromArray($tableBorders);
			// Add table header style
			$this->excel->getActiveSheet()->getStyle('A6:I6')->applyFromArray($headerStyle);
			// Add table data numbers separator
			$this->excel->getActiveSheet()->getStyle('G7:H' . ($tableOffset+2))->getNumberFormat()
				->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
			// Configure columns width
			$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
			$this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
			// (PATCH) Initialize cell selection, otherwise might get a bit crazy
			$this->excel->getActiveSheet()->setSelectedCells('A1');
			// Save our workbook as this file name
			$filename="REPORTE - " . $data['filename'] . ".xls";
			// save it to Excel5 format (excel 2003 .XLS file)
			$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
			// Create the excel
			$objWriter->save(DropPath . $filename);
			// Successful operation
			$result['lpath'] = $filename;
			$result['message'] = "success";
		}
		echo json_encode($result);
	}

	public function download() {
		if ($_SESSION['type'] != 2) {
			$this->load->view('errors/index.html');
		} else {
			header('Content-Disposition: attachment; filename=' . $_GET['docName']);
			// The document source
			readfile(DropPath . $_GET['docName']);
		}
	}

	public function downloadReport() {
		if ($_SESSION['type'] != 2) {
			$this->load->view('errors/index.html');
		} else {
			ignore_user_abort(true);
			header('Content-Type: application/vnd.ms-excel'); //mime type
			header('Content-Disposition: attachment;filename="'.$_GET['lpath'].'"');
			header('Cache-Control: max-age=0');
			readfile(DropPath . $_GET['lpath']);
			// Delete it from server HD now that it's been sent to browser
			unlink(DropPath . $_GET['lpath']);
		}
	}
}
