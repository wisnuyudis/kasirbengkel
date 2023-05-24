<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Controllers;
use App\Models\SupplierModel;
use App\Models\WilayahModel;

class Supplier extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new SupplierModel;	
		$this->data['site_title'] = 'Data Supplier';
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/bootstrap-datepicker/js/bootstrap-datepicker.js' );
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/date-picker.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/image-upload.js');
		// $this->addJs ( $this->config->baseURL . 'public/vendors/datatables/datatables.min.js');
		// $this->addStyle ( $this->config->baseURL . 'public/vendors/datatables/datatables.min.css');
		// $this->addJs ( $this->config->baseURL . 'public/themes/modern/js/data-tables-ajax.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/supplier.js');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/bootstrap-datepicker/css/bootstrap-datepicker3.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		
		$data = $this->data;
		if (!empty($_POST['delete'])) 
		{
			$this->hasPermissionPrefix('delete', 'supplier');
			
			$result = $this->model->deleteData();
			// $result = true;
			if ($result) {
				$data['msg'] = ['status' => 'ok', 'message' => 'Data supplier berhasil dihapus'];
			} else {
				$data['msg'] = ['status' => 'error', 'message' => 'Data supplier gagal dihapus'];
			}
		}
		$this->view('supplier-result.php', $data);
	}
	
	private function setEdit() {
		$this->data['title'] = 'Edit Data Supplier Barang';
		$this->data['breadcrumb']['Edit'] = '';
		unset($this->data['breadcrumb']['Add']);
	}
	
	public function add() 
	{
		$wilayah = new \App\Controllers\Wilayah();
		$data_wilayah = $wilayah->getDataWilayah();
		$this->data = array_merge($this->data, $data_wilayah);
		
		if (!empty($_POST['id'])) {
			$this->setEdit();
		} else {

			$this->data['title'] = 'Tambah Data Supplier Barang';
			$this->data['breadcrumb']['Add'] = '';
		}

		$this->data['message'] = [];
		if (isset($_POST['submit'])) 
		{
			$form_errors = $this->validateForm();
							
			if ($form_errors) {
				$this->data['message']['status'] = 'error';
				$this->data['message']['content'] = $form_errors;
			} else {
				
				$message = $this->model->saveData();
				$this->data = array_merge($this->data, $message);
				$this->setEdit();
			}
		}
	
		$this->view('supplier-form.php', $this->data);
	}
	
	public function edit()
	{
		$this->hasPermissionPrefix('update', 'barang_supplier');
		
		$this->data['title'] = 'Edit ' . $this->currentModule['judul_module'];
		$this->data['breadcrumb']['Edit'] = '';
		
		if (empty($_GET['id'])) {
			$this->errorDataNotFound();
		}
		
		$this->data['message'] = [];
		if (isset($_POST['submit'])) 
		{
			$form_errors = $this->validateForm();
							
			if ($form_errors) {
				$this->data['message']['status'] = 'error';
				$this->data['message']['content'] = $form_errors;
			} else {
				
				// $query = false;
				$message = $this->model->saveData();
				$this->data = array_merge($this->data, $message);
			}
		}
		
		$id = !empty($_POST['id']) ? $_POST['id'] : $_GET['id'];
		$this->data['id'] = $id;
		$this->data['result'] = $this->model->getBarangSupplierById($id);
		
		if (empty($this->data['result'])) {
			$this->errorDataNotFound();
		}
		// print_r($this->data['result']); die;
		$wilayah = new \App\Controllers\Wilayah();
		$data_wilayah = $wilayah->getDataWilayah($this->data['result']['id_wilayah_kelurahan']);
		$this->data = array_merge($this->data, $data_wilayah);
		
		$this->view('supplier-form.php', $this->data);
	}
	
	private function validateForm($check_unique = false) {
	
		$validation =  \Config\Services::validation();
		$validation->setRule('nama_supplier', 'Nama Supplier', 'trim|required');
		$validation->setRule('alamat_supplier', 'Alamat Supplier', 'trim|required');
		$validation->withRequest($this->request)->run();
		$form_errors = $validation->getErrors();
		
		return $form_errors;
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
			$val['ignore_search_urut'] = $no;
			$val['ignore_search_action'] = btn_action([
									'edit' => ['url' => $this->config->baseURL . $this->currentModule['nama_module'] . '/edit?id='. $val['id_supplier']]
								, 'delete' => ['url' => ''
												, 'id' =>  $val['id_supplier']
												, 'delete-title' => 'Hapus data supplier: <strong>'.$val['nama_supplier'].'</strong> ?'
											]
							]);
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
}
