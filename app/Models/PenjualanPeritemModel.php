<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022-2022
*/

namespace App\Models;

class PenjualanPeritemModel extends \App\Models\BaseModel
{
	public function getPenjualanBarangByDate($start_date, $end_date) {
		$sql = 'SELECT nama_barang, harga_satuan, qty, penjualan_detail.diskon, harga_neto, penjualan_detail.untung_rugi, tgl_penjualan 
				FROM penjualan_detail
				LEFT JOIN barang USING(id_barang)
				LEFT JOIN penjualan USING(id_penjualan)
				LEFT JOIN customer USING(id_customer)
				WHERE tgl_invoice >= ? AND tgl_invoice <= ?';
				
		$result = $this->db->query($sql, [$start_date, $end_date])->getResultArray();
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
	
	public function writeExcel($start_date, $end_date) 
	{
		require_once(ROOTPATH . "/app/ThirdParty/PHPXlsxWriter/xlsxwriter.class.php");
				
		
		$sql = 'SELECT nama_barang, harga_satuan, qty, harga_neto, penjualan_detail.untung_rugi, tgl_penjualan 
				FROM penjualan_detail
				LEFT JOIN barang USING(id_barang)
				LEFT JOIN penjualan USING(id_penjualan)
				LEFT JOIN customer USING(id_customer)
				WHERE tgl_invoice >= ? AND tgl_invoice <= ?';
				
		$query = $this->db->query($sql, [$start_date, $end_date]);
		
		$colls = [
					'no' 			=> ['type' => '#,##0', 'width' => 5, 'title' => 'No'],
					'nama_barang' 	=> ['type' => 'string', 'width' => 30, 'title' => 'Nama Barang'],
					'harga_satuan' 	=> ['type' => '#,##0', 'width' => 12, 'title' => 'Harga Satuan'],
					'qty' 			=> ['type' => '#,##0', 'width' => 5, 'title' => 'Qty'],
					'harga_neto' 	=> ['type' => '#,##0', 'width' => 12, 'title' => 'Neto'],
					'untung_rugi' 	=> ['type' => '#,##0', 'width' => 12, 'title' => 'Untung (Rugi)'],
					'tgl_penjualan' => ['type' => 'datetime', 'width' => 19, 'title' => 'Tgl. Penjualan'],
				
				];
		
		$col_type = $col_width = $col_header = [];
		foreach ($colls as $field => $val) {
			$col_type[$field] = $val['type'];
			$col_header[$field] = $val['title'];
			$col_header_type[$field] = 'string';
			$col_width[] = $val['width'];
		}
		
		// Excel
		$sheet_name = strtoupper('Penjualan Barang');
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
		
		$tmp_file = ROOTPATH . 'public/tmp/penjualan_barang_' . time() . '.xlsx.tmp';
		$writer->writeToFile($tmp_file);
		return $tmp_file;
	}
	
	public function getResumePenjualanByDate($start_date, $end_date) {		
		$sql = 'SELECT SUM(total_qty) AS total_qty, SUM(neto) AS total_neto, SUM(untung_rugi) AS total_untung_rugi
				FROM penjualan 
				WHERE tgl_invoice >= ? AND tgl_invoice <= ?';
		return $this->db->query($sql, [$start_date, $end_date])->getRowArray();
	}
	
	// Penjualan
	public function countAllDataPenjualan() {
		$sql = 'SELECT COUNT(*) AS jml FROM penjualan_detail 
				LEFT JOIN penjualan USING(id_penjualan) 
				WHERE tgl_invoice >= ? AND tgl_invoice <= ?';
		$result = $this->db->query($sql, [$_GET['start_date'], $_GET['end_date']])->getRow();
		return $result->jml;
	}
	
	public function getListPenjualan() 
	{

		$columns = $this->request->getPost('columns');

		// Search
		$search_all1 = @$this->request->getPost('search')['value'];
		$search_all = str_replace(' ','%',$search_all1);
		$where = ' WHERE tgl_invoice >= ? AND tgl_invoice <= ? ';
		if ($search_all) {
			foreach ($columns as $val) {
				
				if (strpos($val['data'], 'ignore_search') !== false) 
					continue;
				
				if (strpos($val['data'], 'ignore') !== false)
					continue;
				
				if ($val['data'] == 'diskon') {
					$val['data'] = 'penjualan_detail.diskon';
				}
				
				$where_col[] = $val['data'] . ' LIKE "%' . $search_all . '%"';
			}
			 $where .= ' AND (' . join(' OR ', $where_col) . ') ';
		}
		
		// Query Total Filtered
		$sql = 'SELECT COUNT(*) AS jml FROM penjualan_detail
				LEFT JOIN barang USING(id_barang)
				LEFT JOIN penjualan USING(id_penjualan)
				LEFT JOIN customer USING(id_customer)
				' . $where;
		$data = $this->db->query($sql, [$_GET['start_date'], $_GET['end_date']])->getRowArray();
		$total_filtered = $data['jml'];
		
		// Order
		$order_data = $this->request->getPost('order');
		$order = '';
		if (strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore_search') === false) {
			$table_column = $columns[$order_data[0]['column']]['data'];
			if ($columns[$order_data[0]['column']]['data'] == 'diskon') {
				$table_column = 'penjualan_detail.diskon';
			}
			$order_by = $table_column . ' ' . strtoupper($order_data[0]['dir']);
			$order = ' ORDER BY ' . $order_by;
		}

		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		
		// Query Data
		$sql = 'SELECT * FROM penjualan_detail
				LEFT JOIN barang USING(id_barang)
				LEFT JOIN penjualan USING(id_penjualan)
				LEFT JOIN customer USING(id_customer)
				' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql, [$_GET['start_date'], $_GET['end_date']])->getResultArray();
		
		// Query Total
		$sql = 'SELECT SUM(qty) AS total_qty, SUM(harga_neto) AS total_neto 
				FROM penjualan_detail
				LEFT JOIN barang USING(id_barang)
				LEFT JOIN penjualan USING(id_penjualan)
				LEFT JOIN customer USING(id_customer)
				' . $where;
		$total = $this->db->query($sql, [$_GET['start_date'], $_GET['end_date']])->getRowArray();
		if (!$total) {
			$total = ['total_qty' => 0, 'total_neto' => 0];
		}
		
		foreach ($data as &$val) {
			$val['total'] = $total;
		}
	
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}
?>