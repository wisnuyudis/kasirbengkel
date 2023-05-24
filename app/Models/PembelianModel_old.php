<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Models;

class PembelianModel extends \App\Models\BaseModel
{
	private $fotoPath;
	
	public function __construct() {
		parent::__construct();
		$this->fotoPath = 'public/images/foto/';
	}
	
	public function deleteData() 
	{
		/* $sql = 'SELECT file_dok_pembelian FROM pembelian WHERE id_pembelian = ?';
		$query = $this->db->query($sql, $_POST['id'])->getRowArray();
		
		$error = false;
		$path = ROOTPATH . '/public/images/dokumen/faktur_pembelian/';
		if (file_exists($path . $query['file_dok_pembelian'])) {
			$unlink = delete_file($path . $query['file_dok_pembelian']);
			if (!$unlink) {
				$result['message']['status'] = 'error';
				$result['message']['content'] = 'Gagal menghapus foto dokumen pembelian';
				$error = true;
			}
		} */
		
		// if (!$error) {
			$result = $this->db->table('pembelian')->delete(['id_pembelian' => $_POST['id']]);
		// }
		return $result;
	}
	
	
	
	public function getAllSupplier() {
		$sql = 'SELECT * FROM supplier';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getAllBarangKategori() {
		$sql = 'SELECT * FROM barang_kategori';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getAllUser() {
		$sql = 'SELECT * FROM user';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getAllGudang() {
		$sql = 'SELECT * FROM gudang';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getPembelianById($id) {
		$sql = 'SELECT * FROM pembelian LEFT JOIN supplier USING(id_supplier) WHERE id_pembelian = ?';
		$result = $this->db->query($sql, trim($id))->getRowArray();
		if ($result) {
			$sql_file = 'SELECT * FROM pembelian_file LEFT JOIN file_picker USING(id_file_picker) WHERE id_pembelian = ? ORDER BY urut';
			$images = $this->db->query($sql_file, $result['id_pembelian'])->getResultArray();
			$result['images'] = $images;
		}
		return $result;
	}
	
	public function getPembelianDetailById($id) {

		$sql = 'SELECT * FROM pembelian_detail 
					LEFT JOIN barang USING(id_barang) 
					WHERE id_pembelian = ?';
		$result = $this->db->query($sql, trim($id))->getResultArray();
		return $result;
	}
	
	public function getBarangByBarcode($code) {
		$sql = 'SELECT * FROM barang WHERE barcode = ?';
		$result = $this->db->query($sql, trim($code))->getRowArray();
		return $result;
	}
	
	public function getBarangById($id) {
		$sql = 'SELECT * FROM barang WHERE id_barang = ?';
		$result = $this->db->query($sql, trim($id))->getRowArray();
		return $result;
	}
	
	public function getPembayaranById($id) {
		$sql = 'SELECT * FROM pembelian_bayar 
				LEFT JOIN pembelian USING(id_pembelian) 
				LEFT JOIN user ON user.id_user = pembelian_bayar.id_user_bayar
				WHERE pembelian_bayar.id_pembelian = ?';
		$result = $this->db->query($sql, trim($id))->getResultArray();
		return $result;
	}
	
	public function saveData() {
		
		$this->db->transBegin();

		$data_db['no_invoice'] = $_POST['no_invoice'];
		$data_db['id_supplier'] = $_POST['id_supplier'];
		$data_db['id_gudang'] = $_POST['id_gudang'];
		
		list($d, $m, $y) = explode ('-', $_POST['tgl_invoice']);
		$data_db['tgl_invoice'] = $y . '-' . $m . '-' . $d;
		
		list($d, $m, $y) = explode ('-', $_POST['tgl_jatuh_tempo']);
		$data_db['tgl_jatuh_tempo'] = $y . '-' . $m . '-' . $d;
		
		$data_db['sub_total'] = str_replace('.', '', trim($_POST['sub_total']));
		$data_db['diskon'] = str_replace('.', '', trim($_POST['diskon']));
		if ($data_db['sub_total'] - $data_db['diskon'] < 0) {
			$total = 0;
		} else {
			$total = $data_db['sub_total'] - $data_db['diskon'];
		}
		$data_db['total'] = $total;
		
		// Bayar
		$data_db['total_bayar'] = str_replace('.', '', trim($_POST['total_bayar']));
		$data_db['kurang_bayar'] = str_replace('.', '', trim($_POST['kurang_bayar']));
		$data_db['status'] = $data_db['kurang_bayar'] > 0 ? 'Belum Lunas' : 'Lunas';
		
		if ($data_db['total'] - $data_db['kurang_bayar'] < 0) {
			$data_db['kurang_bayar'] = 0;
		}
		
		$data_db['terima_barang'] = $_POST['terima_barang'];
		$data_db['tgl_terima_barang']  = '0000-00-00';
		$data_db['id_user_terima']  = null;
		if ($_POST['terima_barang'] == 'Y') {
			list($d, $m, $y) = explode ('-', $_POST['tgl_terima_barang']);
			$data_db['tgl_terima_barang'] = $y . '-' . $m . '-' . $d;
			$data_db['id_user_terima'] = $_POST['id_user_terima'];
		}
				
		$id_pembelian = '';
		if ($_POST['id']) 
		{
			$query = $this->db->table('pembelian')->update($data_db, ['id_pembelian' => $_POST['id']]);
			$id_pembelian = $_POST['id'];
		} else {
			$query = $this->db->table('pembelian')->insert($data_db);
			$id_pembelian = $this->db->insertID();
		}
		
		// Detail pembelian barang
		$this->db->table('pembelian_detail')->delete(['id_pembelian' => $id_pembelian]);
		foreach ($_POST['qty'] as $key => $val) {
			$data_db = [];
			$data_db['id_pembelian'] = $id_pembelian;
			$data_db['id_barang'] = $_POST['id_barang'][$key];
			
			$data_db['expired_date'] = '';
			if (!empty($_POST['expired_date'][$key])) {
				list($d, $m, $y) = explode ('-', $_POST['expired_date'][$key]);
				$data_db['expired_date'] = $y . '-' . $m . '-' . $d;
			}
			$data_db['qty'] = str_replace('.', '', $_POST['qty'][$key]);
			// $data_db['id_gudang'] = $_POST['id_gudang'];
			$data_db['harga_satuan'] = str_replace('.', '', $_POST['harga_satuan'][$key]);
			$data_db['harga_neto'] = str_replace('.', '', $_POST['harga_neto'][$key]);
			$data_db['keterangan'] = $_POST['keterangan'][$key];
			$this->db->table('pembelian_detail')->insert($data_db);
		}
		
		// Pembayaran
		if ($_POST['using_pembayaran']) {
			$this->db->table('pembelian_bayar')->delete(['id_pembelian' => $id_pembelian]);
			foreach ($_POST['tgl_bayar'] as $key => $val) {
				$data_db = [];
				$data_db['id_pembelian'] = $id_pembelian;
				list($d, $m, $y) = explode ('-', $val);
				$data_db['tgl_bayar'] = $y . '-' . $m . '-' . $d;
				$data_db['jml_bayar'] = str_replace('.', '', $_POST['jml_bayar'][$key]);
				$data_db['id_user_bayar'] = $_POST['id_user_bayar'][$key];
				$this->db->table('pembelian_bayar')->insert($data_db);
			}
		}
		
		// File
		if ($_POST['id']) {
			$this->db->table('pembelian_file')->delete(['id_pembelian' => $id_pembelian]);
		}
		
		$data_db = [];
		foreach ($_POST['id_file_picker'] as $index => $val) {
			if (!$val) {
				continue;
			}
			$data_db[] = ['id_file_picker' => $val, 'id_pembelian' => $id_pembelian, 'urut' => ($index + 1) ];
		}
		if ($data_db) {
			$this->db->table('pembelian_file')->insertBatch($data_db);
		}
		
		if ($this->db->transStatus() === false) {
			$this->db->transRollback();
			$result['status'] = 'error';
			$result['message'] = 'Data gagal disimpan';
		} else {
			$this->db->transCommit();
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
			$result['id_pembelian'] = $id_pembelian;
		}
		
		return $result;
	}
	
	public function countAllData($where) {
		$sql = 'SELECT COUNT(*) AS jml FROM pembelian' . $where;
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
		if (!empty($_POST)) {
			if (strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore_search') === false) {
				$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
				$order = 'ORDER BY ' . $order_by . ' LIMIT ' . $start . ', ' . $length;
			}
		}

		// Query Total Filtered
		$sql = 'SELECT COUNT(*) AS jml FROM pembelian LEFT JOIN supplier USING(id_supplier) ' . $where;
		$data = $this->db->query($sql)->getRowArray();
		$total_filtered = $data['jml'];

		// Query Data
		$sql = 'SELECT * FROM pembelian LEFT JOIN supplier USING(id_supplier) ' . $where . $order;
		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
	
	// List Barang
	public function countAllDataBarang($where) {
		$sql = 'SELECT COUNT(*) AS jml FROM barang';
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListDataBarang($where) {

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
		$order_data = $this->request->getPost('order');
		$order = '';
		if (!empty($_POST)) {
			if (strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore_search') === false) {
				$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
				$order = ' ORDER BY ' . $order_by;
			}
		}

		// Query Total Filtered
		$sql = 'SELECT COUNT(*) AS jml FROM barang LEFT JOIN satuan_unit USING(id_satuan_unit) ' . $where;
		$data = $this->db->query($sql)->getRowArray();
		$total_filtered = $data['jml'];
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = 'SELECT *, (
					SELECT harga FROM barang_harga WHERE id_barang = barang.id_barang AND jenis = "harga_jual" ORDER BY tgl_input DESC LIMIT 1
				) AS harga_jual, (
					SELECT harga FROM barang_harga WHERE id_barang = barang.id_barang AND jenis = "harga_pokok" ORDER BY tgl_input DESC LIMIT 1
				) AS harga_pokok
				FROM barang
				LEFT JOIN satuan_unit USING(id_satuan_unit)
				LEFT JOIN (
					
					SELECT id_barang, id_gudang, SUM(saldo_stok) AS stok FROM (
						SELECT id_barang, id_gudang, adjusment_stok AS saldo_stok, "adjusment" AS jenis
						FROM barang_adjusment_stok
						WHERE id_gudang = ' . $_GET['id_gudang'] . '
							UNION ALL
						SELECT id_barang, id_gudang, CAST(qty as SIGNED) * -1 AS saldo_stok, "penjualan" AS jenis
						FROM penjualan_detail LEFT JOIN penjualan USING(id_penjualan)
						WHERE id_gudang = ' . $_GET['id_gudang'] . '
							UNION ALL
						SELECT id_barang, id_gudang, qty_retur AS saldo_stok, "penjualan_retur" AS jenis
						FROM penjualan_retur_detail LEFT JOIN penjualan_detail USING(id_penjualan_detail) LEFT JOIN penjualan USING(id_penjualan)
						WHERE id_gudang = ' . $_GET['id_gudang'] . '
							UNION ALL
						SELECT id_barang, id_gudang, qty AS saldo_stok, "pembelian" AS jenis
						FROM pembelian_detail LEFT JOIN pembelian USING(id_pembelian)
						WHERE id_gudang = ' . $_GET['id_gudang'] . '
							UNION ALL
						SELECT id_barang, id_gudang, CAST(qty_retur AS SIGNED) * -1 AS saldo_stok, "pembelian_retur" AS jenis
						FROM pembelian_retur_detail LEFT JOIN pembelian_detail USING(id_pembelian_detail) LEFT JOIN pembelian USING(id_pembelian)
						WHERE id_gudang = ' . $_GET['id_gudang'] . '
							UNION ALL
						SELECT id_barang, id_gudang_asal, CAST(qty_transfer AS SIGNED) * -1 AS saldo_stok, "transfer_keluar" AS jenis 
						FROM transfer_barang_detail
						LEFT JOIN transfer_barang USING (id_transfer_barang)
						WHERE id_gudang_asal = ' . $_GET['id_gudang'] . '
							UNION ALL
						SELECT id_barang, id_gudang_tujuan, qty_transfer AS saldo_stok, "transfer_masuk" AS jenis 
						FROM transfer_barang_detail
						LEFT JOIN transfer_barang USING (id_transfer_barang)
						WHERE id_gudang_tujuan = ' . $_GET['id_gudang'] . '
					) AS tabel
					GROUP BY id_barang, id_gudang
					
				) AS detail USING(id_barang)' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}
?>