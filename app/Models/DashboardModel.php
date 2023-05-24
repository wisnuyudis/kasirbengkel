<?php
namespace App\Models;

class DashboardModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getListTahun() {
		$sql= 'SELECT YEAR(tgl_penjualan) AS tahun
				FROM penjualan
				GROUP BY tahun';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getTotalItemTerjual($tahun) 
	{
		$sql = 'SELECT jml, jml_prev, ROUND((jml - jml_prev)/ jml_prev * 100, 2) AS growth
				FROM (
					SELECT COUNT(IF(tgl_penjualan LIKE "' . $tahun . '%", id_barang, NULL)) AS jml,
							COUNT(IF(tgl_penjualan LIKE "' . ($tahun - 1) . '%", id_barang, NULL)) AS jml_prev	
					FROM penjualan_detail
					LEFT JOIN penjualan USING(id_penjualan)
					WHERE tgl_penjualan LIKE "' . $tahun . '%" OR tgl_penjualan LIKE "' . ($tahun - 1) . '%"
				) AS tabel';
		return $this->db->query($sql)->getRowArray();
	}
	
	public function getTotalJumlahTransaksi($tahun) 
	{
		$sql = 'SELECT jml, jml_prev, ROUND((jml - jml_prev)/ jml_prev * 100, 2) AS growth
				FROM (
					SELECT COUNT(IF(tgl_penjualan LIKE "' . $tahun . '%", id_penjualan, NULL)) AS jml,
							COUNT(IF(tgl_penjualan LIKE "' . ($tahun - 1) . '%", id_penjualan, NULL)) AS jml_prev
					FROM penjualan
					WHERE tgl_penjualan LIKE "' . $tahun . '%" OR tgl_penjualan LIKE "' . ($tahun - 1) . '%"
				) AS tabel';
		return $this->db->query($sql)->getRowArray();
	}
	
	public function getTotalNilaiPenjualan($tahun) {
		$sql = 'SELECT jml, jml_prev, ROUND((jml - jml_prev)/ jml_prev * 100, 2) AS growth
				FROM (
					SELECT SUM(IF(tgl_penjualan LIKE "' . $tahun . '%", neto, NULL)) AS jml,
							SUM(IF(tgl_penjualan LIKE "' . ($tahun - 1) . '%", neto, NULL)) AS jml_prev
					FROM penjualan
					WHERE tgl_penjualan LIKE "' . $tahun . '%" OR tgl_penjualan LIKE "' . ($tahun - 1) . '%"
				) AS tabel';
		return $this->db->query($sql)->getRowArray();
	}
	
	public function getTotalPelangganAktif($tahun) 
	{
		$sql = 'SELECT jml, jml_prev, ROUND( (jml-jml_prev) / jml_prev * 100 ) AS  growth, total FROM (
					SELECT COUNT(jml) AS jml, COUNT(jml_prev) AS jml_prev, (SELECT COUNT(*) FROM customer) AS total
					FROM (
						SELECT MAX(IF(tgl_penjualan LIKE "' . $tahun . '%", 1, NULL)) AS jml,
								MAX(IF(tgl_penjualan LIKE "' . ( $tahun - 1 ) . '%", 1, NULL)) AS jml_prev
						 FROM penjualan
						WHERE tgl_penjualan LIKE "' . $tahun . '%" OR tgl_penjualan LIKE "' . ($tahun - 1) . '%"
						GROUP BY id_customer
					) AS tabel
				) tabel_utama';
				
		return $this->db->query($sql)->getRowArray();
	}
		
	public function getSeriesPenjualan($list_tahun) {
		
		$result = [];
		foreach ($list_tahun as $tahun) {
			 $sql = 'SELECT MONTH(tgl_penjualan) AS bulan, COUNT(id_penjualan) as JML, SUM(neto) total
					FROM penjualan
					WHERE tgl_penjualan >= "' . $tahun . '-01-01" AND tgl_penjualan <= "' . $tahun . '-12-31"
					GROUP BY MONTH(tgl_penjualan)';
			
			$result[$tahun] = $this->db->query($sql)->getResultArray();
		}
		return $result;
	}
	
	public function getSeriesTotalPenjualan($list_tahun) {
		
		$result = [];
		foreach ($list_tahun as $tahun) {
			 $sql = 'SELECT SUM(neto) AS total
					FROM penjualan
					WHERE tgl_penjualan >= "' . $tahun . '-01-01" AND tgl_penjualan <= "' . $tahun . '-12-31"';
			
			$result[$tahun] = $this->db->query($sql)->getResultArray();
		}
		return $result;
	}
	
	public function getPiutangTerbesar () {
		$sql = 'SELECT id_customer, foto, nama_customer, SUM(kurang_bayar) AS total_kurang_bayar 
				FROM penjualan
				LEFT JOIN customer USING(id_customer)
				WHERE status = "kurang_bayar"
				GROUP BY id_customer
				ORDER BY total_kurang_bayar DESC
				LIMIT 5';
		return $this->db->query($sql)->getResultArray();
	}
	
	public function getPembelianPelangganTerbesar ($tahun) {
		$sql = 'SELECT id_customer, foto, nama_customer, SUM(neto) AS total_harga 
				FROM penjualan
				LEFT JOIN customer USING(id_customer)
				WHERE YEAR(tgl_penjualan) = ' . $tahun . '
				GROUP BY id_customer
				ORDER BY total_harga DESC
				LIMIT 5';
		return $this->db->query($sql)->getResultArray();
	}
	
	public function getItemTerjual($tahun) {
		$sql = 'SELECT id_barang, nama_barang, COUNT(id_barang) AS jml
				FROM penjualan_detail
				LEFT JOIN penjualan USING(id_penjualan)
				LEFT JOIN barang USING(id_barang)
				WHERE tgl_penjualan >= "' . $tahun . '-01-01" AND tgl_penjualan <= "' . $tahun . '-12-31"
				GROUP BY id_barang
				ORDER BY jml DESC LIMIT 5';
				
        $result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getKategoriTerjual($tahun) {
		$sql = 'SELECT id_barang_kategori, nama_kategori, COUNT(id_barang) AS jml, SUM(harga_neto) AS nilai
				FROM penjualan_detail
				LEFT JOIN penjualan USING(id_penjualan)
				LEFT JOIN barang USING(id_barang)
				LEFT JOIN barang_kategori USING(id_barang_kategori)
				WHERE tgl_penjualan >= "' . $tahun . '-01-01" AND tgl_penjualan <= "' . $tahun . '-12-31"
				GROUP BY id_barang_kategori
				ORDER BY nilai DESC LIMIT 5';
				
        $result = $this->db->query($sql)->getResultArray();
		return $result;
	}
		
	public function getItemTerbaru() {
		$sql = 'SELECT *, harga AS harga_jual FROM barang 
				LEFT JOIN barang_harga USING(id_barang)
				LEFT JOIN barang_image USING(id_barang)
				LEFT JOIN file_picker USING(id_file_picker)
				WHERE id_jenis_harga = 1 AND urut = 1
				ORDER BY barang.tgl_input DESC LIMIT 5';
		return $this->db->query($sql)->getResultArray();
	}
	
	public function penjualanTerbaru($tahun) {
		$sql = 'SELECT nama_customer, SUM(qty) AS jml_barang, MAX(neto) AS total_harga, tgl_invoice, MAX(tgl_penjualan) AS tgl_penjualan, kurang_bayar, status
				FROM penjualan 
				LEFT JOIN penjualan_detail USING(id_penjualan)
				LEFT JOIN customer USING(id_customer)
				WHERE tgl_penjualan LIKE "' . $tahun . '%"
				GROUP BY id_penjualan
				ORDER BY tgl_penjualan DESC LIMIT 50';
		
		return $this->db->query($sql)->getResultArray();
		
	}
	
	public function countAllDataPejualanTerbesar($tahun) {
		$sql = 'SELECT COUNT(*) as jml
				FROM (SELECT id_barang FROM penjualan_detail
					LEFT JOIN penjualan USING(id_penjualan)
					WHERE tgl_penjualan >= "' . $tahun . '-01-01" AND tgl_penjualan <= "' . $tahun . '-12-31"
					GROUP BY id_barang) AS tabel';
				
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListDataPenjualanTerbesar($tahun) {

		$columns = $this->request->getPost('columns');

		// Search
		$where = ' WHERE 1=1 ';
		$search_all1 = @$this->request->getPost('search')['value'];
		$search_all = str_replace(' ','%',$search_all1);
		if ($search_all) {

			foreach ($columns as $val) {
				
				if (strpos($val['data'], 'ignore') !== false)
					continue;
				
				$where_col[] = $val['data'] . ' LIKE "%' . $search_all . '%"';
			}
			 $where .= ' AND (' . join(' OR ', $where_col) . ') ';
		}
		
		// Order		
		$order_data = $this->request->getPost('order');
		$order = '';
		if (strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore_search') === false) {
			$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
			$order = ' ORDER BY ' . $order_by;
		}

		// Query Total Filtered
		$sql = '
				SELECT tabel_utama.*, COUNT(*) AS jml_data 
				FROM (
					SELECT tabel.*, ROUND(total_harga / total_penjualan * 100, 0) AS kontribusi 
					FROM (
						SELECT id_barang, nama_barang, harga_satuan, COUNT(id_barang) AS jml_terjual, SUM(harga_neto) AS total_harga,
							(SELECT SUM(harga_neto) FROM penjualan_detail LEFT JOIN penjualan USING(id_penjualan) WHERE tgl_penjualan >= "'. $tahun . '-01-01" AND tgl_penjualan <= "' . $tahun . '-12-31") AS total_penjualan
						FROM penjualan_detail
						LEFT JOIN penjualan USING(id_penjualan)
						LEFT JOIN barang USING(id_barang)
						 
						GROUP BY id_barang
					) AS tabel
				) AS tabel_utama
				' . $where;
				
		// echo $sql; die;
		$total_filtered = $this->db->query($sql)->getRowArray()['jml_data'];
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = '
				SELECT * FROM (
					SELECT tabel.*, ROUND(total_harga / total_penjualan * 100, 0) AS kontribusi 
					FROM (
						SELECT id_barang, nama_barang, harga_satuan, COUNT(id_barang) AS jml_terjual, SUM(harga_neto) AS total_harga,
							(SELECT SUM(harga_neto) FROM penjualan_detail LEFT JOIN penjualan USING(id_penjualan) WHERE tgl_penjualan >= "' . $tahun . '-01-01" AND tgl_penjualan <= "' . $tahun . '-12-31") AS total_penjualan
						FROM penjualan_detail
						LEFT JOIN penjualan USING(id_penjualan)
						LEFT JOIN barang USING(id_barang)
						WHERE tgl_penjualan >= "' . $tahun . '-01-01" AND tgl_penjualan <= "' . $tahun . '-12-31"
						GROUP BY id_barang
					) AS tabel
				) AS tabel_utama
				' . $where . $order . ' LIMIT ' . $start . ', ' . $length;

		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
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
	
	public function getListPenjualanTempo($setting_piutang) 
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
				' . $where . ( $this->setWhereJatuhTempo($setting_piutang) );
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
				' . $where . ( $this->setWhereJatuhTempo($setting_piutang) ) . $order . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql, [$_GET['start_date'], $_GET['end_date']])->getResultArray();
		
		// Query Total
		$sql = 'SELECT SUM(total_qty) AS total_qty, SUM(neto) AS total_neto 
				FROM penjualan 
				LEFT JOIN customer USING(id_customer)
				' . $where . ( $this->setWhereJatuhTempo($setting_piutang) );

		$total = $this->db->query($sql, [$_GET['start_date'], $_GET['end_date']])->getRowArray();
		if (!$total) {
			$total = ['total_qty' => 0, 'total_neto' => 0];
		}
		
		foreach ($data as &$val) {
			$val['total'] = $total;
		}
	
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
	
	private function setWhereJatuhTempo($setting_piutang) {
		$where_jatuh_tempo = '';
		if (!empty($_GET['jatuh_tempo'])) {
			$jatuh_tempo = $_GET['jatuh_tempo'];
			if ($jatuh_tempo == 'akan_jatuh_tempo') {
				$where_jatuh_tempo = ' AND tgl_penjualan < DATEDIFF(NOW(), tgl_penjualan) > ' . ( $setting_piutang['piutang_periode'] - $setting_piutang['notifikasi_periode']) . ' AND DATEDIFF(NOW(), tgl_penjualan) <= ' . $setting_piutang['piutang_periode'];
			} else if ($jatuh_tempo = 'lewat_jatuh_tempo'){
				$where_jatuh_tempo = ' AND tgl_penjualan < DATE_SUB(NOW(), INTERVAL ' . $setting_piutang['piutang_periode'] . ' DAY)';
			}
		}
		return $where_jatuh_tempo;
	}
}