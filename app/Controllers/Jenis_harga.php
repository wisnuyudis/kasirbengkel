<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Controllers;
use App\Models\JenisHargaModel;

class Jenis_harga extends \App\Controllers\BaseController
{
	protected $model;
	
	public function __construct() {
		
		parent::__construct();
		$this->model = new JenisHargaModel;
		$this->data['site_title'] = 'Jenis Harga';
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/jenis-harga.js');
	}
	
	public function index()
	{
		$this->hasPermission('read_all');
		$this->view('jenis-harga-result.php', $this->data);
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
			$val['ignore_urut'] = $no;
			$val['ignore_action'] = '<div class="form-inline btn-action-group">'
										. btn_label(
												['icon' => 'fas fa-edit'
													, 'attr' => ['class' => 'btn btn-success btn-edit btn-xs me-1', 'data-id' => $val['id_jenis_harga']]
													, 'label' => 'Edit'
												])
										. btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete btn-xs'
																	, 'data-id' => $val['id_jenis_harga']
																	, 'data-delete-title' => 'Hapus data jenis harga : <strong>' . $val['nama_jenis_harga'] . '</strong>'
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