<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Controllers;
use App\Models\SettingInvoiceModel;

class Setting_invoice extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new SettingInvoiceModel;	
		$this->data['title'] = 'Setting Invoice';
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/image-upload.js');
		
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
		
		$setting = $this->model->getSettingInvoice();
		$setting_invoice = [];
		foreach ($setting as $val) {
			$setting_invoice[$val['param']] = $val['value'];
		}
		
		$setting = $this->model->getSettingNotaRetur();
		$setting_nota_retur = [];
		foreach ($setting as $val) {
			$setting_nota_retur[$val['param']] = $val['value'];
		}
		
		$setting = $this->model->getSettingNotaTransfer();
		$setting_nota_transfer = [];
		foreach ($setting as $val) {
			$setting_nota_transfer[$val['param']] = $val['value'];
		}

		$this->data['setting_invoice'] = $setting_invoice;
		$this->data['setting_nota_retur'] = $setting_nota_retur;
		$this->data['setting_nota_transfer'] = $setting_nota_transfer;
		$this->view('setting-invoice-form.php', $this->data);
	}
	
	private function validateFormSetting() {
	
		$validation =  \Config\Services::validation();
		$validation->setRule('no_invoice', 'No. Invoice', 'trim|required|min_length[5]');
		$validation->setRule('no_nota_retur', 'No. Nota Retur', 'trim|required|min_length[5]');
		$validation->withRequest($this->request)->run();
		$form_errors = $validation->getErrors();
		
		return $form_errors;
	}
}
