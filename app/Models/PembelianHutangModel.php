<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022-2022
*/

namespace App\Models;

class PembelianHutangModel extends \App\Models\BaseModel
{
	public function getResumePembelianHutangByDate($start_date, $end_date, $setting_hutang) 
	{
		$sql = 'SELECT SUM(total_qty) AS total_qty, SUM(neto) AS total_neto, SUM(total_bayar) AS total_bayar, SUM(neto) - SUM(total_bayar) AS total_hutang
				FROM penjualan 
				WHERE jenis_bayar = "tempo" AND status = "kurang_bayar" AND tgl_invoice >= ? AND tgl_invoice <= ? ' . $this->setWhereJatuhTempo($setting_hutang);
		return $this->db->query($sql, [$start_date, $end_date])->getRowArray();
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
						
		$sql = 'SELECT nama_customer, no_invoice, tgl_invoice, neto, total_bayar, neto - total_bayar AS total_hutang 
				FROM penjualan
				LEFT JOIN customer USING(id_customer)
				WHERE jenis_bayar = "tempo" AND status = "kurang_bayar" AND tgl_invoice >= ? AND tgl_invoice <= ?';
				
		$query = $this->db->query($sql, [$start_date, $end_date]);
		
		$colls = [
					'no' 			=> ['type' => '#,##0', 'width' => 5, 'title' => 'No'],
					'nama_customer' => ['type' => 'string', 'width' => 30, 'title' => 'Nama Customer'],
					'no_invoice' 	=> ['type' => 'string', 'width' => 20, 'title' => 'No. Invoice'],
					'tgl_invoice' 	=> ['type' => 'date', 'width' => 13, 'title' => 'Tgl. Invoice'],
					'neto' 			=> ['type' => '#,##0', 'width' => 11, 'title' => 'Neto'],
					'total_bayar' 	=> ['type' => '#,##0', 'width' => 11, 'title' => 'Total Bayar'],
					'total_hutang' => ['type' => '#,##0', 'width' => 11, 'title' => 'Hutang']
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
	
	public function getPenjualanTempoByDate($start_date, $end_date) {
		$sql = 'SELECT nama_customer, no_invoice, tgl_invoice, sub_total, total_diskon, neto, total_bayar, neto - total_bayar AS total_hutang, status 
				FROM penjualan
				LEFT JOIN customer USING(id_customer)
				WHERE jenis_bayar="tempo" AND status = "kurang_bayar" AND  tgl_invoice >= ? AND tgl_invoice <= ?';
				
		$result = $this->db->query($sql, [$start_date, $end_date])->getResultArray();
		return $result;
	}
	
	// Penjualan
	public function countAllDataPenjualanTempo($setting_piutang) {
			
		$sql = 'SELECT COUNT(*) AS jml 
				FROM penjualan AS tabel 
				WHERE jenis_bayar = "tempo" 
					AND status = "kurang_bayar" 
					AND  tgl_invoice >= ? 
					AND tgl_invoice <= ?
					' . $this->setWhereJatuhTempo($setting_piutang) . '
				';
		$result = $this->db->query($sql, [$_GET['start_date'], $_GET['end_date']])->getRow();
		return $result->jml;
	}
	
	public function getListPenjualanTempo($setting_hutang) 
	{

		$columns = $this->request->getPost('columns');

		// Search
		$search_all1 = @$this->request->getPost('search')['value'];
		$search_all = str_replace(' ','%',$search_all1);
		$where = ' WHERE jenis_bayar = "tempo" AND status = "kurang_bayar" AND  tgl_invoice >= ? AND tgl_invoice <= ? ';
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
				' . $where . ( $this->setWhereJatuhTempo($setting_hutang) );
		$data = $this->db->query($sql, [$_GET['start_date'], $_GET['end_date']])->getRowArray();
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
				' . $where . ( $this->setWhereJatuhTempo($setting_hutang) ) . $order . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql, [$_GET['start_date'], $_GET['end_date']])->getResultArray();
		
		// Query Total
		$sql = 'SELECT SUM(total_qty) AS total_qty, SUM(neto) AS total_neto 
				FROM penjualan 
				LEFT JOIN customer USING(id_customer)
				' . $where . ( $this->setWhereJatuhTempo($setting_hutang) );

		$total = $this->db->query($sql, [$_GET['start_date'], $_GET['end_date']])->getRowArray();
		if (!$total) {
			$total = ['total_qty' => 0, 'total_neto' => 0];
		}
		
		foreach ($data as &$val) {
			$val['total'] = $total;
		}
	
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
	
	private function setWhereJatuhTempo($setting_hutang) {
		$where_jatuh_tempo = '';
		if (!empty($_GET['jatuh_tempo'])) {
			$jatuh_tempo = $_GET['jatuh_tempo'];
			if ($jatuh_tempo == 'akan_jatuh_tempo') {
				$where_jatuh_tempo = ' AND tgl_penjualan < DATEDIFF(NOW(), tgl_penjualan) > ' . ( $setting_hutang['hutang_periode'] - $setting_hutang['notifikasi_periode']) . ' AND DATEDIFF(NOW(), tgl_penjualan) <= ' . $setting_hutang['hutang_periode'];
			} else if ($jatuh_tempo = 'lewat_jatuh_tempo'){
				$where_jatuh_tempo = ' AND tgl_penjualan < DATE_SUB(NOW(), INTERVAL ' . $setting_hutang['hutang_periode'] . ' DAY)';
			}
		}
		return $where_jatuh_tempo;
	}
}
?>