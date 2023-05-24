<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Models;

class PenjualanReturModel extends \App\Models\BaseModel
{

	public function __construct() {
		parent::__construct();
	}
	
	public function deleteData($id) 
	{
		$this->db->transStart();
		$this->db->table('penjualan_retur')->delete(['id_penjualan_retur' => $id]);
		$this->db->table('penjualan_retur_detail')->delete(['id_penjualan_retur' => $id]);
		$this->db->table('penjualan_retur_dokumen')->delete(['id_penjualan_retur' => $id]);
		$this->db->transComplete();
		return $this->db->transStatus();
	}
	
	public function getPenjualanReturById($id) {
		
		$sql = 'SELECT * FROM penjualan_retur 
				LEFT JOIN penjualan USING(id_penjualan)
				LEFT JOIN customer USING(id_customer) WHERE id_penjualan_retur = ?';
		$result = $this->db->query($sql, $id)->getRowArray();
		return $result;
	}
	
	public function getBarangByIdPenjualanRetur($id) {
		$sql = 'SELECT *
				FROM penjualan_retur_detail
				LEFT JOIN penjualan_detail USING(id_penjualan_detail)
				LEFT JOIN barang USING(id_barang)
				WHERE id_penjualan_retur = ?';
				
		$result = $this->db->query($sql, $id)->getResultArray();
		return $result;
	}
	
	public function getDokumenByIdPenjualanRetur($id) {
		$sql = 'SELECT *
				FROM penjualan_retur_dokumen
				LEFT JOIN file_picker USING(id_file_picker)
				WHERE id_penjualan_retur = ?';
				
		$result = $this->db->query($sql, $id)->getResultArray();
		return $result;
	}
	
	public function getAllGudang() {
		$sql = 'SELECT * FROM gudang';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getPenjualanDetail($id_penjualan) 
	{
		// Order
		$result['order'] = $this->db->query('SELECT * 
									FROM penjualan
									WHERE id_penjualan = ?' 
									, $id_penjualan
								)
							->getRowArray();
		
		if (!$result['order']) {
			return false;
		}
		
		// Produk
		$result['detail'] = $this->db->query('SELECT * 
									FROM `penjualan_detail`
									LEFT JOIN barang USING(id_barang)
									WHERE id_penjualan = ?' 
									, $id_penjualan
								)
							->getResultArray();
		
		// Customer
		$data = [];
		if ($result['order']['id_customer']) {
			$sql = 'SELECT * FROM customer LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan)
					LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
					LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
					LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi) 
					WHERE id_customer = ' . $result['order']['id_customer'];
			$result['customer'] = $this->db->query($sql)->getRowArray();
		} else {
			$result['customer'] = ['nama_customer' => 'Tamu', 'alamat_customer' => '-'];
		}
		
		// Bayar
		$data = [];
		if ($result['order']) {
			$sql = 'SELECT * FROM penjualan_bayar WHERE id_penjualan = ' . $id_penjualan;
			$result['bayar'] = $this->db->query($sql)->getResultArray();
			
		}

		return $result;
	}
	
	public function saveData() 
	{	
		// barang
		$sub_total = 0;
		$total_diskon = 0;
		$total_qty = 0;
		
		// echo '<pre>'; print_r($_POST['id_barang']); die;
		$data_db_barang = [];
		foreach ($_POST['id_penjualan_detail'] as $key => $id_penjualan_detail) 
		{
			$sql = 'SELECT * FROM penjualan_detail WHERE id_penjualan_detail = ?';
			$query_barang = $this->db->query($sql, $id_penjualan_detail)->getRowArray();
			
			$harga_satuan = $query_barang['harga_satuan'];
			$qty = str_replace(['.'], '', $_POST['qty_barang_retur'][$key]);
			$harga_barang_retur =  $harga_satuan * $qty;
			
			$diskon_nilai = str_replace(['.'], '', $_POST['diskon_barang'][$key]);
			$diskon_jenis = $_POST['diskon_barang_jenis'][$key];
			$diskon_harga = 0;
			if ($diskon_nilai) {
				$diskon_harga = $diskon_nilai;
				if ($diskon_jenis == '%') {
					$diskon_harga = round($harga_barang_retur * $diskon_nilai / 100);
				}
				
				$harga_barang_retur = $harga_barang_retur - $diskon_harga;
				$total_diskon += $diskon_harga;
			}
			$total_qty += $qty;
			
			$data_db_barang[$key]['id_penjualan_detail'] = $id_penjualan_detail;
			$data_db_barang[$key]['qty_retur'] = $_POST['qty_barang_retur'][$key];
			$data_db_barang[$key]['harga_total_retur'] = $harga_satuan * $qty;
			$data_db_barang[$key]['diskon_jenis_retur'] = $diskon_jenis;
			$data_db_barang[$key]['diskon_nilai_retur'] = $diskon_nilai;
			$data_db_barang[$key]['diskon_retur'] = $diskon_harga;
			$data_db_barang[$key]['harga_neto_retur'] = $harga_barang_retur;
			
			$sub_total += $harga_barang_retur;
		}
		
		$this->db->transStart();
		
		// Save table penjualan_retur
		$data_db['id_penjualan'] = $_POST['id_penjualan'];
		$data_db['no_nota_retur'] = $_POST['no_nota_retur'];
		$exp = explode('-', $_POST['tgl_nota_retur']);
		$data_db['tgl_nota_retur'] = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		$data_db['sub_total_retur'] = $sub_total;
		$data_db['total_diskon_item_retur'] = $total_diskon;
		$data_db['total_qty_retur'] = $total_qty;
		
		
		$diskon_total_jenis = $_POST['diskon_total_jenis'];
		$diskon_total_nilai = str_replace(['.'], '', $_POST['diskon_total_nilai']);
		if ($diskon_total_nilai) {
			if ($diskon_total_jenis == '%') {
				$sub_total = $sub_total - round($sub_total * $diskon_total_nilai / 100);
			} else {
				$sub_total = $sub_total - $diskon_total_nilai;
			}
		}
		
		$data_db['diskon_jenis_retur'] = $diskon_total_jenis;
		$data_db['diskon_nilai_retur'] = $diskon_total_nilai;
		
		/* $operator = '';
		if ($_POST['penyesuaian_operator'] == '-') {
			$operator = '-';
		} */
		$data_db['penyesuaian_retur'] = str_replace('.', '', $_POST['penyesuaian_nilai']);
		$neto_retur = $sub_total + $data_db['penyesuaian_retur'];
		if ($neto_retur < 0) {
			$neto_retur = 0;
		}
		$data_db['neto_retur'] = $neto_retur;
				
		// Save table penjualan_barang, penjualan_layanan		
		if ($_POST['id']) 
		{
			$data_db['id_user_update'] = $_SESSION['user']['id_user'];
			$data_db['tgl_update'] = date('Y-m-d H:i:s');
			$query = $this->db->table('penjualan_retur')->update($data_db, ['id_penjualan_retur' => $_POST['id']]);
			$this->db->table('penjualan_retur_detail')->delete(['id_penjualan_retur' => $_POST['id']]);
			
			$id_penjualan_retur = $_POST['id'];
		} else {
			$data_db['id_user_input'] = $_SESSION['user']['id_user'];
			$query = $this->db->table('penjualan_retur')->insert($data_db);
			$id_penjualan_retur = $this->db->insertID();
		}
		
		// Dokumen
		if ($_POST['id']) {
			$this->db->table('penjualan_retur_dokumen')->delete(['id_penjualan_retur' => $id_penjualan_retur]);
		}
		
		$data_db = [];
		foreach ($_POST['id_file_picker'] as $index => $val) {
			$data_db[] = ['id_file_picker' => $val, 'id_penjualan_retur' => $id_penjualan_retur, 'urut' => ($index + 1) ];
		}
		
		$this->db->table('penjualan_retur_dokumen')->insertBatch($data_db);
		
		// Save tabel penjualan_detail
		foreach ($data_db_barang as &$val) {
			$val['id_penjualan_retur'] = $id_penjualan_retur;
		}
		
		$this->db->table('penjualan_retur_detail')->insertBatch($data_db_barang);
		
		$this->db->transComplete();
		
		if ($this->db->transStatus() === false ) {
			$result['status'] = 'error';
			$result['content'] = 'Data gagal disimpan';
		} else {
			$sql = 'SELECT * FROM penjualan_retur WHERE id_penjualan_retur = ?';
			$data = $this->db->query($sql, $id_penjualan_retur)->getRowArray();
			$result['status'] = 'ok';
			$result['content'] = 'Data berhasil disimpan';
			$result['id_penjualan_retur'] = $id_penjualan_retur;
			$result['penjualan_retur'] = $data;
		}
		
		return $result;
	}
	

	public function countAllPenjualanRetur() {
		$sql = 'SELECT COUNT(*) AS jml FROM penjualan_retur AS tabel';
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListPenjualanRetur() 
	{
		$columns = $this->request->getPost('columns');

		// Search
		$where = ' WHERE 1=1 ';
		$search_all = @$this->request->getPost('search')['value'];
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
		
		// Query Total Filtered
		$sql = 'SELECT COUNT(*) AS jml_data FROM penjualan_retur
				LEFT JOIN penjualan USING(id_penjualan)
				LEFT JOIN customer USING(id_customer)
				' . $where;
				
		$query = $this->db->query($sql)->getRowArray();
		$total_filtered = $query['jml_data'];
		
		
		// Order
		$order_data = $this->request->getPost('order');
		$order = '';
		if (strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore_search') === false) {
			$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
			$order = ' ORDER BY ' . $order_by;
		}

		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		
		// Query Data
		$sql = 'SELECT * FROM penjualan_retur
				LEFT JOIN penjualan USING(id_penjualan)
				LEFT JOIN customer USING(id_customer)
				' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
		
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
	
	// List Barang
	public function countAllDataInvoice () {
		$sql = 'SELECT COUNT(*) AS jml FROM penjualan ';
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListDataInvoice() {

		$columns = $this->request->getPost('columns');

		// Search
		$search_all = @$this->request->getPost('search')['value'];
		$where = ' WHERE 1 = 1 ';
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
		if (!empty($_POST)) {
			if (strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore') === false) {
				$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
				$order = ' ORDER BY ' . $order_by;
			}
		}

		// Query Total Filtered
		$sql = 'SELECT COUNT(*) AS jml FROM penjualan LEFT JOIN customer USING(id_customer)' . $where;
		$result = $this->db->query($sql)->getRowArray();
		$total_filtered = $result['jml'];
		
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = 'SELECT * FROM penjualan LEFT JOIN customer USING(id_customer)' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
		
		// List item
		$id_penjualan = [];
		foreach ($data as $val) {
			$id_penjualan[] = $val['id_penjualan'];
		}
		
		if ($id_penjualan) {
		
			$sql = 'SELECT *
					FROM penjualan_detail 
					LEFT JOIN barang USING(id_barang)
					WHERE id_penjualan IN (' . join(',', $id_penjualan) . ')';
			
			$result = $this->db->query($sql)->getResultArray();
			$penjualan_detail = [];
			foreach ($result as $val) {
				$penjualan_detail[$val['id_penjualan']][] = $val;
			}
				
			// Merge
			foreach ($data as &$val) {
				$val['detail']= $penjualan_detail[$val['id_penjualan']];
			}
		}
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}
?>