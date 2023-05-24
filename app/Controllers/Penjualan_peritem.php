<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Controllers;
use App\Models\PenjualanPeritemModel;
use App\Libraries\JWDPDF;

class Penjualan_peritem extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new PenjualanPeritemModel;	
		$this->data['title'] = 'Penjualan Per Item';
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/moment/moment.min.js');
		$this->addJs ( $this->config->baseURL . 'public/vendors/daterangepicker/daterangepicker.js');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/daterangepicker/daterangepicker.css');
		$this->addJs ( $this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');
			
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/penjualan-peritem.js');
	}
	
	public function index() {
		$start_date = date('d-n-Y', strtotime('-1 month'));
		$end_date = date('d-n-Y');
		
		$exp = explode('-', $start_date);
		$start_date_db = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		
		$exp = explode('-', $end_date);
		$end_date_db = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		
		$this->data['total_penjualan'] = $this->model->getResumePenjualanByDate($start_date_db, $end_date_db);
		$this->data['start_date'] = $start_date;
		$this->data['end_date'] = $end_date;
		$this->data['start_date_db'] = $start_date_db;
		$this->data['end_date_db'] = $end_date_db;
		$this->view('penjualan-peritem-form.php', $this->data);
	}
	
	public function ajaxGetResumePenjualan() {
		$result = $this->model->getResumePenjualanByDate($_GET['start_date'], $_GET['end_date']);
		echo json_encode($result);
	}
	
	public function generateExcel($start_date, $end_date, $output) 
	{
		$start_date = $_GET['start_date'];
		$end_date = $_GET['end_date'];
		
		$filepath = $this->model->writeExcel($start_date, $end_date);
		$filename = 'Penjualan Barang - ' . format_date($start_date) . '_' . format_date($end_date) . '.xlsx';
		
		switch ($output) {
			case 'raw':
				$content = file_get_contents($filepath);
				echo $content;
				delete_file($filepath);
				break;
			case 'file':
				return $filepath;
				break;
			default:
				header('Content-disposition: attachment; filename="'. $filename .'"');
				header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
				header('Content-Transfer-Encoding: binary');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');  
				$content = file_get_contents($filepath);
				delete_file($filepath);
				echo $content;
		}
		exit;
	}
	
	public function ajaxExportExcel() 
	{
		$output = '';
		if (@$_GET['ajax'] == 'true') {
			$output = 'raw';
		}
		$this->generateExcel($_GET['start_date'], $_GET['end_date'], $output); 
	}
		
	public function generatePdf($start_date, $end_date, $output) 
	{
		$penjualan_barang = $this->model->getPenjualanBarangByDate($start_date, $end_date);
		if (!$penjualan_barang) {
			$this->errorDataNotFound();
			return false;
		}
		
		$identitas = $this->model->getIdentitas();
		$pdf = new JWDPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
		$pdf->setFooterText('Penjualan Barang Periode ' . format_date($start_date) . ' s.d. ' . format_date($end_date));

		$pdf->setPageUnit('mm');

		// set document information
		$pdf->SetCreator($identitas['nama']);
		$pdf->SetAuthor($identitas['nama']);
		$pdf->SetTitle('List Penjualan Barang Periode ' . $start_date . ' s.d. ' . $end_date);
		$pdf->SetSubject('Penjualan Barang');
		
		// Margin Header
		$pdf->SetMargins(10, 0, 10);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetPrintHeader(true);
		$pdf->SetPrintFooter(true);
		
		$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
		$pdf->SetProtection(array('modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high'), '', null, 0, null);

		// set default font subsetting mode
		$pdf->setFontSubsetting(true);

		$margin_left = 10; //mm
		$margin_right = 10; //mm
		$margin_top = 30; //mm
		$font_size = 10;
		
		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf->SetFont('dejavusans', '', $font_size + 4, '', true);
		// Margin Content
		$pdf->SetMargins($margin_left, $margin_top, $margin_right, false);

		$pdf->AddPage();
		
		// $pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 0, 0)));
		$pdf->SetTextColor(50,50,50);
		$pdf->SetFont ('helvetica', 'B', $font_size + 4, '', 'default', true );
		$pdf->Cell(0, 0, 'Barang Terjual', 0, 1, 'C', 0, '', 0, false, 'T', 'M' );
		$pdf->SetFont ('helvetica', 'B', $font_size + 2, '', 'default', true );
		$pdf->Cell(0, 0, 'Periode: ' . format_date($start_date) . ' s.d. ' . format_date($end_date), 0, 1, 'C', 0, '', 0, false, 'T', 'M' );
		
		$pdf->SetFont ('helvetica', '', $font_size, '', 'default', true );

		$pdf->ln(8);
		$pdf->SetFont ('helvetica', '', $font_size, '', 'default', true );
		$border_color = '#CECECE';
		$background_color = '#efeff0';
		$tbl = <<<EOD
		<table border="0" cellspacing="0" cellpadding="6">
			<thead>
				<tr border="1" style="background-color:$background_color">
					<th style="width:5%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">No</th>
					<th style="width:30%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">Nama Barang</th>
					<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Harga Satuan</th>
					<th style="width:8%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Qty</th>
					<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Neto</th>
					<th style="width:12%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Untung</th>
					<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Tgl. Penjualan</th>
				</tr>
			</thead>
			<tbody>
		EOD;

			$no = 1;
			$format_number = 'format_number';
			$format_date = 'format_date';
			$total_qty = $total_diskon = $total_neto = $total_untung_rugi = 0;

			foreach ($penjualan_barang as $val) {
				$datetime = explode(' ', $val['tgl_penjualan']);
				$exp = explode('-', $datetime[0]);
				$tgl_penjualan = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
				$tbl .= <<<EOD
					<tr>
						<td style="width:5%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">$no</td>
						<td style="width:30%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">$val[nama_barang]</td>
						<td style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['harga_satuan'])}</td>
						<td style="width:8%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['qty'])}</td>
						<td style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['harga_neto'])}</td>
						<td style="width:12%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['untung_rugi'])}</td>
						<td style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">$tgl_penjualan</td>
					</tr>
					EOD;
				$no++;
				$total_qty += $val['qty'];
				$total_untung_rugi += $val['untung_rugi'];
				$total_neto += $val['harga_neto'];
			}
		
			$tbl .= <<<EOD
			</tbody>
			<tfoot>
				<tr style="background-color:$background_color">
					<td style="width:50%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="left">TOTAL</td>
					<td style="width:8%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($total_qty)}</td>
					<td style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($total_neto)}</td>
					<td style="width:12%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($total_untung_rugi)}</td>
					<td style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right"></td>
				</tr>
			</tfoot>
		</table>
		EOD;

		$pdf->writeHTML($tbl, false, false, false, false, '');
		
		$filename = 'Penjualan Barang - ' . format_date($start_date) . '_' . format_date($end_date) . '.pdf';
		$filepath = ROOTPATH . 'public/tmp/penjualan_barang_' . time() . '.pdf.tmp';
		
		switch ($output) {
			case 'raw':
				$pdf->Output($filepath, 'F');
				$content = file_get_contents($filepath);
				echo $content;
				delete_file($filepath);
				break;
			case 'file':
				$pdf->Output($filepath, 'F');
				return $filepath;
				break;
			default:
				$pdf->Output($filename, 'D');
				
		}
		exit;
	}
	
	public function ajaxExportPdf() 
	{
		$output = '';
		if (@$_GET['ajax'] == 'true') {
			$output = 'raw';
		}
		$this->generatePdf($_GET['start_date'], $_GET['end_date'], $output); 
	}
	
	public function ajaxSendEmail() {
		
		$start_date = $_GET['start_date'];
		$end_date = $_GET['end_date'];
		
		if ($_GET['file_format'] == 'pdf') {
			$filepath = $this->generatePdf($start_date, $end_date, 'file');
			$filename = 'Penjualan Barang - ' . format_date($start_date) . '_' . format_date($end_date) . '.pdf';
		} else {
			$filepath = $this->generateExcel($start_date, $end_date, 'file');
			$filename = 'Penjualan Barang - ' . format_date($start_date) . '_' . format_date($end_date) . '.xlsx';
		}
		
		$email_config = new \Config\EmailConfig;
		$email_data = array('from_email' => $email_config->from
						, 'from_title' => $email_config->fromTitle
						, 'to_email' => $_GET['email']
						, 'to_name' => ''
						, 'email_subject' => 'Penjualan Barang ' . format_date($start_date) . ' s.d. ' . format_date($end_date)
						, 'email_content' => '<p>Berikut terlampir penjualan barang periode ' . format_date($start_date) . ' s.d. ' . format_date($end_date) . '.<br/><br/><p>Salam</p>'
						, 'attachment' => ['path' => $filepath, 'name' => $filename]
		);
		
		require_once('app/Libraries/SendEmail.php');
		
		$emaillib = new \App\Libraries\SendEmail;
		$emaillib->init();
		$send_email =  $emaillib->send($email_data);

		delete_file($filepath);
		if ($send_email['status'] == 'ok') {
			$message['status'] = 'ok';
			$message['message'] = 'Nota Retur berhasil dikirim ke alamat email: ' . $_GET['email'];
		} else {
			$message['status'] = 'error';
			$message['message'] = 'Nota Retur gagal dikirim ke alamat email: ' . $_GET['email'] . '<br/>Error: ' . $send_email['message'];
		}
		
		echo json_encode($message);
	}
		
	// Penjualan
	public function getDataDTPenjualan() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllDataPenjualan();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListPenjualan();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		$id_user = $this->session->get('user')['id_user'];
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$exp = explode(' ', $val['tgl_penjualan']);
			$val['tgl_penjualan'] = '<div class="text-end">' . format_tanggal($exp[0]) . '</div>';
			$val['harga_satuan'] = '<div class="text-end">' . format_number($val['harga_satuan']) . '</div>';
			$val['qty'] = '<div class="text-end">' . format_number($val['qty']) . '</div>';
			$val['diskon'] = '<div class="text-end">' . format_number($val['diskon']) . '</div>';
			$val['harga_neto'] = '<div class="text-end">' . format_number($val['harga_neto']) . '</div>';
			$val['untung_rugi'] = '<div class="text-end">' . format_number($val['untung_rugi']) . '</div>';
			$val['total']['total_neto'] = format_number($val['total']['total_neto']);
			$val['total']['total_qty'] = format_number($val['total']['total_qty']);
			$val['ignore_urut'] = $no;
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
}
