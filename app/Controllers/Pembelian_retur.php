<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Controllers;
use App\Models\PembelianReturModel;

class Pembelian_retur extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new PembelianReturModel;	
		$this->data['site_title'] = 'Retur Pembelian';
		
		$this->addJs($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.min.css');

		$this->addJs ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal.js');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal.css');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal-loader.css');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal-fapicker.css');
		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/pembelian-retur.js');
		$this->addStyle ( $this->config->baseURL . 'public/themes/modern/css/modal-pilih-barang.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		$this->view('pembelian-retur-result.php', $this->data);
	}
	
	public function add()
	{
		$this->setData();
		$this->data['title'] = 'Tambah Data Retur Pembelian';
		$this->data['breadcrumb']['Add'] = '';
				
		$this->view('pembelian-retur-form.php', $this->data);
	}
	
	public function ajaxSaveData() {
		$result = $this->model->saveData();
		echo json_encode($result);
	}
	
	public function ajaxDeleteData() {
		
		$this->hasPermissionPrefix('delete', 'pembalian_retur');
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
		$this->data['gudang'] = $gudang;
	}
	
	public function edit()
	{
		$this->hasPermission('update_all', 'pembelian_retur');
		
		$this->data['title'] = 'Edit Retur Pembelian';
		$this->setData();
		
		if (empty($_GET['id'])) {
			$this->errorDataNotFound();
		}
		
		$this->data['breadcrumb']['Edit'] = '';
		$this->data['pembelian_retur'] = $this->model->getPembelianReturById($_GET['id']);
		$this->data['barang'] = $this->model->getBarangByIdPembelianRetur($_GET['id']);
		$this->view('pembelian-retur-form.php', $this->data);
	}
	
	public function notaReturPdf() 
	{
		require_once('app/ThirdParty/Tcpdf/tcpdf.php');
		require_once('app/Helpers/util_helper.php');
		
		$pembelian_retur = $this->model->getPembelianReturDetail($_GET['id']);
		if (!$pembelian_retur) {
			$this->errorDataNotFound();
			return false;
		}
		
		$identitas = $this->model->getIdentitas();
		$setting = $this->model->getSettingInvoice();

		$pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);

		$pdf->setPageUnit('mm');

		// set document information
		$pdf->SetCreator($identitas['nama']);
		$pdf->SetAuthor($identitas['nama']);
		$pdf->SetTitle('Invoice #' .$pembelian_retur['data']['no_nota_retur']);
		$pdf->SetSubject('Nota Retur');

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
		
		// $pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 0, 0)));
		$pdf->SetTextColor(50,50,50);
		$pdf->Image(ROOTPATH . 'public/images/logo_invoice.jpg', 10, 20, 0, 0, 'JPG', 'https://jagowebdev.com');

		$image_dim = getimagesize(ROOTPATH . 'public/images/logo_invoice.jpg');
		$x = $margin_left + ($image_dim[0] * 0.2645833333) + 5;
		$pdf->SetXY($x, $margin_top + 3);
		$pdf->Cell(0, 9, $identitas['nama'], 0, 1, 'L', 0, '', 0, false, 'T', 'M' );

		//Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')
		$pdf->SetX($x);
		$pdf->SetFont ('helvetica', '', $font_size, '', 'default', true );
		$pdf->Cell(0, 0, $identitas['alamat'], 0, 1, 'L', 0, '', 0, false, 'T', 'M' );
		$pdf->SetX($x);
		$pdf->Cell(0, 0, $identitas['nama_kelurahan'] . ', ' . $identitas['nama_kecamatan'], 0, 1, 'L', 0, '', 0, false, 'T', 'M' );
		$pdf->SetX($x);
		$pdf->Cell(0, 0, $identitas['nama_kabupaten'] . ', ' . $identitas['nama_propinsi'] , 0, 1, 'L', 0, '', 0, false, 'T', 'M' );

		$barcode_style = array(
			'position' => 'R',
			'align' => 'C',
			'stretch' => false,
			'fitwidth' => true,
			'cellfitalign' => '',
			'border' => false,
			'hpadding' => 'auto',
			'vpadding' => 'auto',
			'fgcolor' => array(0,0,0),
			'bgcolor' => false, //array(255,255,255),
			'text' => true,
			'font' => 'helvetica',
			'fontsize' => $font_size,
			'stretchtext' => false
		);

		$pdf->SetY($margin_top + 10);
		$pdf->write1DBarcode($pembelian_retur['data']['no_nota_retur'], 'C128', '', '', '', 20, 0.4, $barcode_style, 'N');

		$pdf->ln(8);
		$pdf->SetFont ('helvetica', 'B', $font_size + 10, '', 'default', true );
		$pdf->Cell(0, 0, 'NOTA RETUR', 0, 1, 'C', 0, '', 0, false, 'T', 'M' );

		$pdf->ln(8);
		$pdf->SetFont ('helvetica', 'B', $font_size, '', '', true );
		$pdf->Cell(0, 0, 'Penjual ', 0, 1);
		$pdf->ln(4);

		$pdf->SetFont ('helvetica', '', $font_size, '', 'default', true );

		$y =  $pdf->GetY();
		$pdf->Cell(10, 0, 'Nama', 0, 1);
		$pdf->SetXY($margin_left + 13, $y);
		$pdf->Cell(10, 0, ':', 0, 1);
		$pdf->SetXY($margin_left + 15, $y);
		$pdf->Cell(10, 0, $pembelian_retur['supplier']['nama_supplier'], 0, 1);

		$y =  $pdf->GetY();
		$pdf->Cell(10, 0, 'Alamat', 0, 1);
		$pdf->SetXY($margin_left + 13, $y);
		$pdf->Cell(0, 0, ':', 0, 1);
		$pdf->SetXY($margin_left + 15, $y);
		$pdf->Cell(0, 0, $pembelian_retur['supplier']['alamat_supplier'], 0, 1);
		
		if (!empty($pembelian_retur['supplier']['nama_kecamatan'])) {
			$pdf->SetX($margin_left + 15);
			$pdf->Cell(0, 0, 'Kec. ' . $pembelian_retur['supplier']['nama_kecamatan'] . ', Kab. ' . $pembelian_retur['supplier']['nama_kabupaten'], 0, 1);
			$pdf->SetX($margin_left + 15);
			$pdf->Cell(0, 0, $pembelian_retur['supplier']['nama_propinsi'], 0, 1);
		}

		$pdf->ln(5);
		$pdf->SetFont ('helvetica', 'B', $font_size, '', '', true );
		$y =  $pdf->GetY();
		$pdf->Cell(0, 0, 'Barang Yang Diretur' , 0, 1);
		$pdf->SetFont ('helvetica', '', $font_size, '', '', true );
		$pdf->SetY($y);
		
		$pdf->Cell(0, 0, format_date($pembelian_retur['data']['tgl_nota_retur']), 0, 1, 'R', 0, '', 0, false, 'T', 'M' );

		$pdf->ln(5);
		$pdf->SetFont ('helvetica', '', $font_size, '', 'default', true );
		$border_color = '#CECECE';
		$background_color = '#efeff0';
		$tbl = <<<EOD
		<table border="0" cellspacing="0" cellpadding="6">
			<thead>
				<tr border="1" style="background-color:$background_color">
					<th style="width:5%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">No</th>
					<th style="width:35%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">Deskripsi</th>
					<th style="width:10%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Kuantitas Retur</th>
					<th style="width:10%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Harga Satuan</th>
					<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Harga Total</th>
					<th style="width:10%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Diskon</th>
					<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Harga Neto</th>
				</tr>
			</thead>
			<tbody>
		EOD;

			$no = 1;
			$format_number = 'format_number';
			foreach ($pembelian_retur['detail'] as $val) {
				$tbl .= <<<EOD
					<tr>
						<td style="width:5%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">$no</td>
						<td style="width:35%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">$val[nama_barang]</td>
						<th style="width:10%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['qty_retur'])}</th>
						<th style="width:10%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['harga_satuan'])}</th>
						<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['harga_total_retur'])}</th>
						<th style="width:10%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['diskon_retur'])}</th>
						<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['harga_neto_retur'])}</th>
					</tr>

		EOD;
			$no++;
			}

		$diskon = format_number($pembelian_retur['data']['total_diskon_item_retur']);
		$total = format_number($pembelian_retur['data']['neto_retur']);
		$sub_total = format_number($pembelian_retur['data']['sub_total']);
		

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
					<td colspan="6" style="width:75%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">Total</td>
					<td style="width:25%;border-bottom-color:$border_color;border-right-color:$border_color" align="right">$total</td>
				</tr>
			</tbody>
		</table>
		EOD;

		$pdf->writeHTML($tbl, false, false, false, false, '');
		$pdf->ln(5);

	
		$pdf->ln(5);
		// $pdf->SetFont ('helvetica', '', $font_size, '', '', true );

		$pdf->SetY(-20);
		// $pdf->writeHTML('<hr style="background-color:#FFFFFF; border-bottom-color:#CCCCCC;height:0"/>', false, false, false, false, '');
		$pdf->writeHTML('<div style="background-color:#FFFFFF; border-bottom-color:#ababab;height:0"></div>', false, false, false, false, '');

		$pdf->ln(2);

		$pdf->SetFont ('helvetica', 'I', $font_size, '', '', true );
		$pdf->SetTextColor(50,50,50);
		$pdf->SetTextColor(100,100,100);
		$pdf->Cell(0, 0, 'Terima kasih telah berbelanja ditempat kami. Kepuasan Anda adalah komitmen kami', 0, 1, 'L');
		// $pdf->Output('Invoice-' . str_replace(['/', '\\'], '_', $order['order']['no_invoice']) . '.pdf', 'D');

		
		$filename = 'Nota Retur - ' . str_replace(['/', '\\'], '_', $pembelian_retur['data']['no_nota_retur']) . '.pdf';
		$filepath_invoice = ROOTPATH . 'public/tmp/' . $filename;
		
		if (!empty($_GET['email'])) 
		{	
			$filepath = ROOTPATH . 'public/tmp/invoice_'. time() . '.pdf';
			$pdf->Output($filepath, 'F');
			
			$email_config = new \Config\EmailConfig;
			$email_data = array('from_email' => $email_config->from
							, 'from_title' => 'Jagowebdev.com'
							, 'to_email' => $pembelian_retur['supplier']['email']
							, 'to_name' => $pembelian_retur['supplier']['nama_supplier']
							, 'email_subject' => 'Nota Retur: ' . $pembelian_retur['data']['no_nota_retur']
							, 'email_content' => '<h2>Yth. ' . $pembelian_retur['supplier']['nama_supplier'] . '</h2><p>Berikut terlampir nota retur pembelian dari kami ' . $pembelian_retur['supplier']['nama_supplier'] . '.<br/><br/><p>Salam</p>'
							, 'attachment' => ['path' => $filepath, 'name' => $filename]
			);
			
			require_once('app/Libraries/SendEmail.php');
			
			$emaillib = new \App\Libraries\SendEmail;
			$emaillib->init();
			$send_email =  $emaillib->send($email_data);

			delete_file($filepath);
			if ($send_email['status'] == 'ok') {
				$message['status'] = 'ok';
				$message['message'] = 'Nota Retur berhasil dikirim ke alamat email: ' . $pembelian_retur['supplier']['email'];
			} else {
				$message['status'] = 'error';
				$message['message'] = 'Nota Retur gagal dikirim ke alamat email: ' . $pembelian_retur['supplier']['email'] . '<br/>Error: ' . $send_email['message'];
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
	
	public function getDataDTPembelianRetur() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllPembelianRetur();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListPembelianRetur();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		$id_user = $this->session->get('user')['id_user'];
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$val['nama_supplier'] = $val['nama_supplier'] ?: '-';
			$exp = explode(' ', $val['tgl_invoice']);
			$val['tgl_invoice'] = '<div class="text-end">' . format_tanggal($exp[0]) . '</div>';
			
			$exp = explode(' ', $val['tgl_nota_retur']);
			$val['tgl_nota_retur'] = '<div class="text-end">' . format_tanggal($exp[0]) . '</div>';
			$val['neto_retur'] = '<div class="text-end">' . format_number($val['neto_retur']) . '</div>';
			
			$val['ignore_urut'] = $no;
			$val['ignore_action'] = '<div class="btn-action-group">' . 
				btn_link(['url' => base_url() . '/pembelian-retur/edit?id=' . $val['id_pembelian_retur'],'label' => '', 'icon' => 'fas fa-edit', 'attr' => ['target' => '_blank', 'class' => 'btn btn-success btn-xs me-1', 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Edit Data'] ]) . 
				btn_label(['label' => '', 'icon' => 'fas fa-times', 'attr' => ['class' => 'btn btn-danger btn-xs del-data', 'data-id' => $val['id_pembelian_retur'], 'data-delete-message' => 'Hapus data retur pembelian ?', 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Delete Data'] ]) . 
			'</div>';
			
			$btn_kirim_email = ['url' => base_url() . '/pembelian-retur/notaReturPdf?email=true&id=' . $val['id_pembelian_retur'],'label' => '', 'icon' => 'fas fa-paper-plane', 'attr' => ['target' => '_blank', 'class' => 'btn btn-primary btn-xs me-1 kirim-email', 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Kirim Nota Retur ke Email']];
			if (!$val['email']) {
				$btn_kirim_email['attr']['disabled'] = 'disabled';
				$btn_kirim_email['attr']['class'] = $btn_kirim_email['attr']['class'] . ' disabled';
			}

			$val['ignore_nota_retur'] = '<div class="btn-action-group">' . 
				btn_link(['url' => base_url() . '/pembelian-retur/notaReturPdf?ajax=true&id=' . $val['id_pembelian_retur'],'label' => '', 'icon' => 'fas fa-file-pdf', 'attr' => ['target' => '_blank', 'class' => 'btn btn-danger btn-xs me-1 save-pdf', 'data-filename' => 'Nota Retur - ' . $val['no_nota_retur'], 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Download Nota Retur (PDF)'] ]) . 
				btn_link($btn_kirim_email) . 
			'</div>';
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
	public function getDataDTListInvoice() {
		echo view('themes/modern/pembelian-list-invoice.php', $this->data);
	}
	
	public function getDataDTInvoice() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllDataInvoice();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListDataInvoice();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		$id_user = $this->session->get('user')['id_user'];
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$stok_class = '';
			
			$val['no_invoice'] = '<span class="pembelian-detail">' . $val['no_invoice'] . '</span><span style="display:none" class="pembelian">' . json_encode($val) . '</span>';
			$val['ignore_urut'] = $no;
			
			// Pilih barang
			$val['ignore_pilih'] = btn_label(['label' => 'Pilih', 'attr' => ['data-id-pembelian' => $val['id_pembelian'],'class'=>'btn btn-success pilih-invoice btn-xs']]);
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
}