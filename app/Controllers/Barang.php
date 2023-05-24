<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Controllers;
use App\Models\BarangModel;
use App\Libraries\JWDPDF;

class Barang extends \App\Controllers\BaseController
{
	protected $model;
	
	public function __construct() {
		
		parent::__construct();
		$this->model = new BarangModel;
		$this->data['site_title'] = 'Barang';
		
		$ajax = false;
		if( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) {
			$ajax = true;
		}
		
		if (!$ajax) {
			$configFilepicker = new \Config\Filepicker();
			$this->addJs('
				var filepicker_server_url = "' . $configFilepicker->serverURL . '";
				var filepicker_icon_url = "' . $configFilepicker->iconURL . '";', true
			);
		}
		
		$this->addJs($this->config->baseURL . 'public/vendors/dragula/dragula.min.js');
		$this->addJs($this->config->baseURL . 'public/vendors/jwdfilepicker/jwdfilepicker.js');
		$this->addJs($this->config->baseURL . 'public/themes/modern/js/jwdfilepicker-defaults.js');
		$this->addJs($this->config->baseURL . 'public/vendors/dropzone/dropzone.min.js');
		
		$this->addStyle($this->config->baseURL . 'public/vendors/dragula/dragula.min.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/dropzone/dropzone.min.css');
		$this->addStyle($this->config->baseURL . 'public/themes/modern/css/gallery.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/jwdfilepicker/jwdfilepicker.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/jwdfilepicker/jwdfilepicker-loader.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/jwdfilepicker/jwdfilepicker-modal.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');
		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/barang.js');
		$this->addJs($this->config->baseURL . 'public/themes/modern/js/barang-images.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/select2-kategori.js');
		$this->addStyle ( $this->config->baseURL . 'public/themes/modern/css/barang.css' );
	}
	
	public function index()
	{
		$this->hasPermission('read_all');
		$this->view('barang-result.php', $this->data);
	}
	
	public function generateExcel($output) 
	{
		$filepath = $this->model->writeExcel();
		$filename = 'Daftar Barang.xlsx';
		
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
		$this->generateExcel($output); 
	}
	
	public function generatePdf($output) 
	{
		$barang = $this->model->getDataBarang();
		if (!$barang) {
			$this->errorDataNotFound();
			return false;
		}
		
		$identitas = $this->model->getIdentitas();
		$pdf = new JWDPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
		$pdf->setFooterText('Daftar Barang per ' . format_date(date('Y-m-d')));

		$pdf->setPageUnit('mm');

		// set document information
		$pdf->SetCreator($identitas['nama']);
		$pdf->SetAuthor($identitas['nama']);
		$pdf->SetTitle('Daftar Barang Per ' . format_date(date('Y-m-d')) );
		$pdf->SetSubject('Daftar Barang');
		
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
		$pdf->Cell(0, 0, 'Daftar Barang', 0, 1, 'C', 0, '', 0, false, 'T', 'M' );
		$pdf->SetFont ('helvetica', 'B', $font_size + 2, '', 'default', true );
		$pdf->Cell(0, 0, 'Tanggal : ' . format_date(date('Y-m-d')), 0, 1, 'C', 0, '', 0, false, 'T', 'M' );
		
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
					<th style="width:10%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">Kode Barang</th>
					<th style="width:50%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">Nama Barang</th>
					<th style="width:10%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Satuan</th>
					<th style="width:8%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Stok</th>
					<th style="width:16%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Barcode</th>
				</tr>
			</thead>
			<tbody>
		EOD;

			$no = 1;
			$format_number = 'format_number';

			foreach ($barang as $val) {
				$tbl .= <<<EOD
					<tr>
						<td style="width:5%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">$no</td>
						<td style="width:10%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">$val[kode_barang]</td>
						<td style="width:50%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color">$val[nama_barang]</td>
						<td style="width:10%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color">$val[satuan]</td>
						<td style="width:8%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['total_stok'])}</td>
						<td style="width:16%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color">$val[barcode]</td>
					</tr>
					EOD;
				$no++;
			}
		
			$tbl .= <<<EOD
			</tbody>
		</table>
		EOD;

		$pdf->writeHTML($tbl, false, false, false, false, '');
		
		$filename = 'Daftar Barang - ' . date('dmY') . '.pdf';
		$filepath = ROOTPATH . 'public/tmp/barang_' . time() . '.pdf.tmp';
		
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
		$this->generatePdf($output); 
	}
	
	public function ajaxSendEmail() {
		
		if ($_GET['file_format'] == 'pdf') {
			$filepath = $this->generatePdf('file');
			$filename = 'Daftar Barang - ' . format_date(date('Y-m-d')) . '.pdf';
		} else {
			$filepath = $this->generateExcel('file');
			$filename = 'Daftar Barang - ' . format_date(date('Y-m-d')) . '.xlsx';
		}
		
		$email_config = new \Config\EmailConfig;
		$email_data = array('from_email' => $email_config->from
						, 'from_title' => $email_config->fromTitle
						, 'to_email' => $_GET['email']
						, 'to_name' => ''
						, 'email_subject' => 'Daftar Barang Per ' . format_date(date('Y-m-d'))
						, 'email_content' => '<p>Berikut terlampir data barang per ' . format_date(date('Y-m-d')) . '.<br/><br/><p>Salam</p>'
						, 'attachment' => ['path' => $filepath, 'name' => $filename]
		);
		
		require_once('app/Libraries/SendEmail.php');
		
		$emaillib = new \App\Libraries\SendEmail;
		$emaillib->init();
		$send_email =  $emaillib->send($email_data);

		delete_file($filepath);
		if ($send_email['status'] == 'ok') {
			$message['status'] = 'ok';
			$message['message'] = 'Daftar barang berhasil dikirim ke alamat email: ' . $_GET['email'];
		} else {
			$message['status'] = 'error';
			$message['message'] = 'Daftar barang gagal dikirim ke alamat email: ' . $_GET['email'] . '<br/>Error: ' . $send_email['message'];
		}
		
		echo json_encode($message);
	}
	
	public function ajaxDeleteData() {
		$result = $this->model->deleteData();
		
		if ($result) {
			$message = ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
			echo json_encode($message);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Data gagal dihapus']);
		}
	}
	
	public function ajaxGenerateBarcodeNumber($repeat = false) 
	{
		$add = $repeat ? rand(1, 60) : 0;
		$number = time() + $add;
		$digit = '899' . substr($number, 0, 9);
		$split = str_split($digit);

		$sum_genab = 0;
		$sum_ganjil = 0;
		foreach ($split as $key => &$val) {
			if ( ($key + 1) % 2 ) {
				$sum_ganjil = $sum_ganjil + $val;
			} else {
				$sum_genab = $sum_genab + $val;
			}
		}

		$sum_genab = $sum_genab * 3;
		$sum = $sum_genab + $sum_ganjil;

		$sisa = $sum % 10;
		if ($sisa == 0) {
			$check_digit = 0;
		} else {
			$check_digit = 10 - $sisa;
		}

		$barcode_number = $digit . $check_digit;
		$exists = $this->model->getBarangByBarcode($barcode_number);
		if ($exists) {
			$this->ajaxGenerateBarcodeNumber(true);
		}
		
		echo $barcode_number;
	}
	
	public function ajaxGetFormData() {
		$this->data['form_data'] = [];
		if (isset($_GET['id'])) {
			if ($_GET['id']) {
				$this->data['form_data'] = $this->model->getJenisHargaById($_GET['id']);
				if (!$this->data['form_data'])
					return;
			}
		}
		echo view('themes/modern/jenis-harga-form.php', $this->data);
	}
	
	public function ajaxUpdateData() {

		$message = $this->model->saveData();
		echo json_encode($message);
	}
	
	public function add() 
	{
		$this->data['message'] = [];
		$this->data['title'] = 'Tambah Barang';
		
		if (!empty($_POST['submit'])) {
			$result = $this->saveData();
			$this->data['message'] = $result;
			$this->setData($result['id']);
		} else {
			
			$this->setData();
		}
	
		$this->view('barang-form.php', $this->data);
	}
	
	public function edit() 
	{
		$this->data['message'] = [];
		if (!empty($_POST['submit'])) {
			$this->data['message'] = $this->saveData();
		}
		
		$this->data['title'] = 'Edit Barang';
		$this->setData($_GET['id']);
		
		if (@$_GET['mobile'] == 'true') {
			echo view('themes/modern/barang-form.php', $this->data);
		} else {
			$this->view('barang-form.php', $this->data);
		}
	}
	
	public function edit_stok() 
	{
		$this->data['message'] = [];
		if (!empty($_POST['submit'])) 
		{
			$this->data['message'] = $this->model->saveDataStok();
			if (@$_GET['mobile'] == 'true') {
				echo json_encode($this->data['message']);
				exit;
			}
		}
		
		if (!empty($_POST['id'])) {
			$id = $_POST['id'];
		} else {
			$id = $_GET['id'];
		}
		
		$this->data['title'] = 'Edit Stok Barang';
		$this->setData($id);
		
		if (@$_GET['mobile'] == 'true') {
			echo view('themes/modern/barang-form-stok.php', $this->data);
		} else {
			$this->view('barang-form-stok.php', $this->data);
		}
	}
	
	private function setData($id = '') 
	{
		$result = $this->model->getKategori();
		$list_kategori = kategori_list($result);
		$this->data['list_kategori'] = $this->buildKategoriList($list_kategori);
		
		/* echo '<pre>';
		print_r( $this->data['list_kategori']);
		die;
		 */
		
		$harga_pokok = $this->model->getHargaPokokByIdBarang($id);
		$harga_jual = $this->model->getHargaJualByIdBarang($id);
		
		$form_data = $this->model->getBarangById($id);
		$this->data['form_data'] = $form_data;

		$data_stok = [];
		$stok = $this->model->getStok($id);
		if ($stok) {
			foreach ($stok as $val) {
				$data_stok[$val['id_gudang']] = $val;
			}
		}
		$this->data['stok'] = $data_stok;
		$this->data['harga_pokok'] = $harga_pokok;
		$this->data['harga_jual'] = $harga_jual;
		$this->data['gudang'] = $this->model->getAllGudang();
		$this->data['satuan'] = $this->model->getAllSatuan();
	}
	
	private function saveData() {
		
		$result = $this->model->saveData();
		return $result;
	}
	
	private function buildKategoriList($arr, $id_parent = '', &$result = [])
	{
		
		foreach ($arr as $key => $val) 
		{
			$result[$val['id_barang_kategori']] = ['attr' => ['data-parent' => $id_parent, 'data-icon' => $val['icon'], 'data-new' => $val['new']]
													, 'text' => $val['nama_kategori']
												];
			if (key_exists('children', $val))
			{
				$result[$val['id_barang_kategori']]['attr']['disabled'] = 'disabled';
				$this->buildKategoriList($val['children'], $val['id_barang_kategori'], $result);
			}
		}
		return $result;
	}
	
	/* function buildKategoriListXX($arr, $id_parent = '')
	{
		$option = '';
		foreach ($arr as $key => $val) 
		{
			// Check new
			$disabled = key_exists('children', $val) ? 'disabled="disabled"' : '';
			$option .= '<option value="' . $val['id_barang_kategori'] . '" data-parent="' . $id_parent . '" ' . $disabled . ' data-icon="' . $val['icon'] . '" data-new="' . $val['new'] . '">' . $val['nama_kategori'] . '</option>' . "\r\n";
			if (key_exists('children', $val))
			{ 
				$option .= $this->buildKategoriList($val['children'], $val['id_barang_kategori']);
			}
		}
		return $option;
	} */
		
	public function getDataDT() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllData();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListData();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			if ($val['berat'] < 1000) {
				$val['berat'] = $val['berat'] . ' Gram';
			} else {
				$val['berat'] = round($val['berat'] / 1000, 2) . ' Kg';
			}
			$val['ignore_urut'] = $no;
			$val['ignore_stok'] = '<div class="text-end">' . $val['stok'] . '</div>';
			
			$val['ignore_action'] = '<div class="form-inline btn-action-group">'
										. btn_link(
												['icon' => 'fas fa-edit'
													, 'url' => base_url() . '/barang/edit?id=' . $val['id_barang']
													, 'attr' => ['class' => 'btn btn-success btn-edit btn-xs me-1', 'data-id' => $val['id_barang']]
													, 'label' => 'Edit'
												])
										. btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete btn-xs'
																	, 'data-id' => $val['id_barang']
																	, 'data-delete-title' => 'Hapus barang : <strong>' . $val['nama_barang'] . '</strong>'
																]
													, 'label' => 'Delete'
												]) . 
										
										'</div>';
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
}