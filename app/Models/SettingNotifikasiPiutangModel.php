<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022-2022
*/

namespace App\Models;

class SettingNotifikasiPiutangModel extends \App\Models\BaseModel
{
	public function getSettingNotifikasiPiutang() {
		$sql = 'SELECT * FROM setting WHERE type = ?';
		$result = $this->db->query($sql, 'piutang')->getResultArray();
		return $result;
	}
	
	public function saveSetting() 
	{
		$result = [];
		
		$data_db[] = ['type' => 'piutang', 'param' => 'notifikasi_periode', 'value' => $_POST['notifikasi_periode']];
		$data_db[] = ['type' => 'piutang', 'param' => 'notifikasi_show', 'value' => $_POST['notifikasi_show']];
		$data_db[] = ['type' => 'piutang', 'param' => 'piutang_periode', 'value' => $_POST['piutang_periode']];
		
		
		$this->db->transStart();
		$this->db->table('setting')->delete(['type' => 'piutang']);
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