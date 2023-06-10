<?php

/**
 *	App Name	: Aplikasi Kasir Berbasis Web	
 *	Developed by: Agus Prawoto Hadi
 *	Website		: https://jagowebdev.com
 *	Year		: 2022
 */

namespace App\Controllers;

use App\Models\PenjualanModel;
use App\Models\WilayahModel;

class Penjualan extends \App\Controllers\BaseController
{
	public function __construct()
	{

		parent::__construct();

		$this->model = new PenjualanModel;
		$this->data['site_title'] = 'Penjualan';

		$this->addJs($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.min.css');

		$this->addJs($this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal-loader.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal-fapicker.css');

		$this->addJs($this->config->baseURL . 'public/themes/modern/js/penjualan.js');
		$this->addStyle($this->config->baseURL . 'public/themes/modern/css/modal-pilih-barang.css');

		$this->addJs($this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css');
		$this->addJs($this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');

		$this->addJs($this->config->baseURL . 'public/themes/modern/js/wilayah.js');
	}

	public function index()
	{
		$this->hasPermissionPrefix('read');

		$data = $this->data;
		if (!empty($_POST['delete'])) {
			$this->hasPermissionPrefix('delete', 'penjualan');

			$result = $this->model->deleteData();
			// $result = true;
			if ($result) {
				$data['msg'] = ['status' => 'ok', 'message' => 'Data penjualan berhasil dihapus'];
			} else {
				$data['msg'] = ['status' => 'error', 'message' => 'Data penjualan gagal dihapus'];
			}
		}
		$this->view('penjualan-result.php', $data);
	}

	public function ajaxGetBarangByBarcode()
	{
		$data = $this->model->getBarangByBarcode($_GET['code'], $_GET['id_gudang'], $_GET['id_jenis_harga']);
		if ($data) {
			$result = ['status' => 'ok', 'data' => $data];
		} else {
			$result = ['status' => 'error', 'message' => 'Data tidak ditemukan'];
		}

		echo json_encode($result);
	}

	public function add()
	{
		$this->data['title'] = 'Tambah Data Penjualan';
		$this->data['breadcrumb']['Add'] = '';
		$this->data = array_merge($this->data, $this->setData());
		$this->view('penjualan-form.php', $this->data);
	}

	public function ajaxSaveData()
	{
		$result = $this->model->saveData();
		echo json_encode($result);
	}

	public function ajaxDeleteData()
	{
		$delete = $this->model->deleteData($_POST['id']);
		// $delete = true;
		if ($delete) {
			$result =  ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
		} else {
			$result = ['status' => 'error', 'message' => 'Data gagal dihapus'];
		}

		echo json_encode($result);
	}

	private function setData()
	{
		$result = $this->model->getAllGudang();
		foreach ($result as $val) {
			$gudang[$val['id_gudang']] = $val['nama_gudang'];
		}

		$result = $this->model->getJenisHarga();
		$jenis_harga_selected = '';
		foreach ($result as $val) {
			$jenis_harga[$val['id_jenis_harga']] = $val['nama_jenis_harga'];
			if ($val['default_harga'] == 'Y') {
				$jenis_harga_selected = $val['id_jenis_harga'];
			}
		}

		$pajak = $this->getSetting('pajak');

		return ['gudang' => $gudang, 'pajak' => $pajak, 'jenis_harga' => $jenis_harga, 'jenis_harga_selected' => $jenis_harga_selected];
	}


	public function detailData()
	{
		$init_data = $this->setData();

		$id = $_GET['id'];
		$init_data['penjualan'] = $this->model->getPenjualanById($id);
		if ($init_data['penjualan']) {
			$init_data['jenis_harga_selected'] = $init_data['penjualan']['id_jenis_harga'];
			$init_data['barang'] = $this->model->getPenjualanBarangByIdPenjualan($id);
			$init_data['pembayaran'] = $this->model->getPembayaranByIdPenjualan($id);
		}

		return $init_data;
	}

	// For mobile
	public function detail()
	{
		$detail_data = $this->detailData();
		$this->data = array_merge($this->data, $detail_data);
		if (@$_GET['mobile'] == 'true') {
			echo view('themes/modern/penjualan-mobile-detail.php', $this->data);
		}
	}
	//-- For mobile

	public function edit()
	{
		$this->hasPermission('update_all', 'penjualan');

		$this->data['title'] = 'Edit Penjualan';
		$detail_data = $this->detailData();
		$this->data = array_merge($this->data, $detail_data);

		if (empty($_GET['id'])) {
			$this->errorDataNotFound();
		}
		$this->data['breadcrumb']['Edit'] = '';

		if (@$_GET['mobile'] == 'true') {
			echo view('themes/modern/penjualan-form-mobile.php', $this->data);
		} else {
			$this->view('penjualan-form.php', $this->data);
		}
	}

	public function invoicePdf()
	{
		require_once('app/ThirdParty/Tcpdf/tcpdf.php');
		require_once('app/Helpers/util_helper.php');

		$order = $this->model->getPenjualanDetail($_GET['id']);
		if (!$order) {
			$this->errorDataNotFound();
			return false;
		}

		$identitas = $this->model->getIdentitas();
		$setting = $this->getSetting('invoice');

		$pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);

		$pdf->setPageUnit('mm');

		// set document information
		$pdf->SetCreator($identitas['nama']);
		$pdf->SetAuthor($identitas['nama']);
		$pdf->SetTitle('Invoice #' . $order['order']['no_invoice']);
		$pdf->SetSubject('Invoice Penjualan');

		$margin_left = 10; //mm
		$margin_right = 10; //mm
		$margin_top = 15; //mm
		$font_size = 10;

		$pdf->SetAutoPageBreak(FALSE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetPrintHeader(false);
		$pdf->SetPrintFooter(false);

		$pdf->SetProtection(array('modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high'), '', null, 0, null);

		// set default font subsetting mode
		$pdf->setFontSubsetting(true);

		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf->SetFont('dejavusans', '', $font_size + 4, '', true);
		$pdf->SetMargins($margin_left, $margin_top, $margin_right, false);

		$pdf->AddPage();

		$pdf->StartTransform();
		$pdf->SetXY(170, 7);
		// $pdf->Rotate(-45);
		// $pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(42, 168, 41)));

		if (empty($order['bayar'])) {
			$pdf->SetFillColor(242, 119, 119);
			$pdf->SetTextColor(170, 56, 56);

			//Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')
			$pdf->Cell(40, 10, 'UNPAID', 0, 1, 'C', 1);
		} else {
			$pdf->SetFillColor(92, 232, 92);
			$pdf->SetTextColor(42, 168, 41);

			//Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')
			$pdf->Cell(40, 10, 'LUNAS', 0, 1, 'C', 1);
		}
		$pdf->StopTransform();

		// $pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 0, 0)));
		// Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)

		$pdf->SetTextColor(50, 50, 50);
		$pdf->Image(ROOTPATH . 'public/images/' . $setting['logo'], 10, 20, 0, 0, 'JPG', 'https://jagowebdev.com');

		$image_dim = getimagesize(ROOTPATH . 'public/images/' . $setting['logo']);
		$x = $margin_left + ($image_dim[0] * 0.2645833333) + 5;
		$pdf->SetXY($x, $margin_top + 3);
		$pdf->Cell(0, 9, $identitas['nama'], 0, 1, 'L', 0, '', 0, false, 'T', 'M');

		//Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')
		$pdf->SetX($x);
		$pdf->SetFont('helvetica', '', $font_size, '', 'default', true);
		$pdf->Cell(0, 0, $identitas['alamat'], 0, 1, 'L', 0, '', 0, false, 'T', 'M');
		$pdf->SetX($x);
		$pdf->Cell(0, 0, $identitas['nama_kelurahan'] . ', ' . $identitas['nama_kecamatan'], 0, 1, 'L', 0, '', 0, false, 'T', 'M');
		$pdf->SetX($x);
		$pdf->Cell(0, 0, $identitas['nama_kabupaten'] . ', ' . $identitas['nama_propinsi'], 0, 1, 'L', 0, '', 0, false, 'T', 'M');

		$barcode_style = array(
			'position' => 'R',
			'align' => 'C',
			'stretch' => false,
			'fitwidth' => true,
			'cellfitalign' => '',
			'border' => false,
			'hpadding' => 'auto',
			'vpadding' => 'auto',
			'fgcolor' => array(0, 0, 0),
			'bgcolor' => false, //array(255,255,255),
			'text' => true,
			'font' => 'helvetica',
			'fontsize' => $font_size,
			'stretchtext' => false
		);

		$pdf->SetY($margin_top + 10);
		$pdf->write1DBarcode($order['order']['no_invoice'], 'C128', '', '', '', 20, 0.4, $barcode_style, 'N');

		$pdf->ln(8);
		$pdf->SetFont('helvetica', 'B', $font_size + 10, '', 'default', true);
		$pdf->Cell(0, 0, 'INVOICE', 0, 1, 'C', 0, '', 0, false, 'T', 'M');

		$pdf->ln(8);
		$pdf->SetFont('helvetica', 'B', $font_size, '', '', true);
		$pdf->Cell(0, 0, 'Pembeli ', 0, 1);
		$pdf->ln(4);

		$pdf->SetFont('helvetica', '', $font_size, '', 'default', true);

		$y =  $pdf->GetY();
		$pdf->Cell(10, 0, 'Nama', 0, 1);
		$pdf->SetXY($margin_left + 13, $y);
		$pdf->Cell(10, 0, ':', 0, 1);
		$pdf->SetXY($margin_left + 15, $y);
		$pdf->Cell(10, 0, $order['customer']['nama_customer'], 0, 1);

		$y =  $pdf->GetY();
		$pdf->Cell(10, 0, 'Alamat', 0, 1);
		$pdf->SetXY($margin_left + 13, $y);
		$pdf->Cell(0, 0, ':', 0, 1);
		$pdf->SetXY($margin_left + 15, $y);
		$pdf->Cell(0, 0, $order['customer']['alamat_customer'], 0, 1);

		if (!empty($order['customer']['nama_kecamatan'])) {
			$pdf->SetX($margin_left + 15);
			$pdf->Cell(0, 0, 'Kec. ' . $order['customer']['nama_kecamatan'] . ', Kab. ' . $order['customer']['nama_kabupaten'], 0, 1);
			$pdf->SetX($margin_left + 15);
			$pdf->Cell(0, 0, $order['customer']['nama_propinsi'], 0, 1);
		}

		$pdf->ln(5);
		$pdf->SetFont('helvetica', 'B', $font_size, '', '', true);
		$y =  $pdf->GetY();
		$pdf->Cell(0, 0, 'Transaksi', 0, 1);
		$pdf->SetFont('helvetica', '', $font_size, '', '', true);
		$pdf->SetY($y);

		$pdf->Cell(0, 0, format_date($order['order']['tgl_penjualan']), 0, 1, 'R', 0, '', 0, false, 'T', 'M');

		$pdf->ln(5);
		$pdf->SetFont('helvetica', '', $font_size, '', 'default', true);
		$border_color = '#CECECE';
		$background_color = '#efeff0';
		$tbl = <<<EOD
		<table border="0" cellspacing="0" cellpadding="6">
			<thead>
				<tr border="1" style="background-color:$background_color">
					<th style="width:5%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">No</th>
					<th style="width:35%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">Deskripsi</th>
					<th style="width:10%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Kuantitas</th>
					<th style="width:10%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Harga Satuan</th>
					<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Harga Total</th>
					<th style="width:10%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Diskon</th>
					<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Total</th>
				</tr>
			</thead>
			<tbody>
		EOD;

		$no = 1;
		$format_number = 'format_number';
		foreach ($order['detail'] as $val) {
			$tbl .= <<<EOD
					<tr>
						<td style="width:5%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">$no</td>
						<td style="width:35%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">$val[nama_barang]</td>
						<th style="width:10%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['qty'])}</th>
						<th style="width:10%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['harga_satuan'])}</th>
						<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['harga_total'])}</th>
						<th style="width:10%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['diskon'])}</th>
						<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['harga_neto'])}</th>
					</tr>

		EOD;
			$no++;
		}

		$diskon = 0;
		if ($order['order']['diskon_nilai']) {
			if ($order['order']['diskon_jenis'] == '%') {
				$diskon = $order['order']['diskon_nilai'] . '%';
			} else {
				$diskon = format_number($order['order']['diskon_nilai']);
			}
		}

		$total = format_number($order['order']['neto']);
		$penyesuaian = format_number($order['order']['penyesuaian']);
		$sub_total = format_number($order['order']['sub_total']);
		if ($order['order']['status'] == 'lunas') {
			$status = 'Kembali';
			$kurang_bayar = $order['order']['kembali'];
		} else {
			$status = 'Kurang';
			$kurang_bayar = $order['order']['kurang_bayar'];
		}

		$tbl .= <<<EOD
				<tr style="background-color:$background_color">
					<td colspan="6" style="width:75%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">Subtotal</td>
					<td style="width:25%;border-bottom-color:$border_color;border-right-color:$border_color" align="right">$sub_total</td>
				</tr>
				<tr style="background-color:$background_color">
					<td colspan="6" style="width:75%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">Diskon</td>
					<td style="width:25%;border-bottom-color:$border_color;border-right-color:$border_color" align="right">$diskon</td>
				</tr>
				<tr style="background-color:$background_color">
					<td colspan="6" style="width:75%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">Penyesuaian</td>
					<td style="width:25%;border-bottom-color:$border_color;border-right-color:$border_color" align="right">$penyesuaian</td>
				</tr>
		EOD;

		if ($order['order']['pajak_display_text']) {

			$tbl .= <<<EOD
					<tr style="background-color:$background_color">
						<td colspan="6" style="width:75%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">{$order['order']['pajak_display_text']}</td>
						<td style="width:25%;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($order['order']['pajak_persen'])}%</td>
					</tr>
			EOD;
		}

		$tbl .= <<<EOD
				<tr style="background-color:$background_color">
					<td colspan="6" style="width:75%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">Total</td>
					<td style="width:25%;border-bottom-color:$border_color;border-right-color:$border_color" align="right">$total</td>
				</tr>
				<tr style="background-color:$background_color">
					<td colspan="6" style="width:75%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">Total Bayar</td>
					<td style="width:25%;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($order['order']['total_bayar'])}</td>
				</tr>
				<tr style="background-color:$background_color">
					<td colspan="6" style="width:75%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">$status</td>
					<td style="width:25%;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($kurang_bayar)}</td>
				</tr>
			</tbody>
		</table>
		EOD;

		$pdf->writeHTML($tbl, false, false, false, false, '');
		$pdf->ln(5);

		$pdf->SetFont('helvetica', 'B', $font_size, '', '', true);
		$pdf->Cell(0, 0, 'Pembayaran', 0, 1);

		$pdf->ln(5);
		$pdf->SetFont('helvetica', '', $font_size, '', '', true);

		if (empty($order['bayar'])) {
			$pdf->Cell(0, 0, 'Tidak ada pembayaran', 0, 1, 'L');
		} else {
			$tbl = <<<EOD
			<table border="0" cellspacing="0" cellpadding="6">
				<thead>
					<tr border="1" style="background-color:$background_color">
						<th style="width:75%;border-left-color:$border_color;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right" align="center">Tanggal Pembayaran</th>
						<th style="width:25%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right" align="center">Nominal</th>
					</tr>
				</thead>
				<tbody>
		EOD;

			foreach ($order['bayar'] as $val) {

				$tgl_bayar = format_date($val['tgl_bayar']);
				$jml_bayar = format_number($val['jml_bayar']);
				$tbl .= <<<EOD
				<tr>
					<td style="width:75%;border-left-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color">$tgl_bayar</td>
					<td style="width:25%;border-left-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">$jml_bayar</td>
				</tr>
		EOD;
			}

			$tbl .= <<<EOD
				<tr>
					<td style="width:75%;border-left-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color">TOTAL</td>
					<td style="width:25%;border-left-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($order['order']['total_bayar'])}</td>
				</tr>
			</tbody>
			</table>
		EOD;

			$pdf->writeHTML($tbl, false, false, false, false, '');
		}

		$pdf->SetY(-20);
		// $pdf->writeHTML('<hr style="background-color:#FFFFFF; border-bottom-color:#CCCCCC;height:0"/>', false, false, false, false, '');
		$pdf->writeHTML('<div style="background-color:#FFFFFF; border-bottom-color:#ababab;height:0"></div>', false, false, false, false, '');

		$pdf->ln(2);

		$pdf->SetFont('helvetica', 'I', $font_size, '', '', true);
		$pdf->SetTextColor(50, 50, 50);
		$pdf->SetTextColor(100, 100, 100);
		$pdf->Cell(0, 0, $setting['footer_text'], 0, 1, 'L');

		$filename = 'Invoice-' . str_replace(['/', '\\'], '_', $order['order']['no_invoice']) . '.pdf';
		$filepath_invoice = ROOTPATH . 'public/tmp/' . $filename;

		if (!empty($_GET['email'])) {
			$filepath = ROOTPATH . 'public/tmp/invoice_' . time() . '.pdf';
			$pdf->Output($filepath, 'F');

			if (@$_GET['email']) {
				$email = $_GET['email'];
			} else {
				$email = $order['customer']['email'];
			}
			$email_config = new \Config\EmailConfig;
			$email_data = array(
				'from_email' => $email_config->from, 'from_title' => 'Jagowebdev.com', 'to_email' => $email, 'to_name' => $order['customer']['nama_customer'], 'email_subject' => 'Invoice: ' . $order['order']['no_invoice'], 'email_content' => '<h2>Hi, ' . $order['customer']['nama_customer'] . '</h2><p>Berikut terlampir invoice pembelian atas nama ' . $order['customer']['nama_customer'] . '.</p><p>Anda dapat mengunduhnya pada bagian Attachment.<br/><br/><p>Salam</p>', 'attachment' => ['path' => $filepath, 'name' => $filename]
			);

			require_once('app/Libraries/SendEmail.php');

			$emaillib = new \App\Libraries\SendEmail;
			$emaillib->init();
			$send_email =  $emaillib->send($email_data);

			unlink($filepath);
			if ($send_email['status'] == 'ok') {
				$message['status'] = 'ok';
				$message['message'] = 'Invoice berhasil dikirim ke alamat email: ' . $email;
			} else {
				$message['status'] = 'error';
				$message['message'] = 'Invoice gagal dikirim ke alamat email: ' . $email . '<br/>Error: ' . $send_email['message'];
			}

			echo json_encode($message);
			exit();
		}

		if (@$_GET['ajax'] == 'true') {
			$pdf->Output($filepath_invoice, 'F');
			$content = file_get_contents($filepath_invoice);
			echo $content;
			delete_file($filepath_invoice);
		} else {
			$pdf->Output($filename, 'D');
		}
		exit;
	}

	// Penjualan
	public function getDataDTPenjualan()
	{

		$this->hasPermissionPrefix('read');

		$num_data = $this->model->countAllDataPenjualan();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;

		$query = $this->model->getListDataPenjualan();
		$result['recordsFiltered'] = $query['total_filtered'];

		helper('html');
		$id_user = $this->session->get('user')['id_user'];

		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) {
			$val['nama_customer'] = $val['nama_customer'] ?: '-';
			$exp = explode(' ', $val['tgl_penjualan']);
			$val['tgl_penjualan'] = '<div class="text-end">' . format_tanggal($exp[0]) . '</div>';
			$val['sub_total'] = '<div class="text-end">' . format_number($val['sub_total']) . '</div>';
			$val['neto'] = '<div class="text-end">' . format_number($val['neto']) . '</div>';
			$val['untung_rugi'] = '<div class="text-end">' . format_number($val['untung_rugi']) . '</div>';
			$val['total_diskon_item'] = '<div class="text-end">' . format_number($val['total_diskon_item']) . '</div>';

			if ($val['kurang_bayar'] < 0) {
				$val['kurang_bayar'] = 0;
			}
			$val['kurang_bayar'] = '<div class="text-end">' . format_number($val['kurang_bayar']) . '</div>';

			if ($val['status'] == 'kurang_bayar') {
				$val['status'] = 'kurang';
			}
			$val['status'] = ucfirst($val['status']);

			$val['ignore_urut'] = $no;
			$val['ignore_action'] = '<div class="btn-action-group">' .
				btn_link(['url' => base_url() . '/penjualan/edit?id=' . $val['id_penjualan'], 'label' => '', 'icon' => 'fas fa-edit', 'attr' => ['target' => '_blank', 'class' => 'btn btn-success btn-xs me-1', 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Edit Data']]) .
				btn_label(['label' => '', 'icon' => 'fas fa-times', 'attr' => ['class' => 'btn btn-danger btn-xs del-penjualan', 'data-id' => $val['id_penjualan'], 'data-delete-message' => 'Hapus data penjualan ?', 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Delete Data']]) .
				'</div>';

			$attr_btn_email = ['label' => '', 'icon' => 'fas fa-paper-plane', 'attr' => ['data-url' => base_url() . '/penjualan/invoicePdf?email=Y&id=' . $val['id_penjualan'], 'data-id' => $val['id_penjualan'], 'class' => 'btn btn-primary btn-xs kirim-email']];
			if ($val['email']) {
				$attr_btn_email['attr']['data-bs-toggle'] = 'tooltip';
				$attr_btn_email['attr']['data-bs-title'] = 'Kirim Invoice ke Email';
			} else {
				$attr_btn_email['attr']['disabled'] = 'disabled';
				$attr_btn_email['attr']['class'] = $attr_btn_email['attr']['class'] . ' disabled';
			}

			$url_nota = base_url() . '/penjualan/printNota?id=' . $val['id_penjualan'];
			$val['ignore_invoice'] = '<div class="btn-action-group">'
				. btn_link(['url' => $url_nota, 'label' => '', 'icon' => 'fas fa-print', 'attr' => ['data-url' => $url_nota, 'class' => 'btn btn-secondary btn-xs print-nota me-1', 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Print Nota']])
				. btn_link(['url' => base_url() . '/penjualan/invoicePdf?id=' . $val['id_penjualan'], 'label' => '', 'icon' => 'fas fa-file-pdf', 'attr' => ['data-filename' => 'Invoice-' . $val['no_invoice'], 'target' => '_blank', 'class' => 'btn btn-danger btn-xs save-pdf me-1', 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Download Invoice (PDF)']])
				. btn_label($attr_btn_email)
				. '</div>';
			$no++;
		}

		$result['data'] = $query['data'];
		echo json_encode($result);
		exit();
	}

	public function printNota()
	{
		$this->data['identitas'] = $this->model->getIdentitas();

		/* public function getSetting() {
			$sql = 'SELECT * FROM setting WHERE type = ?';
			$data = $this->db->query($sql, 'invoice')->getResultArray();
			$result = [];
			
			
			foreach ($data as $val) { 
				$result[$val['param']] = $val['value'];
			}
			
			$sql = 'SELECT * FROM setting WHERE type = ? AND param = ?';
			$data = $this->db->query($sql, ['invoice', 'logo'])->getRowArray();
			$result['logo'] = $data['value'];
			
			$sql = 'SELECT * FROM setting WHERE type = ? AND param = ?';
			$data = $this->db->query($sql, ['invoice', 'footer_text'])->getRowArray();
			$result['footer_text'] = $data['value'];
			
			return $result;
		} */

		$setting = $this->getSetting('invoice');


		$this->data['setting'] = $setting;
		$this->data['penjualan'] = $this->model->getPenjualanById($_GET['id']);
		$this->data['barang'] = $this->model->getPenjualanBarangByIdPenjualan($_GET['id']);
		$this->data['pembayaran'] = $this->model->getPembayaranByIdPenjualan($_GET['id']);
		$this->data['petugas'] = $this->model->getUserById($this->data['penjualan']['id_user_input']);
		$this->data['data'] = 'Data penjualan';
		echo view('themes/modern/penjualan-print-nota.php', $this->data);
	}

	public function getDataDTListBarang()
	{
		echo view('themes/modern/penjualan-list-barang.php', $this->data);
	}

	public function getListCustomer()
	{
		echo view('themes/modern/penjualan-list-customer.php', $this->data);
	}

	public function getDataDTCustomer()
	{
		$this->hasPermissionPrefix('read');

		$num_data = $this->model->countAllDataCustomer();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;

		$query = $this->model->getListDataCustomer();
		$result['recordsFiltered'] = $query['total_filtered'];

		helper('html');
		$id_user = $this->session->get('user')['id_user'];

		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) {
			$detail_customer = json_encode($val);
			$val['ignore_urut'] = $no;
			$val['alamat_customer'] = $val['alamat_customer'] . ' ' . $val['nama_kabupaten'];
			$val['no_telp'] = '<div class="text-nowrap">' . $val['no_telp'] . '</div>';
			$val['jenisharga'] = $val['nama_jenis_harga'];
			// Pilih Customer
			$attr_btn = ['data-id-customer' => $val['id_customer'], 'class' => 'btn btn-success pilih-customer btn-xs'];
			$val['ignore_pilih'] = btn_label(['label' => 'Pilih', 'attr' => $attr_btn]) . '<span style="display:none">' . $detail_customer . '</span>';
			$no++;
		}

		$result['data'] = $query['data'];
		echo json_encode($result);
		exit();
	}

	public function getDataDTBarang()
	{

		$this->hasPermissionPrefix('read');

		$num_data = $this->model->countAllDataBarang();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;

		$query = $this->model->getListDataBarang($_GET['id_gudang'], $_GET['id_jenis_harga']);
		$result['recordsFiltered'] = $query['total_filtered'];

		helper('html');
		$id_user = $this->session->get('user')['id_user'];

		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) {
			$stok_class = '';
			if ($val['stok'] == 0) {
				$stok_class = 'text-danger';
			}

			$attr_btn = ['data-id-barang' => $val['id_barang'], 'class' => 'btn btn-success pilih-barang btn-xs'];
			if ($val['stok'] == 0) {
				$attr_btn['disabled'] = 'disabled';
			}

			$val['nama_barang'] = '<span class="nama-barang">' . $val['nama_barang'] . '</span><span style="display:none" class="detail-barang">' . json_encode($val) . '</span>';
			$val['ignore_harga_jual'] = '<div class="text-end">' . format_number($val['harga_jual']) . '</div>';
			$val['ignore_harga_pokok'] = '<div class="text-end">' . format_number($val['harga_pokok']) . '</div>';
			$val['ignore_stok'] = '<div class="text-end ' . $stok_class . '">' . format_number($val['stok']) . '</div>';
			$val['ignore_urut'] = $no;
			$val['ignore_satuan'] = $val['satuan'];
			$val['ignore_action'] = btn_action([
				'edit' => ['url' => $this->config->baseURL . $this->currentModule['nama_module'] . '/edit?id=' . $val['id_barang']], 'delete' => [
					'url' => '', 'id' =>  $val['id_barang'], 'delete-title' => 'Hapus data barang: <strong>' . $val['nama_barang'] . '</strong> ?'
				]
			]);

			// Pilih barang
			$val['ignore_pilih'] = btn_label(['label' => 'Pilih', 'attr' => $attr_btn]);
			$no++;
		}

		$result['data'] = $query['data'];
		echo json_encode($result);
		exit();
	}
}
