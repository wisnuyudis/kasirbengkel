<?php

/**
 *	App Name	: Aplikasi Kasir Berbasis Web	
 *	Developed by: Agus Prawoto Hadi
 *	Website		: https://jagowebdev.com
 *	Year		: 2022
 */

namespace App\Controllers;

use App\Models\PosKasirModel;

class Pos_kasir extends \App\Controllers\BaseController
{
	public function __construct()
	{

		parent::__construct();

		$this->model = new PosKasirModel;
		$this->data['title'] = 'Kasir';
	}

	public function index()
	{

		$this->addJs($this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal-loader.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal-fapicker.css');

		$this->addJs($this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css');
		$this->addJs($this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');
		$this->addJs($this->config->baseURL . 'public/themes/modern/js/wilayah.js');
		$this->addStyle($this->config->baseURL . 'public/themes/modern/css/pos-kasir.css');

		$this->addJs($this->config->baseURL . 'public/themes/modern/js/pos-kasir.js');

		$this->data['cekrole'] = $this->model->cekrole();

		$result = $this->model->getAllGudang();
		$id_gudang_selected = '';
		foreach ($result as $val) {
			$gudang[$val['id_gudang']] = $val['nama_gudang'];
			if ($val['default_gudang'] == 'Y') {
				$id_gudang_selected = $val['id_gudang'];
			}
		}
		$this->data['gudang'] = $gudang;
		$this->data['id_gudang_selected'] = $id_gudang_selected;

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

		$result = $this->model->getSettingPajak();
		foreach ($result as $val) {
			$pajak[$val['param']] = $val['value'];
		}
		$this->data['pajak'] = $pajak;

		echo view('themes/modern/pos-kasir-form.php', $this->data);
	}

	private function validateFormSetting()
	{

		$validation =  \Config\Services::validation();
		$validation->setRule('no_invoice', 'Nama Setting', 'trim|required|min_length[5]');
		$validation->setRule('no_nota_retur', 'Nama Setting', 'trim|required|min_length[5]');
		$validation->withRequest($this->request)->run();
		$form_errors = $validation->getErrors();

		return $form_errors;
	}

	public function ajaxSaveData()
	{
		$model = new \App\Models\PenjualanModel;
		$result = $model->saveData();
		// echo json_encode($result);
		// echo '<pre>';
		// print_r($_POST); die;
		// $result['status'] = 'ok';
		// $result['message'] = 'Data berhasil disimpan';
		echo json_encode($result);
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

		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) {

			$val['stok'] = key_exists($_GET['id_gudang'], $val['list_stok']) ? $val['list_stok'][$_GET['id_gudang']] : 0;
			// if ($val['stok'] <= 0 || $val['stok'] == '' || $val['stok'] == null) {
			// 	$val['stok'] = 0;
			// } else {
			// 	$val['stok'] = $val['stok'];
			// }
			$val['nama_barang'] = '<div style="min-width:150px">' . $val['nama_barang'] . '<span class="detail-barang" style="display:none">' . json_encode($val) . '</span>
									<div class="list-barang-detail"><small class="rounded badge-clear-success">Stok: <span class="">' . $val['stok'] . '</small></div></div>';
			$val['ignore_harga'] = '<div class="text-end text-nowrap" style="color:blue"><small class="rounded badge-clear-success">Rp. ' . format_number($val['harga']) . '</small></div>';
			$val['ignore_hargasatu'] = '<div class="text-end text-nowrap">Rp. ' . format_number($val['hargasatu']) . '</div>';
			$val['ignore_hargadua'] = '<div class="text-end text-nowrap">Rp. ' . format_number($val['hargadua']) . '</div>';
			$val['ignore_hargatiga'] = '<div class="text-end text-nowrap">Rp. ' . format_number($val['hargatiga']) . '</div>';
			$val['ignore_hargaempat'] = '<div class="text-end text-nowrap">Rp. ' . format_number($val['hargaempat']) . '</div>';
			$val['ignore_hargalima'] = '<div class="text-end text-nowrap">Rp. ' . format_number($val['hargalima']) . '</div>';
			// $val['stok'] = '<div class="text-end">' . format_number($val['stok']) . '</div>';
			$val['ignore_urut'] = $no;
			/* $val['ignore_foto'] = ''; */
			// if ($val['meta_file']) 
			// {
			// 	$meta_file = json_decode($val['meta_file'], true);
			// 	$thumbnail = key_exists('thumbnail', $meta_file) ? $meta_file['thumbnail']['small']['filename'] : $val['nama_file'];
			// 	$val['ignore_foto'] = '<div style="width:64px"><img src="' . base_url() . '/public/files/uploads/' . $thumbnail . '"/></div>';
			// }
			$no++;
		}

		$result['data'] = $query['data'];
		echo json_encode($result);
		exit();
	}

	function telo()
	{
		$this->model->cekrole();
	}
}
