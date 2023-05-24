<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022-2022
*/

namespace App\Controllers;
use App\Models\WilayahModel;

class Wilayah extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		$this->model = new WilayahModel;	
	}
	
	public function getPropinsi() 
	{
		$result = $this->model->getPropinsi();
		echo json_encode($result);
		exit;
	}
	
	public function ajaxGetKabupatenByIdPropinsi()
	{
		$result = [];
		if (!empty($_GET['id']) && is_numeric($_GET['id'])) {
			$result = $this->model->getKabupatenByIdPropinsi($_GET['id']);
		}
		
		echo json_encode($result);
		exit;
	}
	
	public function ajaxGetKecamatanByIdKabupaten()
	{
		$result = [];
		if (!empty($_GET['id']) && is_numeric($_GET['id'])) {
			$result = $this->model->getKecamatanByIdKabupaten($_GET['id']);
		}

		echo json_encode($result);
		exit;
	}
	
	public function ajaxGetKelurahanByIdKecamatan()
	{
		$result = [];
		if (!empty($_GET['id']) && is_numeric($_GET['id'])) {
			$result = $this->model->getKelurahanByIdKecamatan($_GET['id']);
		}

		echo json_encode($result);
		exit;
	}
	
	public function getDataWilayah($id_wilayah_kelurahan =null) {
		
		$model = new WilayahModel;
		
		if ($id_wilayah_kelurahan) {
			$data['id_wilayah_kelurahan'] = $id_wilayah_kelurahan;
		} else {
			if (!empty($this->config->idWilayahKelurahan)) {
				$data['id_wilayah_kelurahan'] = $this->config->idWilayahKelurahan;
			} else {
				$data['id_wilayah_kelurahan'] = '';
			}
		}
		
		$kecamatan = $this->model->getKecamatanByIdKelurahan($data['id_wilayah_kelurahan']);
		$data['id_wilayah_kecamatan'] = $kecamatan['id_wilayah_kecamatan'];
		
		$kabupaten = $this->model->getKabupatenByIdKecamatan($data['id_wilayah_kecamatan']);
		$data['id_wilayah_kabupaten'] = $kabupaten['id_wilayah_kabupaten'];
		
		$propinsi = $this->model->getPropinsiByIdKabupaten($data['id_wilayah_kabupaten']);
		$data['id_wilayah_propinsi'] = $propinsi['id_wilayah_propinsi'];
		
		$default_propinsi = set_value('id_wilayah_propinsi', $data['id_wilayah_propinsi']);
		$default_kabupaten = set_value('id_wilayah_kabupaten', $data['id_wilayah_kabupaten']);
		$default_kecamatan = set_value('id_wilayah_kecamatan', $data['id_wilayah_kecamatan']);
		
		$data['propinsi'] =  $model->getPropinsi();
		$data['kabupaten'] = $model->getKabupatenByIdPropinsi($default_propinsi);
		$data['kecamatan'] = $model->getKecamatanByIdKabupaten($default_kabupaten);
		$data['kelurahan'] = $model->getKelurahanByIdKecamatan($default_kecamatan);
		
		$data['default_propinsi'] = $data['id_wilayah_propinsi'];
		$data['default_kabupaten'] = $data['id_wilayah_kabupaten'];
		$data['default_kecamatan'] = $data['id_wilayah_kecamatan'];
		$data['default_kelurahan'] = $data['id_wilayah_kelurahan'];

		return $data;
	}
}
