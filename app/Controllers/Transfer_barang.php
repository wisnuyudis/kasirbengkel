<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Controllers;
use App\Models\TransferBarangModel;

class Transfer_barang extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new TransferBarangModel;	
		$this->data['site_title'] = 'Transfer Barang';
		
		$this->addJs($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.min.css');

		$this->addJs ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal.js');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal.css');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal-loader.css');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal-fapicker.css');
		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/transfer-barang.js');
		$this->addStyle ( $this->config->baseURL . 'public/themes/modern/css/modal-pilih-barang.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		$this->addJs ( $this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');
		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/wilayah.js');
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		
		$data = $this->data;
		$this->view('transfer-barang-result.php', $data);
	}
	
	public function add()
	{
		$this->setData();
		$this->data['title'] = 'Transfer Barang';
		$this->data['breadcrumb']['Add'] = '';

		$this->view('transfer-barang-form.php', $this->data);
	}
	
	public function ajaxSaveData() {
		$result = $this->model->saveData();
		echo json_encode($result);
	}
	
	public function ajaxDeleteData() {
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
		
		$result = $this->model->getJenisHarga();
		$jenis_harga_selected = '';
		foreach ($result as $val) {
			$jenis_harga[$val['id_jenis_harga']] = $val['nama_jenis_harga'];
			if ($val['default_harga'] == 'Y') {
				$jenis_harga_selected = $val['id_jenis_harga'];
			}
		}
		$this->data['jenis_harga'] = $jenis_harga;
		$this->data['jenis_harga_selected'] = $jenis_harga_selected;
		
	}
	
	public function edit()
	{
		$this->hasPermission('update_all', 'barang_transfer');
		
		$this->data['title'] = 'Edit Transfer Barang';
		$this->setData();

		if (empty($_GET['id'])) {
			$this->errorDataNotFound();
		}
		
		$this->data['breadcrumb']['Edit'] = '';

		$this->data['transfer_barang'] = $this->model->getTransferBarangById($_GET['id']);
		$this->data['jenis_harga_selected'] =$this->data['transfer_barang']['id_jenis_harga_transfer'];
		$this->data['barang'] = $this->model->getBarangByIdTransferBarang($_GET['id']);

		$this->view('transfer-barang-form.php', $this->data);
	}
	
	public function notaTransferPdf() 
	{
		require_once('app/ThirdParty/Tcpdf/tcpdf.php');
		require_once('app/Helpers/util_helper.php');
		
		$transfer_barang = $this->model->getTransferBarangDetail($_GET['id']);
		if (!$transfer_barang) {
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
		$pdf->SetTitle('Nota Transfer Barang #' .$transfer_barang['data']['no_nota_transfer']);
		$pdf->SetSubject('Nota Transfer Barang');

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
		$url = $identitas['url_website'] ? $identitas['url_website'] : '';
		$pdf->Image(ROOTPATH . 'public/images/' . $setting['logo'], 10, 20, 0, 0, 'JPG', $url);

		$image_dim = getimagesize(ROOTPATH . 'public/images/' . $setting['logo']);
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
		$pdf->write1DBarcode($transfer_barang['data']['no_nota_transfer'], 'C128', '', '', '', 20, 0.4, $barcode_style, 'N');

		$pdf->ln(8);
		$pdf->SetFont ('helvetica', 'B', $font_size + 10, '', 'default', true );
		$pdf->Cell(0, 0, 'NOTA TRANSFER BARANG', 0, 1, 'C', 0, '', 0, false, 'T', 'M' );

		$pdf->ln(8);
		
		// Gudang asal
		$pdf->SetFont ('helvetica', 'B', $font_size, '', '', true );
		$pdf->Cell(0, 0, 'Gudang Asal ', 0, 1);
		$pdf->ln(4);

		$pdf->SetFont ('helvetica', '', $font_size, '', 'default', true );

		$y =  $pdf->GetY();
		$pdf->Cell(10, 0, 'Nama', 0, 1);
		$pdf->SetXY($margin_left + 13, $y);
		$pdf->Cell(10, 0, ':', 0, 1);
		$pdf->SetXY($margin_left + 15, $y);
		$pdf->Cell(10, 0, $transfer_barang['gudang_asal']['nama_gudang'], 0, 1);

		$y =  $pdf->GetY();
		$pdf->Cell(10, 0, 'Alamat', 0, 1);
		$pdf->SetXY($margin_left + 13, $y);
		$pdf->Cell(0, 0, ':', 0, 1);
		$pdf->SetXY($margin_left + 15, $y);
		$pdf->Cell(0, 0, $transfer_barang['gudang_asal']['alamat_gudang'], 0, 1);
		
		$pdf->SetX($margin_left + 15);
		$pdf->Cell(0, 0, $transfer_barang['gudang_asal']['nama_kecamatan'] . ', ' . $transfer_barang['gudang_asal']['nama_kabupaten'], 0, 1);
		$pdf->SetX($margin_left + 15);
		$pdf->Cell(0, 0, $transfer_barang['gudang_asal']['nama_propinsi'], 0, 1);
		
		$pdf->ln(5);
		
		// Gudang tujuan
		$pdf->SetFont ('helvetica', 'B', $font_size, '', '', true );
		$pdf->Cell(0, 0, 'Gudang Tujuan ', 0, 1);
		$pdf->ln(4);

		$pdf->SetFont ('helvetica', '', $font_size, '', 'default', true );

		$y =  $pdf->GetY();
		$pdf->Cell(10, 0, 'Nama', 0, 1);
		$pdf->SetXY($margin_left + 13, $y);
		$pdf->Cell(10, 0, ':', 0, 1);
		$pdf->SetXY($margin_left + 15, $y);
		$pdf->Cell(10, 0, $transfer_barang['gudang_tujuan']['nama_gudang'], 0, 1);

		$y =  $pdf->GetY();
		$pdf->Cell(10, 0, 'Alamat', 0, 1);
		$pdf->SetXY($margin_left + 13, $y);
		$pdf->Cell(0, 0, ':', 0, 1);
		$pdf->SetXY($margin_left + 15, $y);
		$pdf->Cell(0, 0, $transfer_barang['gudang_tujuan']['alamat_gudang'], 0, 1);
		
		$pdf->SetX($margin_left + 15);
		$pdf->Cell(0, 0, $transfer_barang['gudang_tujuan']['nama_kecamatan'] . ', ' . $transfer_barang['gudang_tujuan']['nama_kabupaten'], 0, 1);
		$pdf->SetX($margin_left + 15);
		$pdf->Cell(0, 0, $transfer_barang['gudang_tujuan']['nama_propinsi'], 0, 1);
		
		// Barang
		$pdf->ln(5);
		$pdf->SetFont ('helvetica', 'B', $font_size, '', '', true );
		$y =  $pdf->GetY();
		$pdf->Cell(0, 0, 'Detail Barang' , 0, 1);
		$pdf->SetFont ('helvetica', '', $font_size, '', '', true );
		$pdf->SetY($y);
		
		$pdf->Cell(0, 0, format_date($transfer_barang['data']['tgl_nota_transfer']), 0, 1, 'R', 0, '', 0, false, 'T', 'M' );

		$pdf->ln(5);
		$pdf->SetFont ('helvetica', '', $font_size, '', 'default', true );
		$border_color = '#CECECE';
		$background_color = '#efeff0';
		$tbl = <<<EOD
		<table border="0" cellspacing="0" cellpadding="6">
			<thead>
				<tr border="1" style="background-color:$background_color">
					<th style="width:5%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">No</th>
					<th style="width:35%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">Nama Barang</th>
					<th style="width:20%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Barcode</th>
					<th style="width:10%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Satuan</th>
					<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Harga Satuan</th>
					<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Kuantitas</th>
				</tr>
			</thead>
			<tbody>
		EOD;

			$no = 1;
			$format_number = 'format_number';
			$total_qty = 0;
			foreach ($transfer_barang['detail'] as $val) {
				$total_qty += $val['qty_transfer'];
				$tbl .= <<<EOD
					<tr>
						<td style="width:5%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">$no</td>
						<td style="width:35%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">$val[nama_barang]</td>
						<th style="width:20%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">$val[barcode]</th>
						<th style="width:10%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">$val[satuan]</th>
						<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['harga_satuan'])}</th>
						<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['qty_transfer'])}</th>
					</tr>

		EOD;
			$no++;
			}

		$tbl .= <<<EOD
				<tr style="background-color:$background_color">
					<td colspan="5" style="width:85%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">Total</td>
					<td style="width:15%;border-bottom-color:$border_color;border-right-color:$border_color" align="right">$total_qty</td>
				</tr>
			</tbody>
		</table>
		EOD;

		$pdf->writeHTML($tbl, false, false, false, false, '');
		$pdf->ln(5);

		$pdf->SetY(-20);
		// $pdf->writeHTML('<hr style="background-color:#FFFFFF; border-bottom-color:#CCCCCC;height:0"/>', false, false, false, false, '');
		$pdf->writeHTML('<div style="background-color:#FFFFFF; border-bottom-color:#ababab;height:0"></div>', false, false, false, false, '');

		$pdf->ln(2);

		$pdf->SetFont ('helvetica', 'I', $font_size, '', '', true );
		$pdf->SetTextColor(50,50,50);
		$pdf->SetTextColor(100,100,100);
		$pdf->Cell(0, 0, $setting['footer_text'], 0, 1, 'L');
		
		$filename = str_replace(['/', '\\'], '_', $transfer_barang['data']['no_nota_transfer']) . '.pdf';
		$filepath_invoice = ROOTPATH . 'public/tmp/Invoice-' . $filename;
		
		if (@$_GET['ajax'] == 'true') {
			$pdf->Output($filepath_invoice, 'F');
			$content = file_get_contents($filepath_invoice);
			echo $content;
			delete_file($filepath_invoice);
		} else {
			$pdf->Output('Nota Transfer - ' . $filename, 'D');
		}
		exit;
	}
	
	// Transfer Barang
	public function getDataDTTransferBarang() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllDataTransferBarang();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListDataTransferBarang();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		$id_user = $this->session->get('user')['id_user'];
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$val['tgl_nota_transfer'] = '<div class="text-end">' . format_tanggal($val['tgl_nota_transfer']) . '</div>';
			$val['id_gudang_asal'] = $val['nama_gudang_asal'];
			$val['id_gudang_tujuan'] = $val['nama_gudang_tujuan'];
			$val['total_qty_transfer'] = '<div class="text-end">' . $val['total_qty_transfer'] . '</div>';
			$val['ignore_urut'] = $no;
			$val['ignore_action'] = '<div class="btn-action-group">' . 
				btn_link(['url' => base_url() . '/transfer-barang/edit?id=' . $val['id_transfer_barang'],'label' => 'Edit', 'icon' => 'fas fa-edit', 'attr' => ['target' => '_blank', 'class' => 'btn btn-success btn-xs me-1'] ]) . 
				btn_label(['label' => 'Delete', 'icon' => 'fas fa-times', 'attr' => ['class' => 'btn btn-danger btn-xs del-data me-1', 'data-id' => $val['id_transfer_barang'], 'data-delete-message' => 'Hapus data transfer barang ?'] ]) . 
				btn_link(['url' => base_url() . '/transfer-barang/notaTransferPdf?id=' . $val['id_transfer_barang'],'label' => 'Nota', 'icon' => 'fas fa-file-pdf', 'attr' => ['data-filename' => 'Nota Transfer - ' . $val['no_nota_transfer'], 'target' => '_blank', 'class' => 'btn btn-primary btn-xs save-pdf me-1'] ]) .
			'</div>';
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
	public function getDataDTListBarang() {
		echo view('themes/modern/transfer-barang-list-barang.php', $this->data);
	}
	
	public function getDataDTBarang() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllDataBarang();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListDataBarang( $_GET['id_gudang'], $_GET['id_jenis_harga'] );
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		$id_user = $this->session->get('user')['id_user'];
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$stok_class = '';
			if ($val['stok'] == 0) {
				$stok_class = 'text-danger';
			}
			
			$attr_btn = ['data-id-barang' => $val['id_barang'],'class'=>'btn btn-success pilih-barang btn-xs'];
			if ($val['stok'] == 0) {
				$attr_btn['disabled'] = 'disabled';
			}
			
			$val['nama_barang'] = '<span class="nama-barang">' . $val['nama_barang'] . '</span><span style="display:none" class="detail-barang">' . json_encode($val) . '</span>';
			$val['ignore_harga_jual'] = '<div class="text-end">' . format_number($val['harga_jual']) . '</div>';
			$val['ignore_harga_pokok'] = '<div class="text-end">' . format_number($val['harga_pokok']) . '</div>';
			$val['ignore_stok'] = '<div class="text-end ' . $stok_class . '">' . format_number($val['stok']) . '</div>';
			$val['ignore_satuan'] = $val['satuan'];
			$val['ignore_urut'] = $no;
			$val['ignore_action'] = btn_action([
									'edit' => ['url' => $this->config->baseURL . $this->currentModule['nama_module'] . '/edit?id='. $val['id_barang']]
								, 'delete' => ['url' => ''
												, 'id' =>  $val['id_barang']
												, 'delete-title' => 'Hapus data barang: <strong>'.$val['nama_barang'].'</strong> ?'
											]
							]);
							
			// Pilih barang
			$val['ignore_pilih'] = btn_label(['label' => 'Pilih', 'attr' => $attr_btn]);
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
}
