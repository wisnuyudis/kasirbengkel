<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022-2022
*/

namespace App\Models;

class BarangModel extends \App\Models\BaseModel
{
	
	private function sqlQuery() 
	{
		$sql = 'SELECT kode_barang, nama_barang, deskripsi, barcode, satuan, total_stok
				FROM barang 
				LEFT JOIN satuan_unit USING(id_satuan_unit)
				LEFT JOIN (
					SELECT *, SUM(saldo_stok) AS total_stok FROM (
						SELECT id_barang, id_gudang, adjusment_stok AS saldo_stok, "adjusment" AS jenis
						FROM barang_adjusment_stok
							UNION ALL
						SELECT id_barang, id_gudang, CAST(qty as SIGNED) * -1 AS saldo_stok, "penjualan" AS jenis
						FROM penjualan_detail LEFT JOIN penjualan USING(id_penjualan)
							UNION ALL
						SELECT id_barang, id_gudang, qty_retur AS saldo_stok, "penjualan_retur" AS jenis
						FROM penjualan_retur_detail LEFT JOIN penjualan_detail USING(id_penjualan_detail) LEFT JOIN penjualan USING(id_penjualan)
							UNION ALL
						SELECT id_barang, id_gudang, qty AS saldo_stok, "pembelian" AS jenis
						FROM pembelian_detail LEFT JOIN pembelian USING(id_pembelian)
							UNION ALL
						SELECT id_barang, id_gudang, CAST(qty_retur AS SIGNED) * -1 AS saldo_stok, "pembelian_retur" AS jenis
						FROM pembelian_retur_detail LEFT JOIN pembelian_detail USING(id_pembelian_detail) LEFT JOIN pembelian USING(id_pembelian)
							UNION ALL
						SELECT id_barang, id_gudang_asal, CAST(qty_transfer AS SIGNED) * -1 AS saldo_stok, "transfer_keluar" AS jenis 
						FROM transfer_barang_detail
						LEFT JOIN transfer_barang USING (id_transfer_barang)
							UNION ALL
						SELECT id_barang, id_gudang_tujuan, qty_transfer AS saldo_stok, "transfer_masuk" AS jenis 
						FROM transfer_barang_detail
						LEFT JOIN transfer_barang USING (id_transfer_barang)
					) AS tabel
					GROUP BY id_barang
				) AS tabel_stok USING(id_barang)';
		return $sql;
	}
	
	public function getDataBarang() {
		$sql = $this->sqlQuery();		
		return $this->db->query($sql)->getResultArray();
	}
	
	public function writeExcel() 
	{
		require_once(ROOTPATH . "/app/ThirdParty/PHPXlsxWriter/xlsxwriter.class.php");
						
		$sql = $this->sqlQuery();		
		$query = $this->db->query($sql);
		
		$colls = [
					'no' 			=> ['type' => '#,##0', 'width' => 5, 'title' => 'No'],
					'kode_barang' 	=> ['type' => 'string', 'width' => 10, 'title' => 'Kode Barang'],
					'nama_barang' 	=> ['type' => 'string', 'width' => 30, 'title' => 'Nama Barang'],
					'deskripsi' 	=> ['type' => 'string', 'width' => 30, 'title' => 'Deskripsi'],
					'barcode' 		=> ['type' => 'string', 'width' => 15, 'title' => 'Barcode'],
					'satuan' 		=> ['type' => 'string', 'width' => 5, 'title' => 'Satuan'],
					'total_stok' 	=> ['type' => '#,##0', 'width' => 7, 'title' => 'Stok']
				];
		
		$col_type = $col_width = $col_header = [];
		foreach ($colls as $field => $val) {
			$col_type[$field] = $val['type'];
			$col_header[$field] = $val['title'];
			$col_header_type[$field] = 'string';
			$col_width[] = $val['width'];
		}
		
		// Excel
		$sheet_name = strtoupper('Daftar Barang');
		$writer = new \XLSXWriter();
		$writer->setAuthor('Jagowebdev');
		
		$writer->writeSheetHeader($sheet_name, $col_header_type, $col_options = ['widths'=> $col_width, 'suppress_row'=>true]);
		$writer->writeSheetRow($sheet_name, $col_header);
		$writer->updateFormat($sheet_name, $col_type);
		
		$no = 1;
		while ($row = $query->getUnbufferedRow('array')) {
			array_unshift($row, $no);
			$writer->writeSheetRow($sheet_name, $row);
			$no++;
		}
		
		$tmp_file = ROOTPATH . 'public/tmp/barang_' . time() . '.xlsx.tmp';
		$writer->writeToFile($tmp_file);
		return $tmp_file;
	}
	
	public function deleteData() 
	{
		$this->db->transBegin();
		$this->db->table('barang')->delete(['id_barang' => $_POST['id']]);
		$this->db->table('barang_adjusment_stok')->delete(['id_barang' => $_POST['id']]);
		$this->db->table('barang_harga')->delete(['id_barang' => $_POST['id']]);
		$this->db->table('barang_image')->delete(['id_barang' => $_POST['id']]);
		$this->db->transComplete();
		return $this->db->transStatus();
	}
	
	public function getAllGudang() {
		$sql = 'SELECT * FROM gudang';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getAllSatuan() {
		$sql = 'SELECT * FROM satuan_unit';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getBarangById($id) {
		$sql = 'SELECT * FROM barang WHERE id_barang = ?';
		$barang = $this->db->query($sql, trim($id))->getRowArray();
		if ($barang) {
			$sql_image = 'SELECT * FROM barang_image LEFT JOIN file_picker USING(id_file_picker) WHERE id_barang = ? ORDER BY urut';
			$images = $this->db->query($sql_image, $barang['id_barang'])->getResultArray();
			$barang['images'] = $images;
		}
		return $barang;
	}
	
	public function getKategori() 
	{
		$result = [];
		
		$sql = 'SELECT * FROM barang_kategori
				ORDER BY urut';
		
		$kategori = $this->db->query($sql)->getResultArray();

		foreach ($kategori as $val) 
		{
			$result[$val['id_barang_kategori']] = $val;
			$result[$val['id_barang_kategori']]['depth'] = 0;			
		}		
		return $result;
	}
	
	public function getStok($id = 0) {
		
		if (!$id)
			return;
		
		$sql = 'SELECT *, SUM(saldo_stok) AS total_stok FROM (
					SELECT id_barang, id_gudang, adjusment_stok AS saldo_stok, "adjusment" AS jenis
					FROM barang_adjusment_stok
					WHERE id_barang = ' . $id . '
						UNION ALL
					SELECT id_barang, id_gudang, CAST(qty as SIGNED) * -1 AS saldo_stok, "penjualan" AS jenis
					FROM penjualan_detail LEFT JOIN penjualan USING(id_penjualan)
					WHERE id_barang = ' . $id . '
						UNION ALL
					SELECT id_barang, id_gudang, qty_retur AS saldo_stok, "penjualan_retur" AS jenis
					FROM penjualan_retur_detail LEFT JOIN penjualan_detail USING(id_penjualan_detail) LEFT JOIN penjualan USING(id_penjualan)
					WHERE id_barang = ' . $id . '
						UNION ALL
					SELECT id_barang, id_gudang, qty AS saldo_stok, "pembelian" AS jenis
					FROM pembelian_detail LEFT JOIN pembelian USING(id_pembelian)
					WHERE id_barang = ' . $id . '
						UNION ALL
					SELECT id_barang, id_gudang, CAST(qty_retur AS SIGNED) * -1 AS saldo_stok, "pembelian_retur" AS jenis
					FROM pembelian_retur_detail LEFT JOIN pembelian_detail USING(id_pembelian_detail) LEFT JOIN pembelian USING(id_pembelian)
					WHERE id_barang = ' . $id . '
						UNION ALL
					SELECT id_barang, id_gudang_asal, CAST(qty_transfer AS SIGNED) * -1 AS saldo_stok, "transfer_keluar" AS jenis 
					FROM transfer_barang_detail
					LEFT JOIN transfer_barang USING (id_transfer_barang)
					WHERE id_barang = ' . $id . '
						UNION ALL
					SELECT id_barang, id_gudang_tujuan, qty_transfer AS saldo_stok, "transfer_masuk" AS jenis 
					FROM transfer_barang_detail
					LEFT JOIN transfer_barang USING (id_transfer_barang)
					WHERE id_barang = ' . $id . '
				) AS tabel
				LEFT JOIN gudang USING(id_gudang)
				GROUP BY id_barang, id_gudang';
	
		$result = $this->db->query($sql, $id)->getResultArray();
		return $result;
	}
	
	public function getHargaPokokByIdBarang($id) 
	{
		$sql = 'SELECT harga FROM barang_harga
				WHERE id_barang = ? AND jenis = "harga_pokok"
				ORDER BY tgl_input DESC LIMIT 1';
				
		$result = $this->db->query($sql, $id)->getRowArray();
		if ($result) 
			return $result['harga'];
		return $result;
	}
	
	public function getBarangByBarcode($code) 
	{		
		$sql = 'SELECT barcode FROM barang
				WHERE barcode = ?';
				
		$result = $this->db->query($sql, $code)->getRowArray();
		return $result;
	}
	
	public function getHargaJualByIdBarang($id) 
	{
		$sql = 'SELECT *, (SELECT harga  FROM barang_harga
					WHERE id_jenis_harga = jenis_harga.id_jenis_harga AND id_barang = ? AND jenis = "harga_jual"
					ORDER BY tgl_input DESC LIMIT 1
				) AS harga
				FROM jenis_harga ';
		$result = $this->db->query($sql, $id)->getResultArray();
		return $result;
	}
	
	public function saveData() 
	{
		$data_db['kode_barang'] = $_POST['kode_barang'];
		$data_db['nama_barang'] = $_POST['nama_barang'];
		$data_db['deskripsi'] = $_POST['deskripsi'];
		$data_db['id_barang_kategori'] = $_POST['id_barang_kategori'];
		$data_db['id_satuan_unit'] = $_POST['id_satuan_unit'];
		$data_db['berat'] = str_replace('.', '', $_POST['berat']);
		$data_db['barcode'] = $_POST['barcode'];
		
		$this->db->transStart();
		
		if ($_POST['id']) 
		{
			$data_db['id_user_edit'] = $_SESSION['user']['id_user'];
			$data_db['tgl_edit'] = date('Y-m-d H:i:s');
			$query = $this->db->table('barang')->update($data_db, ['id_barang' => $_POST['id']]);
			$id_barang = $_POST['id'];
		} else {
			$data_db['id_user_input'] = $_SESSION['user']['id_user'];
			$data_db['tgl_input'] = date('Y-m-d H:i:s');
			$query = $this->db->table('barang')->insert($data_db);
			$id_barang = $this->db->insertID();
		}
		
		if ($query) 
		{
			// Image
			if ($_POST['id']) {
				$this->db->table('barang_image')->delete(['id_barang' => $id_barang]);
			}
	
			$data_db = [];
			foreach ($_POST['id_file_picker'] as $index => $val) {
				if (!$val)
					continue;
				$data_db[] = ['id_file_picker' => $val, 'id_barang' => $id_barang, 'urut' => ($index + 1) ];
			}
			if ($data_db) {
				$this->db->table('barang_image')->insertBatch($data_db);
			}
			
			// Adjusment stok
			$data_db = [];
			foreach ($_POST['adjusment'] as $index => $val) {
				if (!$val)
					continue;
			
				$val = str_replace('.', '', $val);
				if ($val != 0) {
					$data_db[] = ['id_barang' => $id_barang, 'id_gudang' => $_POST['id_gudang'][$index], 'adjusment_stok' => $val, 'tgl_input' => date('Y-m-d H:i:s'), 'id_user_input' => $_SESSION['user']['id_user']];
				}
			}
			if ($data_db) {
				$this->db->table('barang_adjusment_stok')->insertBatch($data_db);
			}
			
			// Harga Pokok
			if ($_POST['adjusment_harga_pokok']) {
				$val = str_replace('.', '',  $_POST['harga_pokok']);
				if ($val==1){
				$data_db = [
								'id_barang' => $id_barang, 
								'harga' => '0', 
								'jenis' => 'harga_pokok', 
								'tgl_input' => date('Y-m-d H:i:s'), 
								'id_user_input' => $_SESSION['user']['id_user']
							];
				$this->db->table('barang_harga')->delete(['id_barang' => $id_barang, 'jenis' => 'harga_pokok']);
				$this->db->table('barang_harga')->insert($data_db);
						}elseif ($val!=1){
							$data_db = [
								'id_barang' => $id_barang, 
								'harga' => str_replace('.', '', $_POST['adjusment_harga_pokok']), 
								'jenis' => 'harga_pokok', 
								'tgl_input' => date('Y-m-d H:i:s'), 
								'id_user_input' => $_SESSION['user']['id_user']
							];
				$this->db->table('barang_harga')->delete(['id_barang' => $id_barang, 'jenis' => 'harga_pokok']);
				$this->db->table('barang_harga')->insert($data_db);
						}
			}
				
			// Harga jual
			$data_db = [];
			foreach ($_POST['harga_jual'] as $index => $val) 
			{
				$val = str_replace('.', '', $val);
				// if ($val != $_POST['harga_awal'][$index]) {
					$data_db[] = [
									'id_barang' => $id_barang, 
									'id_jenis_harga' => $_POST['id_jenis_harga'][$index], 
									'harga' => $val, 
									'jenis' => 'harga_jual', 
									'tgl_input' => date('Y-m-d H:i:s'), 
									'id_user_input' => $_SESSION['user']['id_user']
								];
				// }
			}
			if ($data_db) {
				$this->db->table('barang_harga')->delete(['id_barang' => $id_barang, 'jenis' => 'harga_jual']);
				$this->db->table('barang_harga')->insertBatch($data_db);
			}
			
		}
		
		$this->db->transComplete();
		if ($this->db->transStatus()) {
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
			$result['id'] = $id_barang;
		} else {
			$result['status'] = 'error';
			$result['message'] = 'Data gagal disimpan';
		}
		
		return $result;
	}
	
	public function saveDataStok() 
	{
		$id_barang = $_POST['id'];
		$data_db = [];
		
		foreach ($_POST['adjusment'] as $index => $val) {
			$val = str_replace('.', '', $val);
			if ($val != 0) {
				$data_db[] = ['id_barang' => $id_barang, 'id_gudang' => $_POST['id_gudang'][$index], 'adjusment_stok' => $val, 'tgl_input' => date('Y-m-d H:i:s'), 'id_user_input' => $_SESSION['user']['id_user']];
			}
		}
		
		if ($data_db) {
			$query = $this->db->table('barang_adjusment_stok')->insertBatch($data_db);
		}
			
		if ($query) {
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
		} else {
			$result['status'] = 'error';
			$result['message'] = 'Data gagal disimpan';
		}
		
		return $result;
	}
	
	public function countAllData() {
		$sql = 'SELECT COUNT(*) AS jml FROM barang';
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListData() {

		$columns = $this->request->getPost('columns');

		// Search
		$where = ' WHERE 1=1 ';
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
		if (@strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore_search') === false) {
			$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
			$order = ' ORDER BY ' . $order_by;
		}

		// Query Total Filtered
		$sql = 'SELECT COUNT(*) AS jml_data FROM barang 
				LEFT JOIN satuan_unit USING(id_satuan_unit) 
				' . $where;
		$total_filtered = $this->db->query($sql)->getRowArray()['jml_data'];
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = 'SELECT * FROM barang
				LEFT JOIN satuan_unit USING(id_satuan_unit)
				LEFT JOIN (
					SELECT id_barang, SUM(saldo_stok) AS stok FROM (
						SELECT id_barang, id_gudang, adjusment_stok AS saldo_stok, "adjusment" AS jenis
						FROM barang_adjusment_stok
							UNION ALL
						SELECT id_barang, id_gudang, CAST(qty as SIGNED) * -1 AS saldo_stok, "penjualan" AS jenis
						FROM penjualan_detail LEFT JOIN penjualan USING(id_penjualan)
							UNION ALL
						SELECT id_barang, id_gudang, qty_retur AS saldo_stok, "penjualan_retur" AS jenis
						FROM penjualan_retur_detail LEFT JOIN penjualan_detail USING(id_penjualan_detail) LEFT JOIN penjualan USING(id_penjualan)
							UNION ALL
						SELECT id_barang, id_gudang, qty AS saldo_stok, "pembelian" AS jenis
						FROM pembelian_detail LEFT JOIN pembelian USING(id_pembelian)
							UNION ALL
						SELECT id_barang, id_gudang, CAST(qty_retur AS SIGNED) * -1 AS saldo_stok, "pembelian_retur" AS jenis
						FROM pembelian_retur_detail LEFT JOIN pembelian_detail USING(id_pembelian_detail) LEFT JOIN pembelian USING(id_pembelian)
					) AS tabel
					GROUP BY id_barang
				) AS tabel_stok USING(id_barang)
				' . $where . $order  . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}
?>