<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Models;

class PembelianReturModel extends \App\Models\BaseModel
{

	public function __construct() {
		parent::__construct();
	}
	
	public function deleteData($id) 
	{
		$this->db->transStart();
		$this->db->table('pembelian_retur')->delete(['id_pembelian_retur' => $id]);
		$this->db->table('pembelian_retur_detail')->delete(['id_pembelian_retur' => $id]);
		$this->db->transComplete();
		return $this->db->transStatus();
	}
	
	public function getIdentitas() {
		$sql = 'SELECT * FROM identitas 
				LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan)
				LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
				LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)';
		return $this->db->query($sql)->getRowArray();
	}
	
	public function getSettingInvoice() 
	{
		$sql = 'SELECT * FROM setting WHERE type = ?';
		$data = $this->db->query($sql, ['invoice'])->getResultArray();
		$result = [];
		foreach ($data as $val) { 
			$result[$val['param']] = $val['value'];
		}
		
		$sql = 'SELECT * FROM setting WHERE type = ? AND param = ?';
		$data = $this->db->query($sql, ['invoice', 'logo'])->getRowArray();
		$result['logo'] = $data['value'];
		
		$sql = 'SELECT * FROM setting WHERE type = ? AND param = ?';
		$data = $this->db->query($sql, ['invoice', 'footer_text'])->getRowArray();
		$result['footer_text'] = $data['value'];
		
		return $result;
	}
	
	public function getPembelianReturDetail($id_pembelian_retur) 
	{
		// Data
		$result['data'] = $this->db->query('SELECT * 
									FROM pembelian_retur
									LEFT JOIN pembelian USING(id_pembelian)
									WHERE id_pembelian_retur = ?' 
									, $id_pembelian_retur
								)
							->getRowArray();
		
		if (!$result['data']) {
			return false;
		}
		
		// Produk
		$result['detail'] = $this->db->query('SELECT * 
									FROM pembelian_retur_detail
									LEFT JOIN pembelian_detail USING(id_pembelian_detail)
									LEFT JOIN barang USING(id_barang)
									LEFT JOIN satuan_unit USING(id_satuan_unit)
									WHERE id_pembelian_retur = ?' 
									, $id_pembelian_retur
								)
							->getResultArray();
		
		// Supplier
		$data = [];
		if ($result['data']['id_supplier']) {
			$sql = 'SELECT * FROM supplier LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan)
					LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
					LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
					LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi) 
					WHERE id_supplier = ' . $result['data']['id_supplier'];
			$result['supplier'] = $this->db->query($sql)->getRowArray();
		} else {
			$result['supplier'] = ['nama_supplier' => '-', 'alamat_supplier' => '-'];
		}
		
		return $result;
	}
	
	public function getPembelianReturById($id) {
		
		$sql = 'SELECT * FROM pembelian_retur 
				LEFT JOIN pembelian USING(id_pembelian)
				LEFT JOIN supplier USING(id_supplier) WHERE id_pembelian_retur = ?';
		$result = $this->db->query($sql, $id)->getRowArray();
		return $result;
	}
	
	public function getBarangByIdPembelianRetur($id) {
		$sql = 'SELECT *
				FROM pembelian_retur_detail 
				LEFT JOIN pembelian_detail USING(id_pembelian_detail)
				LEFT JOIN barang USING (id_barang)
				LEFT JOIN satuan_unit USING(id_satuan_unit)
				WHERE id_pembelian_retur = ?';
				
		$result = $this->db->query($sql, $id)->getResultArray();
		return $result;
	}
	
	public function getAllGudang() {
		$sql = 'SELECT * FROM gudang';
		$result = $this->db->query($sql)->getResultArray();
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
		foreach ($_POST['id_pembelian_detail'] as $key => $id_pembelian_detail) 
		{
			$sql = 'SELECT * FROM pembelian_detail WHERE id_pembelian_detail = ?';
			$query_barang = $this->db->query($sql, $id_pembelian_detail)->getRowArray();
			
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
			
			$data_db_barang[$key]['id_pembelian_detail'] = $id_pembelian_detail;
			$data_db_barang[$key]['qty_retur'] = $_POST['qty_barang_retur'][$key];
			$data_db_barang[$key]['harga_total_retur'] = $harga_satuan * $qty;
			$data_db_barang[$key]['diskon_jenis_retur'] = $diskon_jenis;
			$data_db_barang[$key]['diskon_nilai_retur'] = $diskon_nilai;
			$data_db_barang[$key]['diskon_retur'] = $diskon_harga;
			$data_db_barang[$key]['harga_neto_retur'] = $harga_barang_retur;
			
			$sub_total += $harga_barang_retur;
		}
		
		$this->db->transStart();
		
		// Save table pembelian_retur
		$data_db['id_pembelian'] = $_POST['id_pembelian'];
		
		// No nota retur
		if (empty($_POST['id'])) 
		{
			$sql = 'LOCK TABLES pembelian_retur WRITE, setting WRITE';
			$this->db->query($sql);
			
			$sql = 'SELECT * FROM setting WHERE type="nota_retur"';
			$result = $this->db->query($sql)->getResultArray();
			// print_r($result);
			foreach ($result as $val) {
				if ($val['param'] == 'no_nota_retur') {
					$pola_nomor = $val['value'];
				}
				
				if ($val['param'] == 'jml_digit') {
					$jml_digit = $val['value'];
				}
			}
			
			$sql = 'SELECT MAX(no_squence) AS value FROM pembelian_retur WHERE tgl_nota_retur LIKE "' . date('Y') . '%"';
			$result = $this->db->query($sql)->getRowArray();
			$no_squence = $result['value'] + 1;
			$no_nota_retur = str_pad($no_squence, $jml_digit, "0", STR_PAD_LEFT);
			$no_nota_retur = str_replace('{{nomor}}', $no_nota_retur, $pola_nomor);
			$no_nota_retur = str_replace('{{tahun}}', date('Y'), $no_nota_retur);
			$data_db['no_nota_retur'] = $no_nota_retur;
			$data_db['no_squence'] = $no_squence;
		}

		//-- No nota retur
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
		
		$data_db['diskon_jenis'] = $diskon_total_jenis;
		$data_db['diskon_nilai'] = $diskon_total_nilai;
		
		/* $operator = '';
		if ($_POST['penyesuaian_operator'] == '-') {
			$operator = '-';
		} */
		$data_db['penyesuaian'] = str_replace('.', '', $_POST['penyesuaian_nilai']);
		$neto_retur = $sub_total + $data_db['penyesuaian'];
		if ($neto_retur < 0) {
			$neto_retur = 0;
		}
		$data_db['neto_retur'] = $neto_retur;
		
		// Save table penjualan_barang, penjualan_layanan		
		if ($_POST['id']) 
		{
			$data_db['id_user_update'] = $_SESSION['user']['id_user'];
			$data_db['tgl_update'] = date('Y-m-d H:i:s');
			$query = $this->db->table('pembelian_retur')->update($data_db, ['id_pembelian_retur' => $_POST['id']]);
			$id_pembelian_retur = $_POST['id'];
		} else {
			$data_db['id_user_input'] = $_SESSION['user']['id_user'];
			$query = $this->db->table('pembelian_retur')->insert($data_db);
			$id_pembelian_retur = $this->db->insertID();
		}
		
		// Save tabel penjualan_detail
		foreach ($data_db_barang as &$val) {
			$val['id_pembelian_retur'] = $id_pembelian_retur;
		}
		
		$sql = 'UNLOCK TABLES';
		$this->db->query($sql);
		
		$this->db->table('pembelian_retur_detail')->delete(['id_pembelian_retur' => $id_pembelian_retur]);
		$this->db->table('pembelian_retur_detail')->insertBatch($data_db_barang);
		
		$this->db->transComplete();
		
		if ($this->db->transStatus() === false ) {
			$result['status'] = 'error';
			$result['content'] = 'Data gagal disimpan';
		} else {
			$sql = 'SELECT * FROM pembelian_retur WHERE id_pembelian_retur = ?';
			$data = $this->db->query($sql, $id_pembelian_retur)->getRowArray();
			$result['status'] = 'ok';
			$result['content'] = 'Data berhasil disimpan';
			$result['id_pembelian_retur'] = $id_pembelian_retur;
			$result['pembelian_retur'] = $data;
		}
		
		return $result;
	}
	
	public function countAllPembelianRetur() {
		$sql = 'SELECT COUNT(*) AS jml FROM pembelian_retur AS tabel';
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListPembelianRetur() 
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
		$sql = 'SELECT COUNT(*) AS jml_data FROM pembelian_retur
				LEFT JOIN pembelian USING(id_pembelian)
				LEFT JOIN supplier USING(id_supplier)
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
		$sql = 'SELECT * FROM pembelian_retur
				LEFT JOIN pembelian USING(id_pembelian)
				LEFT JOIN supplier USING(id_supplier)
				' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
		
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
	
	// List Barang
	public function countAllDataInvoice () {
		$sql = 'SELECT COUNT(*) AS jml FROM pembelian ';
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
		$sql = 'SELECT COUNT(*) AS jml FROM pembelian LEFT JOIN supplier USING(id_supplier)' . $where;
		$result = $this->db->query($sql)->getRowArray();
		$total_filtered = $result['jml'];
		
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = 'SELECT * FROM pembelian LEFT JOIN supplier USING(id_supplier)' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
		
		// List item
		$id_pembelian = [];
		foreach ($data as $val) {
			$id_pembelian[] = $val['id_pembelian'];
		}
		
		if ($id_pembelian) {
			$sql = 'SELECT *
					FROM pembelian_detail 
					LEFT JOIN barang USING(id_barang)
					LEFT JOIN satuan_unit USING(id_satuan_unit)
					WHERE id_pembelian IN (' . join(',', $id_pembelian) . ')';
			
			$result = $this->db->query($sql)->getResultArray();
		
			if ($result) {
				$pembelian_detail = [];
				foreach ($result as $val) {
					$pembelian_detail[$val['id_pembelian']][] = $val;
				}
					
				// Merge
				foreach ($data as &$val) {
					$val['detail']= $pembelian_detail[$val['id_pembelian']];
				}
			}
		}
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}
?>