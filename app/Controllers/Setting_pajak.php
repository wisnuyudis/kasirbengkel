<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Controllers;
use App\Models\SettingPajakModel;

class Setting_pajak extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new SettingPajakModel;	
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
		
		$setting = $this->model->getSettingPajak();
		$setting_pajak = [];
		foreach ($setting as $val) {
			$setting_pajak[$val['param']] = $val['value'];
		}
		
		$this->data['setting_pajak'] = $setting_pajak;
		$this->view('setting-pajak-form.php', $this->data);
	}
	
	private function validateFormSetting() {
	
		$validation =  \Config\Services::validation();
		$validation->setRule('display_text', 'Display Text', 'trim|required|min_length[5]');
		$validation->withRequest($this->request)->run();
		$form_errors = $validation->getErrors();
		
		return $form_errors;
	}
}
