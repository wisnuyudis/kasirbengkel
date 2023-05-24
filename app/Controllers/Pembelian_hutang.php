<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Controllers;
use App\Models\PembelianHutangModel;
use App\Libraries\JWDPDF;

class Pembelian_hutang extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new PembelianHutangModel;	
		$this->data['title'] = 'Pembelian Hutang';
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/moment/moment.min.js');
		$this->addJs ( $this->config->baseURL . 'public/vendors/daterangepicker/daterangepicker.js');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/daterangepicker/daterangepicker.css');
		$this->addJs ( $this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');
			
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/pembelian-hutang.js');
	}
	
	public function index() 
	{
		if (!empty($_GET['start_date'])) {
			list($y, $m, $d) = explode('-', $_GET['start_date']);
			$start_date = $d . '-' . $m . '-' . $y;
		} else {
			$start_date = date('d-n-Y', strtotime('-1 month'));
		}
		
		if (!empty($_GET['end_date'])) {
			list($y, $m, $d) = explode('-', $_GET['end_date']);
			$end_date = $d . '-' . $m . '-' . $y;
		} else {
			$end_date = date('d-n-Y');
		}
			
		$exp = explode('-', $start_date);
		$start_date_db = $exp[2] . '-' . substr('0' . $exp[1], -2) . '-' . $exp[0];
		
		$exp = explode('-', $end_date);
		$end_date_db = $exp[2] . '-' . substr('0' . $exp[1], -2) . '-' . $exp[0];
		
		$this->data['total_pembelian'] = $this->model->getResumePembelianHutangByDate($start_date_db, $end_date_db, $this->data['setting_hutang']);
		$this->data['start_date'] = $start_date;
		$this->data['end_date'] = $end_date;
		$this->data['start_date_db'] = $start_date_db;
		$this->data['end_date_db'] = $end_date_db;
		
		$jatuh_hutang = "";
		if (!empty($_GET['jatuh_hutang'])) {
			$jatuh_hutang = $_GET['jatuh_hutang']; 
		}
		$this->data['jatuh_hutang'] = $jatuh_hutang;
		
		$this->view('pembelian-hutang.php', $this->data);
	}
	
	public function ajaxGetResumePembelianHutang() {
		$result = $this->model->getResumePembelianHutangByDate($_GET['start_date'], $_GET['end_date'], $this->data['setting_hutang']);
		echo json_encode($result);
	}
	
	public function generateExcel($start_date, $end_date, $output) 
	{
		$start_date = $_GET['start_date'];
		$end_date = $_GET['end_date'];
		
		$filepath = $this->model->writeExcel($start_date, $end_date);
		$filename = 'Pembelian Hutang - ' . format_date($start_date) . '_' . format_date($end_date) . '.xlsx';
		
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
		$pembelian = $this->model->getPembelianHutangByDate($start_date, $end_date);
		if (!$pembelian) {
			$this->errorDataNotFound();
			return false;
		}
		
		$identitas = $this->model->getIdentitas();
		$pdf = new JWDPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
		$pdf->setFooterText('Pembelian hutang periode ' . format_date($start_date) . ' s.d. ' . format_date($end_date));
		
		$pdf->setPageUnit('mm');

		// set document information
		$pdf->SetCreator($identitas['nama']);
		$pdf->SetAuthor($identitas['nama']);
		$pdf->SetTitle('List Pembelian Periode ' . $start_date . ' s.d. ' . $end_date);
		$pdf->SetSubject('Pembelian');
		
		// Margin Header
		$pdf->SetMargins(10, 0, 10);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->startDate = $start_date;
		$pdf->endDate = $end_date;
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
		$pdf->Cell(0, 0, 'Pembelian Hutang', 0, 1, 'C', 0, '', 0, false, 'T', 'M' );
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
					<th style="width:22%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">Nama Supplier</th>
					<th style="width:21%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">No Invoice</th>
					<th style="width:12%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Tgl. Invoice</th>
					<th style="width:12%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Neto</th>
					<th style="width:12%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Dibayar</th>
					<th style="width:12%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Hutang</th>
				</tr>
			</thead>
			<tbody>
		EOD;

			$no = 1;
			$format_number = 'format_number';
			$format_date = 'format_date';
			$total_hutang = $total_dibayar = $total_neto = 0;

			foreach ($pembelian as $val) {
				$datetime = explode(' ', $val['tgl_invoice']);
				$exp = explode('-', $datetime[0]);
				$tgl_invoice = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
				$status = strtoupper($val['status']);
				$hutang = $val['neto'] - $val['total_bayar'];
				$tbl .= <<<EOD
					<tr>
						<td style="width:5%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">$no</td>
						<td style="width:22%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">$val[nama_customer]</td>
						<td style="width:21%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color">$val[no_invoice]</td>
						<td style="width:12%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">$tgl_invoice</td>
						<td style="width:12%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['neto'])}</td>
						<td style="width:12%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['total_bayar'])}</td>
						<td style="width:12%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($hutang)}</td>
					</tr>
					EOD;
				$no++;
				$total_neto += $val['neto'];
				$total_dibayar += $val['total_bayar'];
				$total_hutang += $val['neto'] - $val['total_bayar'];
			}
		
			$tbl .= <<<EOD
			</tbody>
			<tfoot>
				<tr style="background-color:$background_color">
					<td colspan="4" style="width:60%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="left">TOTAL</td>
					<td style="width:12%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($total_neto)}</td>
					<td style="width:12%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($total_dibayar)}</td>
					<td style="width:12%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($total_hutang)}</td>
				</tr>
			</tfoot>
		</table>
		EOD;

		$pdf->writeHTML($tbl, false, false, false, false, '');
		
		$filename = 'Pembelian Hutang - ' . format_date($start_date) . '_' . format_date($end_date) . '.pdf';
		$filepath = ROOTPATH . 'public/tmp/pembelian_' . time() . '.pdf.tmp';
		
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
			$filename = 'Pembelian Hutang - ' . format_date($start_date) . '_' . format_date($end_date) . '.pdf';
		} else {
			$filepath = $this->generateExcel($start_date, $end_date, 'file');
			$filename = 'Pembelian Hutang - ' . format_date($start_date) . '_' . format_date($end_date) . '.xlsx';
		}
		
		$email_config = new \Config\EmailConfig;
		$email_data = array('from_email' => $email_config->from
						, 'from_title' => $email_config->fromTitle
						, 'to_email' => $_GET['email']
						, 'to_name' => ''
						, 'email_subject' => 'Pembelian Hutang Periode ' . format_date($start_date) . ' s.d. ' . format_date($end_date)
						, 'email_content' => '<p>Berikut terlampir data pembelian hutang periode ' . format_date($start_date) . ' s.d. ' . format_date($end_date) . '.<br/><br/><p>Salam</p>'
						, 'attachment' => ['path' => $filepath, 'name' => $filename]
		);
		
		require_once('app/Libraries/SendEmail.php');
		
		$emaillib = new \App\Libraries\SendEmail;
		$emaillib->init();
		$send_email =  $emaillib->send($email_data);

		delete_file($filepath);
		if ($send_email['status'] == 'ok') {
			$message['status'] = 'ok';
			$message['message'] = 'Laporan Pembelian berhasil dikirim ke alamat email: ' . $_GET['email'];
		} else {
			$message['status'] = 'error';
			$message['message'] = 'Laporan Pembelian gagal dikirim ke alamat email: ' . $_GET['email'] . '<br/>Error: ' . $send_email['message'];
		}
		
		echo json_encode($message);
	}
	
	
	// Penjualan
	public function getDataDTPembelianHutang() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllDataPembelianHutang($this->data['setting_hutang']);
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListPembelianHutang($this->data['setting_hutang']);
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		$id_user = $this->session->get('user')['id_user'];
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$val['nama_customer'] = $val['nama_customer'] ?: '-';
			$exp = explode(' ', $val['tgl_penjualan']);
			$val['tgl_penjualan'] = '<div class="text-end">' . format_tanggal($exp[0]) . '</div>';
			$val['sub_total'] = '<div class="text-end">' . format_number($val['sub_total']) . '</div>';
			$val['neto'] = '<div class="text-end">' . format_number($val['neto']) . '</div>';
			$val['total_bayar'] = '<div class="text-end">' . format_number($val['total_bayar']) . '</div>';
			$val['total_diskon_item'] = '<div class="text-end">' . format_number($val['total_diskon_item']) . '</div>';
			$val['kurang_bayar'] = '<div class="text-end">' . format_number($val['kurang_bayar']) . '</div>';
			
			$val['total']['total_neto'] = format_number($val['total']['total_neto']);
			$val['total']['total_qty'] = format_number($val['total']['total_qty']);
			
			$val['ignore_urut'] = $no;
			$val['ignore_action'] = '<div class="btn-action-group">' . 
				btn_link(['url' => base_url() . '/pembelian/edit?id=' . $val['id_penjualan'],'label' => '', 'icon' => 'fas fa-edit', 'attr' => ['target' => '_blank', 'class' => 'btn btn-success btn-xs me-1', 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Edit Data'] ]) . 
				btn_label(['label' => '', 'icon' => 'fas fa-times', 'attr' => ['class' => 'btn btn-danger btn-xs del-pembelian', 'data-id' => $val['id_penjualan'], 'data-delete-message' => 'Hapus data pembelian ?', 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Delete Data'] ]) . 
			'</div>';
			
			$attr_btn_email = ['label' => '', 'icon' => 'fas fa-paper-plane', 'attr' => ['data-url' => base_url() . '/pembelian/invoicePdf?email=Y&id=' . $val['id_penjualan'],'data-id' => $val['id_penjualan'],'class' => 'btn btn-primary btn-xs kirim-email'] ];
			if ($val['email']) {
				$attr_btn_email['attr']['data-bs-toggle'] = 'tooltip';
				$attr_btn_email['attr']['data-bs-title'] = 'Kirim Invoice ke Email';
			} else {
				$attr_btn_email['attr']['disabled'] = 'disabled';
				$attr_btn_email['attr']['class'] = $attr_btn_email['attr']['class'] . ' disabled';
			}
			
			$url_nota = base_url() . '/pembelian/printNota?id=' . $val['id_penjualan'];
			$val['ignore_invoice'] = '<div class="btn-action-group">' 
				. btn_link(['url' => $url_nota,'label' => '', 'icon' => 'fas fa-print', 'attr' => ['data-url' => $url_nota, 'class' => 'btn btn-secondary btn-xs print-nota me-1', 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Print Nota'] ])
				. btn_link(['url' => base_url() . '/pembelian/invoicePdf?id=' . $val['id_penjualan'],'label' => '', 'icon' => 'fas fa-file-pdf', 'attr' => ['data-filename' => 'Invoice-' . $val['no_invoice'], 'target' => '_blank', 'class' => 'btn btn-danger btn-xs save-pdf me-1', 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Download Invoice (PDF)'] ])
				. btn_label( $attr_btn_email ) 
				 . '</div>';
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
}
