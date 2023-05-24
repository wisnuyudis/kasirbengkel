<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022-2022
*/

namespace App\Models;

class SettingInvoiceModel extends \App\Models\BaseModel
{
	public function getSettingInvoice() {
		$sql = 'SELECT * FROM setting WHERE type = ?';
		$result = $this->db->query($sql, 'invoice')->getResultArray();
		return $result;
	}
	
	public function getSettingNotaRetur() {
		$sql = 'SELECT * FROM setting WHERE type = ?';
		$result = $this->db->query($sql, 'nota_retur')->getResultArray();
		return $result;
	}
	
	public function getSettingNotaTransfer() {
		$sql = 'SELECT * FROM setting WHERE type = ?';
		$result = $this->db->query($sql, 'nota_transfer')->getResultArray();
		return $result;
	}
	
	public function saveSetting() 
	{
		$result = [];
		
		$data_db[] = ['type' => 'invoice', 'param' => 'no_invoice', 'value' => $_POST['no_invoice']];
		$data_db[] = ['type' => 'invoice', 'param' => 'jml_digit', 'value' => $_POST['jml_digit_invoice']];
		$data_db[] = ['type' => 'invoice', 'param' => 'footer_text', 'value' => $_POST['footer_text']];
		$data_db[] = ['type' => 'nota_retur', 'param' => 'no_nota_retur', 'value' => $_POST['no_nota_retur']];
		$data_db[] = ['type' => 'nota_retur', 'param' => 'jml_digit', 'value' => $_POST['jml_digit_nota_retur']];
		$data_db[] = ['type' => 'nota_transfer', 'param' => 'no_nota_transfer', 'value' => $_POST['no_nota_transfer']];
		$data_db[] = ['type' => 'nota_transfer', 'param' => 'jml_digit', 'value' => $_POST['jml_digit_nota_transfer']];
		
		helper('upload_file');
		
		// Logo Login
		$error = false;
		$sql = 'SELECT * FROM setting WHERE type="invoice" AND param="logo"';
		$setting = $this->db->query($sql)->getRowArray();
		
		$logo_invoice_lama = $setting['value'];
		$path = ROOTPATH . 'public/images/';
		if ($_FILES['logo']['name']) 
		{
			//old file
			if ($logo_invoice_lama) {
				if (file_exists($path . $logo_invoice_lama)) {
					$unlink = delete_file($path . $logo_invoice_lama);
					if (!$unlink) {
						$result['status'] = 'error';
						$result['message'] = 'Gagal menghapus gambar lama';
						$error = true;
					}
				}
			}
			
			$filename = \upload_file($path, $_FILES['logo']);
			$data_db[] = ['type' => 'invoice', 'param' => 'logo', 'value' => $filename];
		} else {
			$data_db[] = ['type' => 'invoice', 'param' => 'logo', 'value' => $logo_invoice_lama];
		}
		
		if ($error) {
			return $result;
		}
		
		$this->db->transStart();
		$this->db->table('setting')->delete(['type' => 'invoice']);
		$this->db->table('setting')->delete(['type' => 'nota_retur']);
		$this->db->table('setting')->delete(['type' => 'nota_transfer']);
		$this->db->table('setting')->insertBatch($data_db);
		$this->db->transComplete();
		
		if ($this->db->transStatus()) {
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
		} else {
			$result['status'] = 'error';
			$result['message'] = 'Data gagal disimpan';
		}
		
		return $result;
	}
}
?>