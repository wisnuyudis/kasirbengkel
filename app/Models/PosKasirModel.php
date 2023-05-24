<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022-2022
*/

namespace App\Models;

class PosKasirModel extends \App\Models\BaseModel
{
	public function getSettingPajak() {
		$sql = 'SELECT * FROM setting WHERE type="pajak"';
		$result = $this->db->query($sql)->getResultArray();
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
								LEFT JOIN (
								SELECT id_barang, SUM(adjusment_stok) AS stok 
								FROM barang_adjusment_stok LEFT JOIN gudang USING(id_gudang)
								WHERE id_gudang = ' . $id_gudang . ' AND adjusment_stok>"0"
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
				, (
					SELECT harga
					FROM barang_harga
					LEFT JOIN jenis_harga USING (id_jenis_harga)
					WHERE id_barang = barang.id_barang 
						AND jenis = "harga_pokok" 
					ORDER BY tgl_input DESC 
					LIMIT 1
				) AS harga_pokok				
				, (
					SELECT harga
					FROM barang_harga
					LEFT JOIN jenis_harga USING (id_jenis_harga)
					WHERE id_jenis_harga = "1" AND id_barang = barang.id_barang 
						AND jenis = "harga_jual" 
					ORDER BY tgl_input DESC 
					LIMIT 1
				) AS hargasatu
				, (
					SELECT harga
					FROM barang_harga
					LEFT JOIN jenis_harga USING (id_jenis_harga)
					WHERE id_jenis_harga = "2" AND id_barang = barang.id_barang 
						AND jenis = "harga_jual" 
					ORDER BY tgl_input DESC 
					LIMIT 1
				) AS hargadua
				, (
					SELECT harga
					FROM barang_harga
					LEFT JOIN jenis_harga USING (id_jenis_harga)
					WHERE id_jenis_harga = "3" AND id_barang = barang.id_barang 
						AND jenis = "harga_jual" 
					ORDER BY tgl_input DESC 
					LIMIT 1
				) AS hargatiga
				, (
					SELECT harga
					FROM barang_harga
					LEFT JOIN jenis_harga USING (id_jenis_harga)
					WHERE id_jenis_harga = "4" AND id_barang = barang.id_barang 
						AND jenis = "harga_jual" 
					ORDER BY tgl_input DESC 
					LIMIT 1
				) AS hargaempat
				, (
					SELECT harga
					FROM barang_harga
					LEFT JOIN jenis_harga USING (id_jenis_harga)
					WHERE id_jenis_harga = "5" AND id_barang = barang.id_barang 
						AND jenis = "harga_jual" 
					ORDER BY tgl_input DESC 
					LIMIT 1
				) AS hargalima
				FROM barang 
				LEFT JOIN (SELECT * FROM barang_image WHERE urut = 1) as barang_image USING(id_barang)
				LEFT JOIN file_picker USING(id_file_picker)
				' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
				

		$data = $this->db->query($sql)->getResultArray();
		
		// List stok
		$id_barang = [];
		foreach ($data as $val) {
			$id_barang[] = $val['id_barang'];
		}
		
		$list_stok = $list_harga = [];
		if ($id_barang) {
			$list_stok = $this->getListStokByIdBarang($id_barang);
			$list_harga = $this->getListHargaBarang($id_barang);
		}

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
		
		$list_id_barang = join(',', $id_barang);
		$sql = 'SELECT id_barang, id_gudang, SUM(saldo_stok) AS stok FROM (
					SELECT id_barang, id_gudang, adjusment_stok AS saldo_stok, "adjusment" AS jenis
					FROM barang_adjusment_stok
					WHERE id_barang IN (' . $list_id_barang . ') AND adjusment_stok>"0"
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
				GROUP BY id_barang, id_gudang';
		// echo $sql; die;
		$result = $this->db->query($sql)->getResultArray();
		$list_stok = [];
		foreach ($result as $val) {
			$list_stok[$val['id_barang']][$val['id_gudang']] = $val['stok'];
		}
		
		return $list_stok;
	}
}
?>