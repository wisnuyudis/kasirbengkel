<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022-2022
*/

namespace App\Models;

class SettingPajakModel extends \App\Models\BaseModel
{
	public function getSettingPajak() {
		$sql = 'SELECT * FROM setting WHERE type = ?';
		$result = $this->db->query($sql, 'pajak')->getResultArray();
		return $result;
	}
	
	public function saveSetting() 
	{
		$result = [];
		
		$data_db[] = ['type' => 'pajak', 'param' => 'display_text', 'value' => $_POST['display_text']];
		$data_db[] = ['type' => 'pajak', 'param' => 'tarif', 'value' => $_POST['tarif']];
		$data_db[] = ['type' => 'pajak', 'param' => 'status', 'value' => $_POST['status']];
		
		
		$this->db->transStart();
		$this->db->table('setting')->delete(['type' => 'pajak']);
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