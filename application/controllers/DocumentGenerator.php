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

	public function generateSimpleRequestsReport() {
		$result = null;
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
			$this->excel->getActiveSheet()->fromArray((array)$data['total'], NULL, 'G' . $offset);
			$this->excel->getActiveSheet()->setCellValue('H' . $offset, '=SUM(G7:G' . ($offset-1) . ')');
			$this->excel->getActiveSheet()->setCellValue('H' . ($offset+1), '=SUM(H7:H' . ($offset-1) . ')');
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
			$this->excel->getActiveSheet()->setCellValue('B' . $offset, '=COUNTIF(E7:E'. ($offset-6) . ',"Recibida")');
			$this->excel->getActiveSheet()->setCellValue('B' . ($offset+1), '=COUNTIF(E7:E'. ($offset-6) . ',"Aprobada")');
			$this->excel->getActiveSheet()->setCellValue('B' . ($offset+2), '=COUNTIF(E7:E'. ($offset-6) . ',"Rechazada")');
			// Add percentage stat formula
			$this->excel->getActiveSheet()
						->setCellValue('C' . $offset, '=ROUND(B'. $offset .' * 100 / ROWS(E7:E' . ($offset-6) . '), 2)');
			$this->excel->getActiveSheet()
						->setCellValue('C' . ($offset+1), '=ROUND(B'. ($offset+1) .' * 100 / ROWS(E7:E' . ($offset-6) . '), 2)');
			$this->excel->getActiveSheet()
						->setCellValue('C' . ($offset+2), '=ROUND(B'. ($offset+2) .' * 100 / ROWS(E7:E' . ($offset-6) . '), 2)');

			// Merge header and dataTitle cells
			$this->excel->getActiveSheet()->mergeCells('A1:I1');
			$this->excel->getActiveSheet()->mergeCells('A2:I2');
			$this->excel->getActiveSheet()->mergeCells('A4:I4');
			$this->excel->getActiveSheet()->mergeCells('A' . ($offset-3) . ':C' . ($offset-3));
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
			$this->excel->getActiveSheet()->getStyle('A6:I' . $tableOffset)->applyFromArray($tableBorders);
			$this->excel->getActiveSheet()->getStyle('A' . ($offset-1) . ':C' . $statTableOffset)
						->applyFromArray($tableBorders);
			// Extend table style for requested & approved total amount
			$this->excel->getActiveSheet()->getStyle('G' . ($tableOffset+1) . ':H' . ($tableOffset+2))
						->applyFromArray($tableBorders);
			// Add table header style
			$this->excel->getActiveSheet()->getStyle('A6:I6')->applyFromArray($headerStyle);
			$this->excel->getActiveSheet()->getStyle('A' . ($offset-1) . ':C' . ($offset-1))
						->applyFromArray($headerStyle);
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

	public function generateRequestsReport() {
		$result = null;
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
			$this->excel->getActiveSheet()->fromArray((array)$data['total'], NULL, 'H' . $offset);
			$this->excel->getActiveSheet()->setCellValue('I' . $offset, '=SUM(H7:H' . ($offset-1) . ')');
			$this->excel->getActiveSheet()->setCellValue('I' . ($offset+1), '=SUM(I7:I' . ($offset-1) . ')');
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
			$this->excel->getActiveSheet()->setCellValue('B' . $offset, '=COUNTIF(F7:F'. ($offset-6) . ',"Recibida")');
			$this->excel->getActiveSheet()->setCellValue('B' . ($offset+1), '=COUNTIF(F7:F'. ($offset-6) . ',"Aprobada")');
			$this->excel->getActiveSheet()->setCellValue('B' . ($offset+2), '=COUNTIF(F7:F'. ($offset-6) . ',"Rechazada")');
			// Add percentage stat formula
			$this->excel->getActiveSheet()
				->setCellValue('C' . $offset, '=ROUND(B'. $offset .' * 100 / ROWS(F7:F' . ($offset-6) . '), 2)');
			$this->excel->getActiveSheet()
				->setCellValue('C' . ($offset+1), '=ROUND(B'. ($offset+1) .' * 100 / ROWS(F7:F' . ($offset-6) . '), 2)');
			$this->excel->getActiveSheet()
				->setCellValue('C' . ($offset+2), '=ROUND(B'. ($offset+2) .' * 100 / ROWS(F7:F' . ($offset-6) . '), 2)');

			// Merge header and dataTitle cells
			$this->excel->getActiveSheet()->mergeCells('A1:J1');
			$this->excel->getActiveSheet()->mergeCells('A2:J2');
			$this->excel->getActiveSheet()->mergeCells('A4:J4');
			$this->excel->getActiveSheet()->mergeCells('A' . ($offset-3) . ':C' . ($offset-3));
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
			$this->excel->getActiveSheet()->getStyle('A6:J' . $tableOffset)->applyFromArray($tableBorders);
			$this->excel->getActiveSheet()->getStyle('A' . ($offset-1) . ':C' . $statTableOffset)
				->applyFromArray($tableBorders);
			// Extend table style for requested & approved total amount
			$this->excel->getActiveSheet()->getStyle('H' . ($tableOffset+1) . ':I' . ($tableOffset+2))
				->applyFromArray($tableBorders);
			// Add table header style
			$this->excel->getActiveSheet()->getStyle('A6:J6')->applyFromArray($headerStyle);
			$this->excel->getActiveSheet()->getStyle('A' . ($offset-1) . ':C' . ($offset-1))
				->applyFromArray($headerStyle);
			// Add table data numbers separator
			$this->excel->getActiveSheet()->getStyle('H7:I' . ($tableOffset+2))->getNumberFormat()
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
			$this->excel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
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
		$result = null;
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
				$this->excel->getActiveSheet()->fromArray((array)$data['total'], NULL, 'E' . $offset);
				$totalStartCell = 'E';
				$requestedCell = $totalValCell = 'F';
				$lastCell = 'G';
			} else if ($data['status'] == "Rechazada") {
				// Take reunion number into account
				$this->excel->getActiveSheet()->fromArray((array)$data['total'], NULL, 'F' . $offset);
				$totalStartCell = 'F';
				$requestedCell = $totalValCell = 'G';
				$lastCell = 'H';
			} else {
				$this->excel->getActiveSheet()->fromArray((array)$data['total'], NULL, 'G' . $offset);
				$totalStartCell = $requestedCell = 'G';
				$totalValCell = 'H';
				$lastCell = 'I';
			}
			$this->excel->getActiveSheet()
				->setCellValue($totalValCell . $offset, '=SUM(' . $requestedCell . '7:' . $requestedCell . ($offset-1) . ')');
			if ($data['status'] == "Aprobada") {
				$this->excel->getActiveSheet()->setCellValue($totalValCell . ($offset+1), '=SUM(H7:H' . ($offset-1) . ')');
			}
			// Merge header and dataTitle cells
			$this->excel->getActiveSheet()->mergeCells('A1:' . $lastCell .'1');
			$this->excel->getActiveSheet()->mergeCells('A2:' . $lastCell . '2');
			$this->excel->getActiveSheet()->mergeCells('A4:' . $lastCell .'4');
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
			$totalStartCell = $data['status'] == "Rechazada" ? chr(ord($totalStartCell) + 1): $totalStartCell;
			$this->excel->getActiveSheet()->getStyle($totalStartCell . 	'7:' . $totalValCell . ($tableOffset+2))->getNumberFormat()
				->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
			// Configure columns width
			$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
			$this->excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
			$this->excel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
			if ($lastCell >= 'H') {
				$this->excel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
			}
			if ($lastCell >= 'I') {
				$this->excel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
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
		$result = null;
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
			$this->excel->getActiveSheet()->fromArray((array)$data['total'], NULL, 'I' . $offset);
			$this->excel->getActiveSheet()->setCellValue('J' . $offset, '=SUM(I7:I' . ($offset-1) . ')');
			$this->excel->getActiveSheet()->setCellValue('J' . ($offset+1), '=SUM(J7:J' . ($offset-1) . ')');
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
			$this->excel->getActiveSheet()->mergeCells('A' . $offset . ':J' . $offset);
			$this->excel->getActiveSheet()->mergeCells('A' . ($offset+1) . ':J' . ($offset+1));
			$this->excel->getActiveSheet()->getStyle('A' . $offset)->getAlignment()
				->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$this->excel->getActiveSheet()->getStyle('A' . ($offset+1))->getAlignment()
				->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			// Merge header and dataTitle cells
			$this->excel->getActiveSheet()->mergeCells('A1:J1');
			$this->excel->getActiveSheet()->mergeCells('A2:J2');
			$this->excel->getActiveSheet()->mergeCells('A4:J4');
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
			$this->excel->getActiveSheet()->getStyle('A6:J' . $tableOffset)->applyFromArray($tableBorders);
			// Extend table style for requested & approved total amount
			$this->excel->getActiveSheet()->getStyle('I' . ($tableOffset+1) . ':J' . ($tableOffset+2))
				->applyFromArray($tableBorders);
			// Add table header style
			$this->excel->getActiveSheet()->getStyle('A6:J6')->applyFromArray($headerStyle);
			// Add table data numbers separator
			$this->excel->getActiveSheet()->getStyle('I7:J' . ($tableOffset+2))->getNumberFormat()
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
			$this->excel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
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
