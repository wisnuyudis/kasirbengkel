<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Models;

class SupplierModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function deleteData() {
		$result = $this->db->table('supplier')->delete(['id_supplier' => $_POST['id']]);
		return $result;
	}
	
	public function getBarangSupplierById($id) {
		$sql = 'SELECT * FROM supplier 
				LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan) 
				LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
				LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)
				WHERE id_supplier = ?';
		$result = $this->db->query($sql, trim($id))->getRowArray();
		return $result;
	}
	
	public function saveData() {
			
		$data_db['nama_supplier'] = $_POST['nama_supplier'];
		$data_db['alamat_supplier'] = $_POST['alamat_supplier'];
		$data_db['no_telp'] = $_POST['no_telp'];
		$data_db['id_wilayah_kelurahan'] = $_POST['id_wilayah_kelurahan'];
		
		if ($_POST['id']) 
		{
			$query = $this->db->table('supplier')->update($data_db, ['id_supplier' => $_POST['id']]);
		} else {
			$query = $this->db->table('supplier')->insert($data_db);
		}
		
		if ($query) {
			$result['msg']['status'] = 'ok';
			$result['msg']['content'] = 'Data berhasil disimpan';
		} else {
			$result['msg']['status'] = 'error';
			$result['msg']['content'] = 'Data gagal disimpan';
		}
		
		return $result;
	}
	
	public function countAllData($where) {
		$sql = 'SELECT COUNT(*) AS jml FROM supplier' . $where;
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListData($where) {

		$columns = $this->request->getPost('columns');

		// Search
		$search_all1 = @$this->request->getPost('search')['value'];
		$search_all = str_replace(' ','%',$search_all1);
		if ($search_all) {
			// Additional Search

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
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		
		$order_data = $this->request->getPost('order');
		$order = '';
		
		if (!empty($_POST) && strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore_search') === false) {
			$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
			$order = 'ORDER BY ' . $order_by . ' LIMIT ' . $start . ', ' . $length;
		}

		// Query Total Filtered
		// $sql = 'SELECT COUNT(*) AS jml_data FROM dokter ' . $where;
		$sql = 'SELECT COUNT(*) AS jml_data FROM supplier 
				LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan) 
				LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
				LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)
				' . $where;
				
		$query = $this->db->query($sql)->getRowArray();
		$total_filtered = $query['jml_data'];
							
		
		// Query Data
		$sql = 'SELECT * FROM supplier 
				LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan) 
				LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
				LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)
				' . $where . $order;
		
		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}
?>