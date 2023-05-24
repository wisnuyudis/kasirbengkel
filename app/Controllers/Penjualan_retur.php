<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Controllers;
use App\Models\PenjualanReturModel;

class Penjualan_retur extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new PenjualanReturModel;	
		$this->data['site_title'] = 'Retur Penjualan';
		
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
		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/penjualan-retur.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/penjualan-retur-dokumen.js');
		$this->addStyle ( $this->config->baseURL . 'public/themes/modern/css/modal-pilih-barang.css');
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		
		$data = $this->data;
		if (!empty($_POST['delete'])) 
		{
			$this->hasPermissionPrefix('delete', 'penjualan');
			
			$result = $this->model->deleteData();
			// $result = true;
			if ($result) {
				$data['msg'] = ['status' => 'ok', 'message' => 'Data penjualan berhasil dihapus'];
			} else {
				$data['msg'] = ['status' => 'error', 'message' => 'Data penjualan gagal dihapus'];
			}
		}
		$this->view('penjualan-retur-result.php', $data);
	}
	
	public function add()
	{
		$this->setData();
		$this->data['title'] = 'Tambah Data Penjualan';
		$this->data['breadcrumb']['Add'] = '';
		
		$this->view('penjualan-retur-form.php', $this->data);
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
	}
	
	public function edit()
	{
		$this->hasPermission('update_all', 'penjualan');
		
		$this->data['title'] = 'Edit Penjualan';
		$this->setData();
		$data = $this->data;
		
		if (empty($_GET['id'])) {
			$this->errorDataNotFound();
			return;
		}
		
		$data['breadcrumb']['Edit'] = '';
	
		$this->data['penjualan_retur'] = $this->model->getPenjualanReturById($_GET['id']);
		if (!$this->data['penjualan_retur']) {
			$this->errorDataNotFound();
			return;
		}
		$dokumen = [];
		if ($this->data['penjualan_retur']) {
			$dokumen = $this->model->getDokumenByIdPenjualanRetur($_GET['id']);
		}
		$this->data['dokumen'] = $dokumen;
		$this->data['barang'] = $this->model->getBarangByIdPenjualanRetur($_GET['id']);
		$this->view('penjualan-retur-form.php', $this->data);
	}
	
	public function invoice() {
		
		$id_penjualan = $_GET['id'];
		$invoice = $this->generatePdfInvoice($id_penjualan);

		$this->data['message']['status'] = 'ok';
		$this->data['id_order'] = $id_penjualan;

		if (!$invoice) {
			$this->data['message']['status'] = 'error';
			$this->data['message']['message'] = 'Data order tidak ditemukan';
		}
		exit;
	}
	
	// Penjualan
	public function getDataDTPenjualanRetur() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllPenjualanRetur();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListPenjualanRetur();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		$id_user = $this->session->get('user')['id_user'];
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		if ($query['data']) {
			foreach ($query['data'] as $key => &$val) 
			{
				$val['nama_customer'] = $val['nama_customer'] ?: '-';
				$exp = explode(' ', $val['tgl_penjualan']);
				$val['tgl_penjualan'] = '<div class="text-end">' . format_tanggal($exp[0]) . '</div>';
				$val['sub_total'] = '<div class="text-end">' . format_number($val['sub_total']) . '</div>';
				$val['neto_retur'] = '<div class="text-end">' . format_number($val['neto_retur']) . '</div>';
				$val['total_diskon_item_retur'] = '<div class="text-end">' . format_number($val['total_diskon_item_retur']) . '</div>';
				$val['kurang_bayar'] = '<div class="text-end">' . format_number($val['kurang_bayar']) . '</div>';
				
				$val['ignore_urut'] = $no;
				$val['ignore_action'] = '<div class="btn-action-group">' . 
					btn_link(['url' => base_url() . '/penjualan-retur/edit?id=' . $val['id_penjualan_retur'],'label' => 'Edit', 'icon' => 'fas fa-edit', 'attr' => ['target' => '_blank', 'class' => 'btn btn-success btn-xs me-1'] ]) . 
					btn_label(['label' => 'Delete', 'icon' => 'fas fa-times', 'attr' => ['class' => 'btn btn-danger btn-xs del-data', 'data-id' => $val['id_penjualan_retur'], 'data-delete-message' => 'Hapus data retur penjualan ?'] ]) . 
				'</div>';
				$no++;
			}
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
	public function getDataDTListInvoice() {
		echo view('themes/modern/penjualan-list-invoice.php', $this->data);
	}
	
	public function getDataDTInvoice() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllDataInvoice();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListDataINvoice();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		$id_user = $this->session->get('user')['id_user'];
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$stok_class = '';
			
			$val['no_invoice'] = '<span class="penjualan-detail">' . $val['no_invoice'] . '</span><span style="display:none" class="penjualan">' . json_encode($val) . '</span>';
			$val['ignore_urut'] = $no;
			
			// Pilih barang
			$val['ignore_pilih'] = btn_label(['label' => 'Pilih', 'attr' => ['data-id-penjualan' => $val['id_penjualan'],'class'=>'btn btn-success pilih-invoice btn-xs']]);
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
}
