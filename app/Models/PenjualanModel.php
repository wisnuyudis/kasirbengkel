<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Models;

class PenjualanModel extends \App\Models\BaseModel
{

	public function __construct() {
		parent::__construct();
	}
	
	public function deleteData($id) 
	{
		$this->db->transStart();
		$this->db->table('penjualan')->delete(['id_penjualan' => $id]);
		$this->db->table('penjualan_detail')->delete(['id_penjualan' => $id]);
		$this->db->table('penjualan_bayar')->delete(['id_penjualan' => $id]);
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
	
	public function getBarangByBarcode($code, $id_gudang, $id_jenis_harga) {
		
		$sql = 'SELECT id_barang FROM barang WHERE barcode = ?';
		$id_barang = $this->db->query($sql, $code)->getRowArray()['id_barang'];
		
		$sql = 'SELECT *, (
					SELECT harga 
					FROM barang_harga
					LEFT JOIN jenis_harga USING (id_jenis_harga)
					WHERE id_jenis_harga = ' . $id_jenis_harga . ' AND id_barang = barang.id_barang 
					AND jenis = "harga_jual" 
					ORDER BY tgl_input DESC 
					LIMIT 1
				) AS harga_jual, (
					SELECT harga FROM barang_harga WHERE id_barang = barang.id_barang AND jenis = "harga_pokok" ORDER BY tgl_input DESC LIMIT 1
				) AS harga_pokok
				FROM barang
				LEFT JOIN satuan_unit USING(id_satuan_unit)
				LEFT JOIN (
					
					SELECT id_barang, id_gudang, SUM(saldo_stok) AS stok FROM (
						SELECT id_barang, id_gudang, adjusment_stok AS saldo_stok, "adjusment" AS jenis
						FROM barang_adjusment_stok
						WHERE id_gudang = ' . $id_gudang . ' AND id_barang = ' . $id_barang . '
							UNION ALL
						SELECT id_barang, id_gudang, CAST(qty as SIGNED) * -1 AS saldo_stok, "penjualan" AS jenis
						FROM penjualan_detail LEFT JOIN penjualan USING(id_penjualan)
						WHERE id_gudang = ' . $id_gudang . ' AND id_barang = ' . $id_barang . '
							UNION ALL
						SELECT id_barang, id_gudang, qty_retur AS saldo_stok, "penjualan_retur" AS jenis
						FROM penjualan_retur_detail LEFT JOIN penjualan_detail USING(id_penjualan_detail) LEFT JOIN penjualan USING(id_penjualan)
						WHERE id_gudang = ' . $id_gudang . ' AND id_barang = ' . $id_barang . '
							UNION ALL
						SELECT id_barang, id_gudang, qty AS saldo_stok, "pembelian" AS jenis
						FROM pembelian_detail LEFT JOIN pembelian USING(id_pembelian)
						WHERE id_gudang = ' . $id_gudang . ' AND id_barang = ' . $id_barang . '
							UNION ALL
						SELECT id_barang, id_gudang, CAST(qty_retur AS SIGNED) * -1 AS saldo_stok, "pembelian_retur" AS jenis
						FROM pembelian_retur_detail LEFT JOIN pembelian_detail USING(id_pembelian_detail) LEFT JOIN pembelian USING(id_pembelian)
						WHERE id_gudang = ' . $id_gudang . ' AND id_barang = ' . $id_barang . '
							UNION ALL
						SELECT id_barang, id_gudang_asal, CAST(qty_transfer AS SIGNED) * -1 AS saldo_stok, "transfer_keluar" AS jenis 
						FROM transfer_barang_detail
						LEFT JOIN transfer_barang USING (id_transfer_barang)
						WHERE id_gudang_asal = ' . $id_gudang . ' AND id_barang = ' . $id_barang . '
							UNION ALL
						SELECT id_barang, id_gudang_asal, qty_transfer AS saldo_stok, "transfer_masuk" AS jenis 
						FROM transfer_barang_detail
						LEFT JOIN transfer_barang USING (id_transfer_barang)
						WHERE id_gudang_tujuan = ' . $id_gudang . ' AND id_barang = ' . $id_barang . '
					) AS tabel
					GROUP BY id_barang, id_gudang
					
				) AS detail USING(id_barang) WHERE id_barang = ' . $id_barang;
				
		$result = $this->db->query($sql, trim($code))->getRowArray();
		return $result;
	}
	
	public function getPenjualanById($id) {
		
		$sql = 'SELECT * FROM penjualan LEFT JOIN customer USING(id_customer) WHERE id_penjualan = ?';
		$result = $this->db->query($sql, $id)->getRowArray();
		return $result;
	}
	
	public function getPenjualanBarangByIdPenjualan($id) 
	{
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
			if (key_exists($val['id_barang'], $list_stok)) {
				$val['list_stok'] = $list_stok[$val['id_barang']];
			} else {
				$val['list_stok'][$val['id_barang']] = 0;
			}
			
			if (key_exists($val['id_barang'], $list_harga)) {
				$val['list_harga'] = $list_harga[$val['id_barang']];
			} else {
				$val['list_harga'] = 0;
			}
			
			// $val['list_stok'] = $list_stok[$val['id_barang']];
			// $val['list_harga'] = $list_harga[$val['id_barang']];
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
			$result['customer'] = ['nama_customer' => 'Umum', 'alamat_customer' => '-'];
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
		$total_diskon_item = 0;
		$total_qty = 0;
		$total_untung_rugi = 0;
		$total_harga_pokok = 0;
		
		// echo '<pre>'; print_r($_POST); die;
		$data_db_barang = [];
		foreach ($_POST['id_barang'] as $key => $id_barang) 
		{
			$sql = 'SELECT * FROM barang LEFT JOIN satuan_unit USING(id_satuan_unit) WHERE id_barang = ?';
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
			
			$untung_rugi = $harga_barang - $_POST['harga_pokok'][$key] * $qty;
			$total_untung_rugi += $untung_rugi;
			$total_harga_pokok += $_POST['harga_pokok'][$key] *  $qty;
			
			$data_db_barang[$key]['id_barang'] = $id_barang;
			$data_db_barang[$key]['qty'] = $_POST['qty'][$key];
			$data_db_barang[$key]['satuan'] = $query_barang['satuan'];
			$data_db_barang[$key]['harga_pokok'] = $_POST['harga_pokok'][$key];
			$data_db_barang[$key]['harga_satuan'] = $harga_satuan;
			$data_db_barang[$key]['harga_total'] = $harga_satuan * $qty;
			$data_db_barang[$key]['diskon_jenis'] = $diskon_jenis;
			$data_db_barang[$key]['diskon_nilai'] = $diskon_nilai;
			$data_db_barang[$key]['diskon'] = $diskon_harga;
			$data_db_barang[$key]['harga_neto'] = $harga_barang;
			$data_db_barang[$key]['harga_pokok_total'] = $_POST['harga_pokok'][$key] * $qty;
			$data_db_barang[$key]['untung_rugi'] = $untung_rugi;
			
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
		$data_db['jenis_bayar'] = $_POST['jenis_bayar'];
		$data_db['harga_pokok'] = $total_harga_pokok;
		$data_db['untung_rugi'] = $total_untung_rugi;
		
		// Invoice
		$sql = 'LOCK TABLES penjualan WRITE, setting WRITE, penjualan_detail WRITE, penjualan_bayar WRITE';
		$this->db->query($sql);
		
		if (empty($_POST['id'])) 
		{
			$sql = 'SELECT * FROM setting WHERE type="invoice"';
			$result = $this->db->query($sql)->getResultArray();
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
			$data_db['tgl_invoice'] = date('Y-m-d');
			$data_db['tgl_penjualan'] = date('Y-m-d H:i:s');
			
		} else {
			$exp = explode('-', $_POST['tgl_invoice']);
			$data_db['tgl_invoice'] = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		}
		
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
		
		$data_db['penyesuaian'] = str_replace('.', '', $_POST['penyesuaian_nilai']);
		$neto = $sub_total + $data_db['penyesuaian'];
		if ($neto < 0) {
			$neto = 0;
		}
		
		// Pajak
		$data_db['pajak_persen'] = $data_db['pajak_nilai'] = 0;
		$data_db['pajak_display_text'] = null;
		if (!empty($_POST['pajak_nilai'])) 
		{
			$setting = $this->getSetting('pajak');
			foreach ($setting as $val) {
				$pajak_setting[$val['param']] = $val['value'];
			}
				
			$pajak = round( $neto * $_POST['pajak_nilai'] / 100 );
			$neto = $neto + $pajak;
			$data_db['pajak_display_text'] = $pajak_setting['display_text'];
			$data_db['pajak_persen'] = $_POST['pajak_nilai'];
			$data_db['pajak_nilai'] = $pajak;
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

		if ($total_bayar >= $neto) {
			$status = 'lunas';
			$data_db['kembali'] = $total_bayar - $neto;
		}  else {
			$status = 'kurang_bayar';
		}
		
		$data_db['status'] = $status;
		
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
				$data_db_bayar[$key]['tgl_bayar'] = format_datedb($_POST['tgl_bayar'][$key]);
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
	
	public function countAllDataPenjualanCust() {
		$sql = 'SELECT COUNT(DISTINCT customer.id_customer) AS jml FROM penjualan AS tabel LEFT JOIN customer USING(id_customer)';
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	

	public function countAllDataPenjualanCustDate($startDate,$endDate) {
		$sql = 'SELECT COUNT(DISTINCT customer.id_customer) AS jml FROM penjualan AS tabel LEFT JOIN customer USING(id_customer) WHERE tgl_penjualan BETWEEN "'.$startDate.'" AND "'.$endDate.'"';
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}

	public function countAllDataPenjualanDetail($id,$startDate,$endDate) {
		if($id == null && $startDate == null && $endDate == null){
			$sql = 'SELECT COUNT(DISTINCT customer.id_customer) AS jml FROM penjualan AS tabel LEFT JOIN customer USING(id_customer) WHERE customer.id_customer IS NULL';
		} else if ($id == null){
			$sql = 'SELECT COUNT(DISTINCT customer.id_customer) AS jml FROM penjualan AS tabel LEFT JOIN customer USING(id_customer) WHERE customer.id_customer IS NULL AND tgl_penjualan BETWEEN "'.$startDate.'" AND "'.$endDate.'"';
		} else if($startDate == null && $endDate == null){
			$sql = 'SELECT COUNT(DISTINCT customer.id_customer) AS jml FROM penjualan AS tabel LEFT JOIN customer USING(id_customer) WHERE customer.id_customer LIKE ' .$id;
		} else {
			$sql = 'SELECT COUNT(DISTINCT customer.id_customer) AS jml FROM penjualan AS tabel LEFT JOIN customer USING(id_customer) WHERE customer.id_customer LIKE ' .$id. 'AND tgl_penjualan BETWEEN "'.$startDate.'" AND "'.$endDate.'"';
		}
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
		$sql = 'SELECT * FROM penjualan 
				LEFT JOIN customer USING(id_customer)
				' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
	
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}

	public function getListDataPenjualanCustDetail($id,$startDate,$endDate) 
	{

		$columns = $this->request->getPost('columns');

		// Search
		$search_all1 = @$this->request->getPost('search')['value'];
		$search_all = str_replace(' ','%',$search_all1);
		if ($id == NULL && $startDate == '' && $endDate == ''){
			$where = 'WHERE id_customer IS NULL'; 
		} else if ($id == null){
			$where = 'WHERE id_customer IS NULL AND tgl_penjualan BETWEEN "'.$startDate.'" AND "'.$endDate.'" ';
		} else if ($startDate == '' && $endDate == ''){
			$where = 'WHERE id_customer LIKE '.$id;
		} else {
			$where = 'WHERE id_customer LIKE '.$id.' AND tgl_penjualan BETWEEN "'.$startDate.'" AND "'.$endDate.'" ';
		}
		
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
		$sql = 'SELECT * FROM penjualan 
				LEFT JOIN customer USING(id_customer)
				' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
	
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}

	public function getListDataPenjualanCust() 
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
		$sql = 'SELECT COUNT(DISTINCT customer.id_customer) AS jml FROM penjualan 
				LEFT JOIN customer USING(id_customer)
				' . $where ;
		$data = $this->db->query($sql)->getRowArray();
		$total_filtered = $data['jml'] + 1;
		
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
		$sql = 'SELECT * FROM penjualan 
				LEFT JOIN customer USING(id_customer)
				' . $where .' GROUP BY customer.nama_customer LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
	
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}

	public function getCustomer($id){
		$sql = 'SELECT nama_customer FROM customer WHERE id_customer = "'.$id.'"';
		$result = $this->db->query($sql)->getRow();
		return $result;
	}

	public function getListDataPenjualanCustDate($startDate,$endDate) 
	{

		$columns = $this->request->getPost('columns');

		// Search
		$search_all1 = @$this->request->getPost('search')['value'];
		$search_all = str_replace(' ','%',$search_all1);
		$where = 'WHERE tgl_penjualan BETWEEN "'.$startDate.'" AND "'.$endDate.'" ';
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
		$sql = 'SELECT COUNT(DISTINCT customer.id_customer) AS jml FROM penjualan 
				LEFT JOIN customer USING(id_customer)
				' . $where ;
		$data = $this->db->query($sql)->getRowArray();
		$total_filtered = $data['jml'] + 1;
		
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
		$sql = 'SELECT * FROM penjualan 
				LEFT JOIN customer USING(id_customer)
				' . $where .' GROUP BY customer.nama_customer LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
	
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
	
	// List Customer
	public function countAllDataCustomer() {
		$sql = 'SELECT COUNT(*) AS jml FROM customer';
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListDataCustomer() {

		$columns = $this->request->getPost('columns');

		// Search
		$search_all1 = @$this->request->getPost('search')['value'];
		$search_all = str_replace(' ','%',$search_all1);
		
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
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		
		$order_data = $this->request->getPost('order');
		$order = '';
		
		if (!empty($_POST) && strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore_search') === false) {
			$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
			$order = 'ORDER BY ' . $order_by . ' LIMIT ' . $start . ', ' . $length;
		}

		// Query Total Filtered
		$sql = 'SELECT COUNT(*) AS jml_data FROM customer 
				LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan) 
				LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
				LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)
				' . $where;
				
		$query = $this->db->query($sql)->getRowArray();
		$total_filtered = $query['jml_data'];
							
		
		// Query Data
		$sql = 'SELECT * FROM customer 
				LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan) 
				LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
				LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)
				' . $where . $order;
		
		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
	
	// List Barang
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
			
			$list_columns = ['barcode', 'kode_Barang'];
			$where_col = [];
			foreach ($list_columns as $column) {
				$where_col[] = $column . ' = "' . $search_all . '"';
			}
			$where .= ' OR (' . join(' OR ', $where_col) . ') ';
			 
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
				LEFT JOIN satuan_unit USING(id_satuan_unit)
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
				) AS harga_jual, (
					SELECT harga FROM barang_harga WHERE id_barang = barang.id_barang AND jenis = "harga_pokok" ORDER BY tgl_input DESC LIMIT 1
				) AS harga_pokok
				FROM barang
				LEFT JOIN satuan_unit USING(id_satuan_unit)
				LEFT JOIN (
					
					SELECT id_barang, id_gudang, SUM(saldo_stok) AS stok FROM (
						SELECT id_barang, id_gudang, adjusment_stok AS saldo_stok, "adjusment" AS jenis
						FROM barang_adjusment_stok
						WHERE id_gudang = ' . $id_gudang . '
							UNION ALL
						SELECT id_barang, id_gudang, CAST(qty as SIGNED) * -1 AS saldo_stok, "penjualan" AS jenis
						FROM penjualan_detail LEFT JOIN penjualan USING(id_penjualan)
						WHERE id_gudang = ' . $id_gudang . '
							UNION ALL
						SELECT id_barang, id_gudang, qty_retur AS saldo_stok, "penjualan_retur" AS jenis
						FROM penjualan_retur_detail LEFT JOIN penjualan_detail USING(id_penjualan_detail) LEFT JOIN penjualan USING(id_penjualan)
						WHERE id_gudang = ' . $id_gudang . '
							UNION ALL
						SELECT id_barang, id_gudang, qty AS saldo_stok, "pembelian" AS jenis
						FROM pembelian_detail LEFT JOIN pembelian USING(id_pembelian)
						WHERE id_gudang = ' . $id_gudang . '
							UNION ALL
						SELECT id_barang, id_gudang, CAST(qty_retur AS SIGNED) * -1 AS saldo_stok, "pembelian_retur" AS jenis
						FROM pembelian_retur_detail LEFT JOIN pembelian_detail USING(id_pembelian_detail) LEFT JOIN pembelian USING(id_pembelian)
						WHERE id_gudang = ' . $id_gudang . '
							UNION ALL
						SELECT id_barang, id_gudang_asal, CAST(qty_transfer AS SIGNED) * -1 AS saldo_stok, "transfer_keluar" AS jenis 
						FROM transfer_barang_detail
						LEFT JOIN transfer_barang USING (id_transfer_barang)
						WHERE id_gudang_asal = ' . $id_gudang . '
							UNION ALL
						SELECT id_barang, id_gudang_tujuan, qty_transfer AS saldo_stok, "transfer_masuk" AS jenis 
						FROM transfer_barang_detail
						LEFT JOIN transfer_barang USING (id_transfer_barang)
						WHERE id_gudang_tujuan = ' . $id_gudang . '
					) AS tabel
					GROUP BY id_barang, id_gudang
					
				) AS detail USING(id_barang)' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
		
		// List stok
		$id_barang = [];
		foreach ($data as $val) {
			$id_barang[] = $val['id_barang'];
		}
		
		if ($id_barang) {
			$list_stok = $this->getListStokByIdBarang($id_barang);
			$list_harga = $this->getListHargaBarang($id_barang);
			
			// Merge
			foreach ($data as &$val) {
				$val['list_stok'] = key_exists($val['id_barang'], $list_stok) ? $list_stok[$val['id_barang']] : 0;
				$val['list_harga'] = key_exists($val['id_barang'], $list_harga) ? $list_harga[$val['id_barang']] : 0;
			}
		}
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
	
	private function getListHargaBarang($id_barang) {
		
		if (!$id_barang)
			return;
		
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
		
		// print_r( $id_barang ); die;
		if (!$id_barang)
			return;
		
		$list_id_barang = join(',', $id_barang);
		
		$sql = 'SELECT *, SUM(saldo_stok) AS stok FROM (
					SELECT id_barang, id_gudang, adjusment_stok AS saldo_stok, "adjusment" AS jenis
					FROM barang_adjusment_stok
					WHERE id_barang IN (' . $list_id_barang . ')
						UNION ALL
					SELECT id_barang, id_gudang, CAST(qty as SIGNED) * -1 AS saldo_stok, "penjualan" AS jenis
					FROM penjualan_detail LEFT JOIN penjualan USING(id_penjualan)
					WHERE id_barang IN (' . $list_id_barang . ')
						UNION ALL
					SELECT id_barang, id_gudang, qty_retur AS saldo_stok, "penjualan_retur" AS jenis
					FROM penjualan_retur_detail LEFT JOIN penjualan_detail USING(id_penjualan_detail) LEFT JOIN penjualan USING(id_penjualan)
					WHERE id_barang IN (' . $list_id_barang . ')
						UNION ALL
					SELECT id_barang, id_gudang, qty AS saldo_stok, "pembelian" AS jenis
					FROM pembelian_detail LEFT JOIN pembelian USING(id_pembelian)
					WHERE id_barang IN (' . $list_id_barang . ')
						UNION ALL
					SELECT id_barang, id_gudang, CAST(qty_retur AS SIGNED) * -1 AS saldo_stok, "pembelian_retur" AS jenis
					FROM pembelian_retur_detail LEFT JOIN pembelian_detail USING(id_pembelian_detail) LEFT JOIN pembelian USING(id_pembelian)
					WHERE id_barang IN (' . $list_id_barang . ')
						UNION ALL
					SELECT id_barang, id_gudang_asal, CAST(qty_transfer AS SIGNED) * -1 AS saldo_stok, "transfer_keluar" AS jenis 
					FROM transfer_barang_detail
					LEFT JOIN transfer_barang USING (id_transfer_barang)
					WHERE id_gudang_asal IN (' . $list_id_barang . ')
						UNION ALL
					SELECT id_barang, id_gudang_tujuan, qty_transfer AS saldo_stok, "transfer_masuk" AS jenis 
					FROM transfer_barang_detail
					LEFT JOIN transfer_barang USING (id_transfer_barang)
					WHERE id_gudang_tujuan IN (' . $list_id_barang . ')
				) AS tabel
				LEFT JOIN gudang USING(id_gudang)
				GROUP BY id_barang, id_gudang';
	
		$result = $this->db->query($sql)->getResultArray();
		$list_stok = [];
		foreach ($result as $val) {
			$list_stok[$val['id_barang']][$val['id_gudang']] = $val['stok'];
		}
		
		return $list_stok;
	}
}
?>