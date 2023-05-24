<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022-2022
*/

namespace App\Models;

class PenjualanMobileModel extends \App\Models\BaseModel
{
	public function getSettingPajak() 
	{
		$sql = 'SELECT * FROM setting WHERE type="pajak"';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getPenjualanById($id) {
		
		$sql = 'SELECT * FROM penjualan LEFT JOIN customer USING(id_customer) WHERE id_penjualan = ?';
		$result = $this->db->query($sql, $id)->getRowArray();
		return $result;
	}
	
	public function getPenjualanBarangByIdPenjualan($id) {
		$sql = 'SELECT *
				FROM penjualan_detail 
				LEFT JOIN barang USING(id_barang)
				WHERE id_penjualan = ?';
				
		$result = $this->db->query($sql, $id)->getResultArray();
		
		$data = [];
		foreach ($result as $val) {
			$data[$val['id_barang']] = $val;
		}
		
		// List stok
		$id_barang = [];
		foreach ($data as $val) {
			$id_barang[] = $val['id_barang'];
		}
		
		$list_stok = $this->getListStokByIdBarang($id_barang);
		$list_harga = $this->getListHargaBarang($id_barang);
		
		// Merge
		foreach ($data as &$val) {
			$val['list_stok'] = $list_stok[$val['id_barang']];
			$val['list_harga'] = $list_harga[$val['id_barang']];
		}
		
		return $data;
	}
	
	public function getPembayaranByIdPenjualan($id) {
		$sql = 'SELECT *
				FROM penjualan_bayar 
				WHERE id_penjualan = ?';
				
		$result = $this->db->query($sql, $id)->getResultArray();
		return $result;
	}
	
	public function getAllGudang() {
		$sql = 'SELECT * FROM gudang';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getJenisHarga() {
		$sql = 'SELECT * FROM jenis_harga';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	
	
	public function countAllDataBarang() {
		$sql = 'SELECT COUNT(*) AS jml FROM barang ';
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListDataBarang( $id_gudang, $id_jenis_harga ) {

		$columns = $this->request->getPost('columns');

		// Search
		$search_all1 = @$this->request->getPost('search')['value'];
		$search_all = str_replace(' ','%',$search_all1);
		$where = ' WHERE 1 = 1 ';
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
			if (strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore') === false) {
				$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
				$order = ' ORDER BY ' . $order_by;
			}
		}

		// Query Total Filtered
		$sql = 'SELECT COUNT(*) as jml
				FROM barang 
								LEFT JOIN (
								SELECT id_barang, SUM(adjusment_stok) AS stok 
								FROM barang_adjusment_stok LEFT JOIN gudang USING(id_gudang)
								WHERE id_gudang = ' . $id_gudang . '
								GROUP BY id_barang
				) AS detail USING(id_barang)' . $where;
				
		$result = $this->db->query($sql)->getRowArray();
		$total_filtered = $result['jml'];
		
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = 'SELECT *, (
					SELECT harga 
					FROM barang_harga
					LEFT JOIN jenis_harga USING (id_jenis_harga)
					WHERE id_jenis_harga = ' . $id_jenis_harga . ' AND id_barang = barang.id_barang 
					AND jenis = "harga_jual" 
					ORDER BY tgl_input DESC 
					LIMIT 1
				) AS harga
				FROM barang 
				LEFT JOIN (SELECT * FROM barang_image WHERE default_image = "Y" ) as barang_image USING(id_barang)
				LEFT JOIN file_picker USING(id_file_picker)
				LEFT JOIN (
								SELECT id_barang, SUM(adjusment_stok) AS stok 
								FROM barang_adjusment_stok LEFT JOIN gudang USING(id_gudang)
								WHERE id_gudang = ' . $id_gudang . '
								GROUP BY id_barang
				) AS detail USING(id_barang)
				' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
		
		// List stok
		$id_barang = [];
		foreach ($data as $val) {
			$id_barang[] = $val['id_barang'];
		}
		
		$list_stok = $this->getListStokByIdBarang($id_barang);
		$list_harga = $this->getListHargaBarang($id_barang);
		
		// Merge
		foreach ($data as &$val) {
			$val['list_stok'] = $list_stok[$val['id_barang']];
			$val['list_harga'] = $list_harga[$val['id_barang']];
		}
		
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
	
	private function getListHargaBarang($id_barang) {
		
		$sql = 'SELECT * FROM jenis_harga';
		$result = $this->db->query($sql)->getResultArray();
		$list_harga = [];
		foreach ($result as $val) {
			$sql = 'SELECT id_barang, (
					SELECT harga 
					FROM barang_harga
					WHERE id_jenis_harga = ' . $val['id_jenis_harga'] . ' AND id_barang = barang.id_barang 
					AND jenis = "harga_jual" 
					ORDER BY tgl_input DESC 
					LIMIT 1
				) AS harga_jual
				FROM barang
				WHERE id_barang IN(' . join(',', $id_barang) . ')';
			$result_harga = $this->db->query($sql)->getResultArray();
			
			foreach ($result_harga as $val_harga) {
				$list_harga[$val_harga['id_barang']][$val['id_jenis_harga']] = $val_harga['harga_jual'];
			}
		}
		
		return $list_harga;
	}
	
	private function getListStokByIdBarang($id_barang) {
		
		$sql = 'SELECT id_barang, id_gudang, SUM(adjusment_stok) AS stok 
				FROM barang_adjusment_stok LEFT JOIN gudang USING(id_gudang)
				WHERE id_barang IN (' . join(',', $id_barang) . ')
				GROUP BY id_barang, id_gudang';
		
		$result = $this->db->query($sql)->getResultArray();
		$list_stok = [];
		foreach ($result as $val) {
			$list_stok[$val['id_barang']][$val['id_gudang']] = $val['stok'];
		}
		
		return $list_stok;
	}
	
	public function saveData() 
	{	
		// barang
		$sub_total = 0;
		$total_diskon_item = 0;
		$total_qty = 0;
		
		// echo '<pre>'; print_r($_POST['id_barang']); die;
		$data_db_barang = [];
		foreach ($_POST['id_barang'] as $key => $id_barang) 
		{
			$sql = 'SELECT * FROM barang WHERE id_barang = ?';
			$query_barang = $this->db->query($sql, $id_barang)->getRowArray();
			$harga_satuan = str_replace(['.'], '', $_POST['harga_satuan'][$key]);
			$qty = str_replace(['.'], '', $_POST['qty'][$key]);
			$harga_barang =  $harga_satuan * $qty;
			
			$diskon_nilai = str_replace(['.'], '', $_POST['diskon_barang_nilai'][$key]);
			$diskon_jenis = $_POST['diskon_barang_jenis'][$key];
			$diskon_harga = 0;
			if ($diskon_nilai) {
				$diskon_harga = $diskon_nilai;
				if ($diskon_jenis == '%') {
					$diskon_harga = round($harga_barang * $diskon_nilai / 100);
				}
				
				$harga_barang = $harga_barang - $diskon_harga;
				$total_diskon_item += $diskon_harga;
			}
			$total_qty += $qty;
			
			$data_db_barang[$key]['id_barang'] = $id_barang;
			$data_db_barang[$key]['qty'] = $_POST['qty'][$key];
			$data_db_barang[$key]['satuan'] = $query_barang['satuan'];
			$data_db_barang[$key]['harga_satuan'] = $harga_satuan;
			$data_db_barang[$key]['harga_total'] = $harga_satuan * $qty;
			$data_db_barang[$key]['diskon_jenis'] = $diskon_jenis;
			$data_db_barang[$key]['diskon_nilai'] = $diskon_nilai;
			$data_db_barang[$key]['diskon'] = $diskon_harga;
			$data_db_barang[$key]['harga_neto'] = $harga_barang;
			
			$sub_total += $harga_barang;
		}
		
		$this->db->transStart();
		
		// Save table penjualan
		
		$data_db['id_customer'] = null;
		if ($_POST['id_customer']) {
			$data_db['id_customer'] = $_POST['id_customer'];
		}
		$data_db['id_gudang'] = $_POST['id_gudang'];
		$data_db['id_jenis_harga'] = $_POST['id_jenis_harga'];
		$data_db['sub_total'] = $sub_total;
		$data_db['total_diskon_item'] = $total_diskon_item;
		$data_db['total_qty'] = $total_qty;
		$data_db['tgl_penjualan'] = date('Y-m-d H:i:s');
		$data_db['tgl_invoice'] = date('Y-m-d');
		
		// Invoice
		$sql = 'LOCK TABLES penjualan WRITE, setting WRITE, penjualan_bayar WRITE';
		$this->db->query($sql);
		
		$sql = 'SELECT * FROM setting WHERE type="invoice"';
		$result = $this->db->query($sql)->getResultArray();
		// print_r($result); die;
		foreach ($result as $val) {
			if ($val['param'] == 'no_invoice') {
				$pola_no_invoice = $val['value'];
			}
			
			if ($val['param'] == 'jml_digit') {
				$jml_digit = $val['value'];
			}
		}
		
		$sql = 'SELECT MAX(no_squence) AS value FROM penjualan WHERE tgl_invoice LIKE "' . date('Y') . '%"';
		$result = $this->db->query($sql)->getRowArray();
		$no_squence = $result['value'] + 1;
		$no_invoice = str_pad($no_squence, $jml_digit, "0", STR_PAD_LEFT);
		$no_invoice = str_replace('{{nomor}}', $no_invoice, $pola_no_invoice);
		$no_invoice = str_replace('{{tahun}}', date('Y'), $no_invoice);
		$data_db['no_invoice'] = $no_invoice;
		$data_db['no_squence'] = $no_squence;
		
		//-- Invoice
		
		$diskon_total_jenis = $_POST['diskon_total_jenis'];
		$diskon_total_nilai = str_replace(['.'], '', $_POST['diskon_total_nilai']);
		$diskon = 0;
		if ($diskon_total_nilai) {
			if ($diskon_total_jenis == '%') {
				$sub_total = $sub_total - round($sub_total * $diskon_total_nilai / 100);
				$diskon = round($sub_total * $diskon_total_nilai / 100);
			} else {
				$sub_total = $sub_total - $diskon_total_nilai;
				$diskon = $diskon_total_nilai;
			}
		}
		
		$data_db['diskon'] = $diskon;
		$data_db['total_diskon'] = $total_diskon_item + $diskon;
		$data_db['diskon_jenis'] = $diskon_total_jenis;
		$data_db['diskon_nilai'] = $diskon_total_nilai;
		
		$operator = '';
		if ($_POST['penyesuaian_operator'] == 'minus') {
			$operator = '-';
		}
		$data_db['penyesuaian'] = $operator . str_replace('.', '', $_POST['penyesuaian_nilai']);
		$neto = $sub_total + $data_db['penyesuaian'];
		if ($neto < 0) {
			$neto = 0;
		}
		$data_db['neto'] = $neto;
		
		
		$total_bayar = 0;
		foreach ($_POST['jml_bayar'] as $key => $val) 
		{
			$total_bayar += (int) str_replace('.', '', $val);
		}
		
		$data_db['total_bayar'] = $total_bayar;
		$data_db['kurang_bayar'] = $neto - $total_bayar;
		$data_db['kembali'] = 0;
		
		if ($total_bayar > $neto) {
			$data_db['kembali'] = $total_bayar - $neto;
		}
		
		if ($data_db['kurang_bayar'] <= 0) {
			$status = 'lunas';
		} else {
			$status = 'kurang_bayar';
		}
						
		// Save table penjualan_barang, penjualan_layanan		
		if (!empty($_POST['id'])) 
		{
			$data_db['id_user_update'] = $_SESSION['user']['id_user'];
			$data_db['tgl_update'] = date('Y-m-d H:i:s');
			$query = $this->db->table('penjualan')->update($data_db, ['id_penjualan' => $_POST['id']]);
			$this->db->table('penjualan_detail')->delete(['id_penjualan' => $_POST['id']]);
			$this->db->table('penjualan_bayar')->delete(['id_penjualan' => $_POST['id']]);
			$id_penjualan = $_POST['id'];
		} else {
			$data_db['id_user_input'] = $_SESSION['user']['id_user'];
			$query = $this->db->table('penjualan')->insert($data_db);
			$id_penjualan = $this->db->insertID();
		}
		
		// Save tabel penjualan_bayar
		if ($total_bayar) 
		{
			foreach ($_POST['jml_bayar'] as $key => $val) 
			{
				$data_db_bayar[$key]['id_penjualan'] = $id_penjualan;
				$data_db_bayar[$key]['jml_bayar'] = str_replace('.', '', $val);
				$data_db_bayar[$key]['tgl_bayar'] = $_POST['tgl_bayar'][$key];
			}
			$this->db->table('penjualan_bayar')->insertBatch($data_db_bayar);
		}
		
		// Save tabel penjualan_detail
		foreach ($data_db_barang as &$val) {
			$val['id_penjualan'] = $id_penjualan;
		}
		
		$sql = 'UNLOCK TABLES';
		$this->db->query($sql);
		
		$this->db->table('penjualan_detail')->insertBatch($data_db_barang);
		
		$this->db->transComplete();
		
		if ($this->db->transStatus() === false ) {
			$result['status'] = 'error';
			$result['message'] = 'Data gagal disimpan';
		} else {
			$sql = 'SELECT * FROM penjualan WHERE id_penjualan = ?';
			$data = $this->db->query($sql, $id_penjualan)->getRowArray();
						
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
			$result['id_penjualan'] = $id_penjualan;
			$result['penjualan'] = $data;
			$result['no_invoice'] = $data['no_invoice'];
			$result['customer'] = ['email' => ''];
			
			if (!empty($_POST['id_customer'])) {
				$sql = 'SELECT * FROM customer WHERE id_customer = ?';
				$result['customer'] = $this->db->query($sql, $_POST['id_customer'])->getRowArray();
			}
		}
		
		return $result;
	}
	
	
	// Penjualan
	public function countAllDataPenjualan() {
		$sql = 'SELECT COUNT(*) AS jml FROM penjualan AS tabel';
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListDataPenjualan() 
	{

		$columns = $this->request->getPost('columns');

		// Search
		$search_all1 = @$this->request->getPost('search')['value'];
		$search_all = str_replace(' ','%',$search_all1);
		$where = ' WHERE 1=1 ';
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
		$sql = 'SELECT COUNT(*) AS jml FROM penjualan 
				LEFT JOIN customer USING(id_customer)
				' . $where;
		$data = $this->db->query($sql)->getRowArray();
		$total_filtered = $data['jml'];
		
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
		$sql = 'SELECT *, user.nama as nama FROM penjualan 
				LEFT JOIN customer USING(id_customer)
				LEFT JOIN user on penjualan.id_user_input=user.id_user
				' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
	
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}
?>