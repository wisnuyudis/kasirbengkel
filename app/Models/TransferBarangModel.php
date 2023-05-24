<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Models;

class TransferBarangModel extends \App\Models\BaseModel
{

	public function __construct() {
		parent::__construct();
	}
	
	public function deleteData($id) 
	{
		$this->db->transStart();
		$this->db->table('transfer_barang')->delete(['id_transfer_barang' => $id]);
		$this->db->table('transfer_barang_detail')->delete(['id_transfer_barang' => $id]);
		$this->db->transComplete();
		return $this->db->transStatus();
	}
	
	public function getTransferBarangById($id) {
		
		$sql = 'SELECT * FROM transfer_barang WHERE id_transfer_barang = ?';
		$result = $this->db->query($sql, $id)->getRowArray();
		return $result;
	}
	
	public function getBarangByIdTransferBarang($id) {
		$sql = 'SELECT *
				FROM transfer_barang_detail 
				LEFT JOIN barang USING(id_barang)
				WHERE id_transfer_barang = ?';
				
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
	
	public function getIdentitas() {
		$sql = 'SELECT * FROM identitas 
				LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan)
				LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
				LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)';
		return $this->db->query($sql)->getRowArray();
	}
	
	public function getTransferBarangDetail($id_transfer_barang) 
	{
		// Order
		$result['data'] = $this->db->query('SELECT * 
									FROM transfer_barang
									WHERE id_transfer_barang = ?' 
									, $id_transfer_barang
								)
							->getRowArray();
		
		if (!$result['data']) {
			return false;
		}
		
		// Produk
		$result['detail'] = $this->db->query('SELECT * 
									FROM transfer_barang_detail
									LEFT JOIN barang USING(id_barang)
									LEFT JOIN satuan_unit USING(id_satuan_unit)
									WHERE id_transfer_barang = ?' 
									, $id_transfer_barang
								)
							->getResultArray();
		
		// Gudang Asal
		$result['gudang_asal'] = $this->db->query('SELECT * 
									FROM gudang
									LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan)
									LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
									LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
									LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)
									WHERE id_gudang = ?' 
									, $result['data']['id_gudang_asal']
								)
							->getRowArray();
		
		// Gudang Tujuan
		$result['gudang_tujuan'] = $this->db->query('SELECT * 
									FROM gudang
									LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan)
									LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
									LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
									LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)
									WHERE id_gudang = ?' 
									, $result['data']['id_gudang_tujuan']
								)
							->getRowArray();
		
		return $result;
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
			$qty = str_replace(['.'], '', $_POST['qty_barang'][$key]);
			$harga_barang =  $harga_satuan * $qty;
			
			$diskon_nilai = str_replace(['.'], '', $_POST['diskon_barang'][$key]);
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
			$data_db_barang[$key]['qty_transfer'] = $_POST['qty_barang'][$key];
			$data_db_barang[$key]['harga_satuan'] = $harga_satuan;
			$data_db_barang[$key]['harga_total_transfer'] = $harga_satuan * $qty;
			$data_db_barang[$key]['diskon_jenis_transfer'] = $diskon_jenis;
			$data_db_barang[$key]['diskon_nilai_transfer'] = $diskon_nilai;
			$data_db_barang[$key]['diskon_transfer'] = $diskon_harga;
			$data_db_barang[$key]['harga_neto_transfer'] = $harga_barang;
			
			$sub_total += $harga_barang;
		}
		
		$this->db->transStart();
		
		// Save table transfer_barang
		$data_db['id_gudang_asal'] = $_POST['id_gudang_asal'];
		$data_db['id_gudang_tujuan'] = $_POST['id_gudang_tujuan'];
		$data_db['keterangan'] = $_POST['keterangan'];
		$data_db['id_jenis_harga_transfer'] = $_POST['id_jenis_harga'];
		$data_db['sub_total_transfer'] = $sub_total;
		$data_db['total_diskon_item_transfer'] = $total_diskon_item;
		$data_db['total_qty_transfer'] = $total_qty;
		
		$exp = explode('-', $_POST['tgl_nota_transfer']);
		$data_db['tgl_nota_transfer'] = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		
		// Invoice
		if ( !$_POST['id']) 
		{
			$sql = 'LOCK TABLES transfer_barang WRITE, setting WRITE';
			$this->db->query($sql);
			
			$sql = 'SELECT * FROM setting WHERE type="nota_transfer"';
			$result = $this->db->query($sql)->getResultArray();
			foreach ($result as $val) {
				if ($val['param'] == 'no_nota_transfer') {
					$pattern = $val['value'];
				}
				
				if ($val['param'] == 'jml_digit') {
					$jml_digit = $val['value'];
				}
			}
			
			$sql = 'SELECT MAX(no_squence) AS value FROM transfer_barang WHERE tgl_nota_transfer LIKE "' . date('Y') . '%"';
			$result = $this->db->query($sql)->getRowArray();
			$no_squence = $result['value'] + 1;
			$no_nota_transfer = str_pad($no_squence, $jml_digit, "0", STR_PAD_LEFT);
			$no_nota_transfer = str_replace('{{nomor}}', $no_nota_transfer, $pattern);
			$no_nota_transfer = str_replace('{{tahun}}', date('Y'), $no_nota_transfer);
			$data_db['no_nota_transfer'] = $no_nota_transfer;
			$data_db['no_squence'] = $no_squence;
		}
		//-- Invoice
		
		$diskon_total_jenis = $_POST['diskon_total_jenis'];
		$diskon_total_nilai = str_replace(['.'], '', $_POST['diskon_total_nilai']);
		if ($diskon_total_nilai) {
			if ($diskon_total_jenis == '%') {
				$sub_total = $sub_total - round($sub_total * $diskon_total_nilai / 100);
			} else {
				$sub_total = $sub_total - $diskon_total_nilai;
			}
		}
		
		$data_db['diskon_jenis_transfer'] = $diskon_total_jenis;
		$data_db['diskon_nilai_transfer'] = $diskon_total_nilai;
		
		$operator = '';
		if ($_POST['penyesuaian_operator'] == '-') {
			$operator = '-';
		}
		$data_db['penyesuaian_transfer'] = $operator . str_replace('.', '', $_POST['penyesuaian_nilai']);
		$neto = $sub_total + $data_db['penyesuaian_transfer'];
		if ($neto < 0) {
			$neto = 0;
		}
		$data_db['neto_transfer'] = $neto;
						
		// Save table transfer_barang		
		if ($_POST['id']) 
		{
			$data_db['id_user_update'] = $_SESSION['user']['id_user'];
			$data_db['tgl_update'] = date('Y-m-d H:i:s');
			$query = $this->db->table('transfer_barang')->update($data_db, ['id_transfer_barang' => $_POST['id']]);
			$id_transfer_barang = $_POST['id'];
		} else {
			$data_db['id_user_input'] = $_SESSION['user']['id_user'];
			$query = $this->db->table('transfer_barang')->insert($data_db);
			$id_transfer_barang = $this->db->insertID();
		}
		
		// Save tabel penjualan_detail
		foreach ($data_db_barang as &$val) {
			$val['id_transfer_barang'] = $id_transfer_barang;
		}
		
		$sql = 'UNLOCK TABLES';
		$this->db->query($sql);
		
		$this->db->table('transfer_barang_detail')->delete(['id_transfer_barang' => $_POST['id']]);
		$this->db->table('transfer_barang_detail')->insertBatch($data_db_barang);
		
		$this->db->transComplete();
		
		if ($this->db->transStatus() === false ) {
			$result['status'] = 'error';
			$result['message'] = 'Data gagal disimpan';
		} else {
			$sql = 'SELECT * FROM transfer_barang WHERE id_transfer_barang = ?';
			$data = $this->db->query($sql, $id_transfer_barang)->getRowArray();
			
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
			$result['id_transfer_barang'] = $id_transfer_barang;
			$result['transfer_barang'] = $data;
			$result['no_nota_transfer'] = $data['no_nota_transfer'];
		}
		
		return $result;
	}
	
	// Transfer Barang
	public function countAllDataTransferBarang() {
		$sql = 'SELECT COUNT(*) AS jml FROM transfer_barang AS tabel';
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListDataTransferBarang() 
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
		$sql = 'SELECT COUNT(*) AS jml
				FROM transfer_barang
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
		$sql = 'SELECT transfer_barang.*
						, (SELECT nama_gudang FROM gudang WHERE id_gudang = id_gudang_asal) AS nama_gudang_asal 
						, (SELECT nama_gudang FROM gudang WHERE id_gudang = id_gudang_tujuan) AS nama_gudang_tujuan
				FROM transfer_barang
				' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
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
				FROM barang ' . $where;
				
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
		
		$list_stok = $this->getListStokByIdBarang($id_barang);
		$list_harga = $this->getListHargaBarang($id_barang);
		
		// Merge
		foreach ($data as &$val) {
			$val['list_stok'] = $list_stok[$val['id_barang']];
			$val['list_harga'] = $list_harga[$val['id_barang']];
		}
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
	
	public function getListHargaBarang($id_barang) {
		
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
	
	public function getListStokByIdBarang($id_barang) {
		
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
}
?>