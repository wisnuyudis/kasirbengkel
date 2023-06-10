<?php

/**
 *	App Name	: Aplikasi Kasir Berbasis Web	
 *	Developed by: Agus Prawoto Hadi
 *	Website		: https://jagowebdev.com
 *	Year		: 2022
 */

namespace App\Models;

class CustomerModel extends \App\Models\BaseModel
{
	public function __construct()
	{
		parent::__construct();
	}

	public function deleteData()
	{
		$result = $this->db->table('customer')->delete(['id_customer' => $_POST['id']]);
		return $result;
	}

	public function getCustomerById($id)
	{
		$sql = 'SELECT customer.*, wilayah_kelurahan.*, wilayah_kecamatan.*, wilayah_kabupaten.*, 
		   		wilayah_propinsi.*, b.id_gudang FROM customer 
				LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan) 
				LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
				LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)
				LEFT JOIN user b on b.id_user=customer.id_sales
				WHERE id_customer = ?';
		$result = $this->db->query($sql, trim($id))->getRowArray();
		return $result;
	}

	public function saveData()
	{

		$data_db['nama_customer'] = $_POST['nama_customer'];
		$data_db['alamat_customer'] = $_POST['alamat_customer'];
		$data_db['no_telp'] = $_POST['no_telp'];
		$data_db['email'] = $_POST['email'];
		$data_db['id_sales'] = $_POST['id_sales'];
		$data_db['nama_sales'] = $_POST['nama_sales'];
		$data_db['id_jenis_harga'] = $_POST['id_jenis_harga'];
		$data_db['nama_jenis_harga'] = $_POST['nama_jenis_harga'];
		$data_db['id_wilayah_kelurahan'] = $_POST['id_wilayah_kelurahan'];

		$new_name = '';
		$img_db['foto'] = '';

		$path = ROOTPATH . 'public/images/foto/';

		if (!empty($_POST['id'])) {
			$sql = 'SELECT foto FROM customer WHERE id_customer = ?';
			$img_db = $this->db->query($sql, $_POST['id'])->getRowArray();
			$new_name = $img_db['foto'];

			if ($_POST['foto_delete_img']) {
				$del = delete_file($path . $img_db['foto']);
				$new_name = '';
				if (!$del) {
					$data['msg']['message'] = 'Gagal menghapus gambar lama';
					$error = true;
				}
			}
		}

		$file = $this->request->getFile('foto');

		if ($file && $file->getName()) {
			//old file
			if ($_POST['id']) {
				if ($img_db['foto']) {
					if (file_exists($path . $img_db['foto'])) {
						$unlink = delete_file($path . $img_db['foto']);
						if (!$unlink) {
							$result['msg']['status'] = 'error';
							$result['msg']['content'] = 'Gagal menghapus gambar lama';
							return $result;
						}
					}
				}
			}

			helper('upload_file');
			$new_name =  get_filename($file->getName(), $path);
			$file->move($path, $new_name);

			if (!$file->hasMoved()) {
				$result['msg']['status'] = 'error';
				$result['msg']['content'] = 'Error saat memperoses gambar';
				return $result;
			}
		}

		$data_db['foto'] = $new_name;

		if (@$_POST['id']) {
			$query = $this->db->table('customer')->update($data_db, ['id_customer' => $_POST['id']]);
			$id_customer = $_POST['id'];
		} else {
			$query = $this->db->table('customer')->insert($data_db);
			$id_customer = $this->db->insertID();
		}

		if ($query) {
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
			$result['id_customer'] = $id_customer;
		} else {
			$result['status'] = 'error';
			$result['message'] = 'Data gagal disimpan';
		}

		return $result;
	}

	public function countAllData($where)
	{
		$sql = 'SELECT COUNT(*) AS jml FROM customer ' . $where;
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}

	public function getListData($where)
	{

		$columns = $this->request->getPost('columns');

		// Search
		$search_all1 = @$this->request->getPost('search')['value'];
		$search_all = str_replace(' ', '%', $search_all1);
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

	public function getuser()
	{
		$sql = 'SELECT * FROM user';
		$query = $this->db->query($sql)->getResultArray();
		// foreach ($query as $val) {
		// 	$result[$val['id_user']] = $val['nama'];
		// }
		return $query;
	}

	public function getjenisharga()
	{
		$sql = 'SELECT * FROM jenis_harga';
		$query = $this->db->query($sql)->getResultArray();
		// foreach ($query as $val) {
		// 	$result[$val['id_user']] = $val['nama'];
		// }
		return $query;
	}

	function cekrole()
	{
		$data = $this->db->query('select b.id_role, c.nama_role, a.id_gudang from user a 
		left join user_role b on b.id_user=a.id_user
		left join role c on c.id_role=b.id_role where a.id_user=' . $_SESSION['user']['id_user'])->getRowArray();
		return $data;
	}
}
