<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Controllers;
use App\Models\BarcodeCetakModel;

class Barcode_cetak extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new BarcodeCetakModel;	
		$this->data['site_title'] = 'Cetak Barcode';
				
		$this->addJs ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal.js');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal.css');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal-loader.css');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal-fapicker.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jsbarcode/JsBarcode.all.min.js');
		$this->addJs ( $this->config->baseURL . 'public/vendors/jspdf/jspdf.umd.js');
		$this->addJs ( $this->config->baseURL . 'public/vendors/docxjs/index.js');
		$this->addJs ( $this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');
		$this->addJs ( $this->config->baseURL . 'public/vendors/printjs/print.min.js');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/printjs/print.min.css');
		// $this->addJs ( 'https://unpkg.com/docx@7.4.0/build/index.js' );
		
		$this->addStyle ( $this->config->baseURL . 'public/themes/modern/css/modal-pilih-barang.css');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/barcode-cetak.js');
	}
	
	public function index()
	{
		$this->data['title'] = 'Cetak Barcode';
		$this->view('barcode-cetak-form.php', $this->data);
	}
	
	public function getDataDTListBarang() {
		echo view('themes/modern/barcode-cetak-list-barang.php', $this->data);
	}
	
	public function getDataDTBarang() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllDataBarang();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListDataBarang();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		$id_user = $this->session->get('user')['id_user'];
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			
			$val['nama_barang'] = '<span class="nama-barang">' . $val['nama_barang'] . '</span><span style="display:none" class="detail-barang">' . json_encode($val);
			$val['ignore_urut'] = $no;
	
			// Pilih barang
			$val['ignore_pilih'] = btn_label(['label' => 'Pilih', 'attr' => ['data-id-barang' => $val['id_barang'],'class'=>'btn btn-success pilih-barang btn-xs']]);
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
}