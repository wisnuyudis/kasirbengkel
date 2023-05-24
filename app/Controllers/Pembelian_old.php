<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Controllers;
use App\Models\PembelianModel;

class Pembelian extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new PembelianModel;	
		$this->data['site_title'] = 'Data Pembelian';
		
		// $this->addJs ( $this->config->baseURL . 'public/themes/modern/js/data-tables-ajax.js');
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
		
		
		$this->addJs($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.min.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal.js');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal.css');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal-loader.css');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal-fapicker.css');
		
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
		
		$this->addStyle ( $this->config->baseURL . 'public/themes/modern/css/modal-pilih-barang.css');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/pembelian.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/pembelian-images.js');
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		
		$data = $this->data;
		if (!empty($_POST['delete'])) 
		{
			$this->hasPermissionPrefix('delete', 'obat');
			
			$result = $this->model->deleteData();
			// $result = true;
			if ($result) {
				$data['msg'] = ['status' => 'ok', 'message' => 'Data pembelian berhasil dihapus'];
			} else {
				$data['msg'] = ['status' => 'error', 'message' => 'Data pembelian gagal dihapus'];
			}
		}
		$this->view('pembelian-result.php', $data);
	}
	
	private function setEdit() 
	{
		$this->data['title'] = 'Edit Data Pembelian';
		$this->data['breadcrumb']['Edit'] = '';
		unset($this->data['breadcrumb']['Add']);
	}
	
	public function ajaxGetBarangByBarcode() {
		$data = $this->model->getBarangByBarcode($_GET['code']);
		if ($data) {
			$result = ['status' => 'ok', 'data' => $data];
		} else {
			$result = ['status' => 'error', 'message' => 'Data tidak ditemukan'];
		}
		
		echo json_encode($result);
	}
	
	public function add() 
	{
		/* echo '<pre>';
		print_r($_POST); die; */
		if (!empty($_POST['id'])) {
			$this->setEdit();
		} else {
			$this->data['title'] = 'Tambah Data Pembelian';
			$this->data['breadcrumb']['Add'] = '';
		}
		
		$id_pembelian = '';
		$this->data['message'] = [];
		if (isset($_POST['submit'])) 
		{
			$this->saveData();
			if (!empty($this->data['message']['id_pembelian'])) {
				$id_pembelian = $this->data['message']['id_pembelian'];
			}
			$this->setEdit();
		}
		
		$this->setData($id_pembelian);
		
		if (!$this->data['supplier']) {
			$this->printError('Data supplier masih kosong'); 
			return;
		}
		
		if (!empty($_POST['id_barang'])) 
		{
			$pembelian_detail = [];
			$list_index = ['id_barang', 'keterangan', 'expired_date', 'harga_satuan', 'qty', 'harga_neto'];
			
			
			/* foreach ($list_index as $val) {
				$pembelian_detail[$index][$val] = $_POST[$val][$index];
			} */
			
			
			foreach ($_POST['id_barang'] as $index => $val) {
				$pembelian_detail[$index]['id_barang'] = $val;
				$barang = $this->model->getBarangById($val);
				$pembelian_detail[$index]['nama_barang'] = $barang['nama_barang'];
			}
			
			foreach ($_POST['qty'] as $index => $val) {
				$pembelian_detail[$index]['qty'] = $val;
			}
			
			foreach ($_POST['keterangan'] as $index => $val) {
				$pembelian_detail[$index]['keterangan'] = $val;
			}
			
			foreach ($_POST['harga_neto'] as $index => $val) {
				$pembelian_detail[$index]['harga_neto'] = $val;
			}
						
			foreach ($_POST['expired_date'] as $index => $val) {
				$pembelian_detail[$index]['expired_date'] = $val;
			}
			
			foreach ($_POST['harga_satuan'] as $index => $val) {
				$pembelian_detail[$index]['harga_satuan'] = $val;
			}
			
			$this->data['pembelian_detail'] = $pembelian_detail;
		}
		$this->view('pembelian-form.php', $this->data);
	}
	
	private function saveData() {
		$form_errors = $this->validateForm();
		
		if ($form_errors) {
			$this->data['message'] = ['status' => 'error', 'message' => $form_errors];
		} else {
			 $result = $this->model->saveData();
			 $this->data['message'] = $result;
		}
	}
	
	private function setData($id = null) 
	{
		$result = $this->model->getAllSupplier();
		$supplier = [];
		if ($result) {
			foreach ($result as $val) {
				$supplier[$val['id_supplier']] = $val['nama_supplier'];
			}
		}
		$this->data['supplier'] = $supplier;
		
		$result = $this->model->getAllUser();
		foreach ($result as $val) {
			$user[$val['id_user']] = $val['nama'];
		}
		$this->data['user'] = $user;
		
		$query = $this->model->getAllBarangKategori();
		foreach ($query as $val) {
			$kategori[$val['id_barang_kategori']] = $val['nama_kategori'];
		}

		$this->data['kategori'] = $kategori;
		
		$data = $this->model->getAllGudang();
		foreach ($data as $val) {
			$gudang[$val['id_gudang']] = $val['nama_gudang'];
		}
		$this->data['gudang'] = $gudang;
		
		if ($id) {
			$this->data['pembelian'] = $this->model->getPembelianById($id);
			// $this->data['stok'] = $this->model->getStok($_GET['id']);
			$this->data['pembelian_detail'] = $this->model->getPembelianDetailById($id);
			$this->data['pembayaran'] = $this->model->getPembayaranById($id);
		}
	}
	
	public function edit()
	{
		$this->hasPermissionPrefix('update', 'pembelian');
		
		$this->data['title'] = 'Edit Pembelian';
		
		if (empty($_GET['id'])) {
			$this->errorDataNotFound();
		}
				
		// Submit
		$this->data['msg'] = [];
		if (isset($_POST['submit'])) 
		{
			$this->saveData();
		}
		
		$this->setData($_GET['id']);
		$this->data['breadcrumb']['Edit'] = '';
		
		
		if (empty($this->data['pembelian'])) {
			$this->errorDataNotFound();
		}
		
		$this->view('pembelian-form.php', $this->data);
	}
	
	public function getDataDT() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllData( $this->whereOwn() );
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListData( $this->whereOwn() );
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		$id_user = $this->session->get('user')['id_user'];
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$val['total'] = format_number($val['total']);
			$val['diskon'] = format_number($val['diskon']);
			$val['ignore_search_urut'] = $no;
			$val['ignore_search_action'] = btn_action([
									'edit' => ['url' => $this->config->baseURL . $this->currentModule['nama_module'] . '/edit?id='. $val['id_pembelian']]
								, 'delete' => ['url' => ''
												, 'id' =>  $val['id_pembelian']
												, 'delete-title' => 'Hapus data faktur pembelian nomor : <strong>'.$val['no_invoice'].'</strong> ?'
											]
							]);

			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
	private function validateForm($check_unique = false) {
	
		$validation =  \Config\Services::validation();
		$validation->setRule('no_invoice', 'Nomor Invoice', 'trim|required');
		$validation->setRule('tgl_invoice', 'Tanggal Invoice', 'trim|required');
		$validation->setRule('tgl_jatuh_tempo', 'Tanggal Jatuh Tempo Faktur', 'trim|required');
		$validation->setRule('total', 'Total', 'trim|required');
		$validation->setRule('sub_total', 'Sub Total', 'trim|required');
		$validation->withRequest($this->request)->run();
		$form_errors = $validation->getErrors();
		
		if ($_POST['terima_barang'] == 'Y') {
			if (trim($_POST['tgl_terima_barang']) == '') {
				$form_errors['tgl_terima_barang'] = 'Tanggal terima barang harus diisi';
			}
		}
		
		if ($_POST['using_detail_barang'] == 0) {
			$form_errors['using_detail_barang'] = 'Detil barang harus diisi';
		} else {
			foreach ($_POST['qty'] as $key => $val) 
			{
				if (trim($_POST['qty'][$key]) == '') {
					$form_errors['qty'] = 'Kuantitas harus diisi';
				}
				
				if (trim($_POST['keterangan'][$key]) == '') {
					$form_errors['keterangan'] = 'Keterangan harus diisi';
				}
				
				if (trim($_POST['harga_satuan'][$key]) == '') {
					$form_errors['harga_satuan'] = 'Harga satuan harus diisi';
				}
				
				if (trim($_POST['harga_neto'][$key]) == '') {
					$form_errors['harga_neto'] = 'Harga Neto harus diisi';
				}
			}
		}
		
		if ($_POST['using_pembayaran'] == 1) {
			
			foreach ($_POST['jml_bayar'] as $key => $val) 
			{
				if (trim($_POST['jml_bayar'][$key]) == '' || trim($_POST['jml_bayar'][$key]) == 0) {
					$form_errors['jml_bayar'] = 'Jumlah Bayar harus diisi';
				}
				
				if (trim($_POST['tgl_bayar'][$key]) == '') {
					$form_errors['tgl_bayar'] = 'Tanggal Bayar harus diisi';
				}
			}
		}
		
		return $form_errors;
	}
	
	public function getDataDTListBarang() {
		echo view('themes/modern/pembelian-list-barang.php', $this->data);
	}
	
	public function getDataDTBarang() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllDataBarang( $this->whereOwn() );
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListDataBarang( $this->whereOwn() );
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		$id_user = $this->session->get('user')['id_user'];
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$val['ignore_harga_jual'] = '<div class="text-end">' . format_number($val['harga_jual']) . '</div>';
			$val['ignore_harga_pokok'] = '<div class="text-end">' . format_number($val['harga_pokok']) . '</div>';
			$val['ignore_stok'] = '<div class="text-end">' . format_number($val['stok']) . '</div>';
			$val['nama_barang'] = '<span class="nama-barang">' . $val['nama_barang'] . '</span><span style="display:none" class="detail-barang">' . json_encode($val);
			$val['ignore_urut'] = $no;
			$val['ignore_satuan'] = $val['satuan'];
			$val['ignore_action'] = btn_action([
									'edit' => ['url' => $this->config->baseURL . $this->currentModule['nama_module'] . '/edit?id='. $val['id_barang']]
								, 'delete' => ['url' => ''
												, 'id' =>  $val['id_barang']
												, 'delete-title' => 'Hapus data barang: <strong>'.$val['nama_barang'].'</strong> ?'
											]
							]);
							
			// Pilih barang
			$val['ignore_pilih'] = btn_label(['label' => 'Pilih', 'attr' => ['data-id-barang' => $val['id_barang'],'class'=>'btn btn-success pilih-barang btn-xs']]);
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
}
