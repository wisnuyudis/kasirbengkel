<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022-2022
*/

namespace App\Models;

class KasModel extends \App\Models\BaseModel
{
	public function deleteData() 
	{
		$result = $this->db->table('gudang')->delete(['id_gudang' => $_POST['id']]);
		$gudang_default = $this->db->query('SELECT id_gudang FROM gudang WHERE default_gudang="Y"') ->getRowArray();
		if (!$gudang_default) {
			$sql = 'UPDATE gudang SET default_gudang = "Y" limit 1';
			$this->db->query($sql);
		}
		return $result;
	}
	
	public function getKasById($id) {
		$sql = 'SELECT * FROM kas WHERE id = ?';
		$result = $this->db->query($sql, $id)->getRowArray();
		return $result;
	}
	
	public function switchDefault() {
		$result = $this->db->query('SELECT COUNT(*) AS jml FROM gudang WHERE default_gudang="Y"') ->getRowArray();
		if ($result['jml'] == 1  && $_POST['default_gudang'] == 'N') {
			return ['status' => 'error', 'message' => 'Setidaknya ada satu gudang yang dipilih menjadi default'];
		}
		
		$this->db->transStart();
		$this->db->table('gudang')->update(['default_gudang' => 'N']);
		$this->db->table('gudang')->update(['default_gudang' => 'Y'], ['id_gudang' => $_POST['id']]);
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
	
	public function saveData() 
	{
		// $result = [];
		if ($_POST['id']) {
			$result = $this->db->query('SELECT id FROM kas') ->getRowArray();
			if ($result['id'] && $_POST['id'] == $result['id']) {
				return ['status' => 'error', 'message' => 'Setidaknya ada satu gudang yang dipilih menjadi default'];
			}
		}
		
		$this->db->transStart();
		
		
		$data_db['id_gudang'] = $_POST['id_gudang'];
		$data_db['nilai'] = $_POST['nilai'];
		$data_db['keterangan'] = $_POST['keterangan'];
		$data_db['date'] = $_POST['date'];
		$data_db['type'] = 'masuk';
		
		if ($_POST['id']) 
		{
			$this->db->table('kas')->update($data_db, ['id' => $_POST['id']]);	
		} else {
			$this->db->table('kas')->insert($data_db);
		}
		
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
	
	public function countAllData() {
		$sql = 'SELECT COUNT(*) AS jml FROM kas WHERE type LIKE "masuk"';
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListData() {

		$columns = $this->request->getPost('columns');

		// Search
		$where = ' WHERE type LIKE "masuk" ';
		$search_all1 = @$this->request->getPost('search')['value'];
		$search_all = str_replace(' ','%',$search_all1);
		if ($search_all) {
			foreach ($columns as $val) {
				
				if (strpos($val['data'], 'ignore_search') !== false) 
					continue;
				
				if (strpos($val['data'], 'ignore') !== false)
					continue;
				
				$where_col[] = $val['data'] . ' LIKE "%' . $search_all . '%"';
			}
			 $where .= ' AND (' . join(' OR ', $where_col) . ') ';
		}
		
		// Order		
		$order_data = $this->request->getPost('order');
		$order = '';
		if (@strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore_search') === false) {
			$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
			$order = ' ORDER BY ' . $order_by;
		}

		// Query Total Filtered
		$sql = 'SELECT COUNT(*) AS jml_data FROM kas LEFT JOIN gudang USING(id_gudang)
				' . $where;
		$total_filtered = $this->db->query($sql)->getRowArray()['jml_data'];
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = 'SELECT * FROM kas 
				LEFT JOIN gudang USING(id_gudang)
				' . $where . $order  . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}
?>