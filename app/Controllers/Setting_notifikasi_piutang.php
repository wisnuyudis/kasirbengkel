<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Controllers;
use App\Models\SettingNotifikasiPiutangModel;

class Setting_notifikasi_piutang extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new SettingNotifikasiPiutangModel;	
		$this->data['title'] = 'Setting Pajak';
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/setting-pajak.js');
		
		helper(['cookie', 'form']);
	}
	
	public function index() {
		
		if (!empty($_POST['submit'])) {
			$error = $this->validateFormSetting();
			if ($error) {
				$this->data['message'] = ['status' => 'error', 'message' => $error];
			} else {
				$message = $this->model->saveSetting();
				$this->data['message'] = $message;
			}
		}
		
		$setting = $this->model->getSettingNotifikasiPiutang();
		$setting_notifikasi = [];
		foreach ($setting as $val) {
			$setting_notifikasi[$val['param']] = $val['value'];
		}
		
		$this->data['setting_notifikasi'] = $setting_notifikasi;
		$this->view('setting-notifikasi-piutang-form.php', $this->data);
	}
	
	private function validateFormSetting() {
	
		$validation =  \Config\Services::validation();
		$validation->setRule('notifikasi_show', 'Tampilkan Notifikasi', 'trim|required');
		$validation->withRequest($this->request)->run();
		$form_errors = $validation->getErrors();
		
		return $form_errors;
	}
}
