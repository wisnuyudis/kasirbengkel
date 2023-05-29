<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Controllers;
use App\Models\KasModel;
use App\Models\PenjualanModel;

class Kas_masuk extends \App\Controllers\BaseController
{
	protected $model;
	protected $modelpenjualan;
	
	public function __construct() {
		
		parent::__construct();
		$this->model = new KasModel;
		$this->modelpenjualan = new PenjualanModel;
		$this->data['site_title'] = 'Kas Masuk';
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/wilayah.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/gudang.js');
	}
	
	public function index()
	{
		$this->hasPermission('read_all');
		$this->view('kas-masuk.php', $this->data);
	}
	
	public function ajaxDeleteData() {

		$delete = $this->model->deleteData();
		if ($delete) {
			$message['status'] = 'ok';
			$message['message'] = 'Data berhasil dihapus';
		} else {
			$message['status'] = 'error';
			$message['message'] = 'Data gagal dihapus';
		}
		echo json_encode($message);
	}
	
	public function ajaxGetFormData() {
		$this->data['kas'] = [];
		if (isset($_GET['id'])) {
			if ($_GET['id']) {
				$this->data['kas'] = $this->model->getKasById($_GET['id']);
				if (!$this->data['kas'])
					return;
			}
		}
		
		$this->data = array_merge($this->data, $this->setData());
		echo view('themes/modern/kas-masuk-form.php', $this->data);
	}

	private function setData() 
	{
		$result = $this->modelpenjualan->getAllGudang();
		foreach ($result as $val) {
			$gudang[$val['id_gudang']] = $val['nama_gudang'];
		}
		
		$result = $this->modelpenjualan->getJenisHarga();
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
	
	public function ajaxUpdateData() {

		$message = $this->model->saveData();
		echo json_encode($message);
	}
	
	public function ajaxSwitchDefault() {
		$result = $this->model->switchDefault();
		echo json_encode($result);
	}
		
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
			$checked = $val['default_gudang'] == 'Y' ? 'checked' : '';
			$val['ignore_search_urut'] = $no;
			$val['default_gudang'] = '<div class="form-switch text-center">
								<input name="aktif" type="checkbox" class="form-check-input switch" data-id-gudang="' . $val['id_gudang'] . '" ' . $checked . '>
							</div>';
			$val['ignore_search_action'] = '<div class="form-inline btn-action-group">'
										. btn_label(
												['icon' => 'fas fa-edit'
													, 'attr' => ['class' => 'btn btn-success btn-edit btn-xs me-1', 'data-id' => $val['id_gudang']]
													, 'label' => 'Edit'
												])
										. btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete btn-xs'
																	, 'data-id' => $val['id_gudang']
																	, 'data-delete-title' => 'Hapus nama gudang : <strong>' . $val['nama_gudang'] . '</strong>'
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