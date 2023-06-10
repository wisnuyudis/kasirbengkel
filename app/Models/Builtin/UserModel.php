<?php
namespace App\Models\Builtin;

class UserModel extends \App\Models\BaseModel
{
	public function getListUsers($where) {
		
		// Get user
		$columns = $this->request->getPost('columns');
		$order_by = '';
		
		// Search
		$search_all = @$this->request->getPost('search')['value'];
		if ($search_all) {
			
			foreach ($columns as $val) {
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
		if (!empty($_POST['columns']) && strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore') === false) {
			$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
			$order = ' ORDER BY ' . $order_by . ' LIMIT ' . $start . ', ' . $length;
		}
		
		$sql = 'SELECT COUNT(*) as jml FROM
				(SELECT user.*, GROUP_CONCAT(judul_role) AS judul_role FROM user 
				LEFT JOIN user_role USING(id_user) 
				LEFT JOIN role ON user_role.id_role = role.id_role
				' . $where . '
				GROUP BY id_user) AS tabel';
				
		$query = $this->db->query($sql)->getRowArray();
		$total_filtered = $query['jml'];
		
		$sql = 'SELECT user.*, GROUP_CONCAT(judul_role) AS judul_role, gudang.nama_gudang FROM user 
				LEFT JOIN user_role USING(id_user) 
				LEFT JOIN gudang USING(id_gudang) 
				LEFT JOIN role ON user_role.id_role = role.id_role
				' . $where . '
				GROUP BY id_user
				' . $order;
		
		
		$data = $this->db->query($sql)->getResultArray();
		return ['data' => $data, 'total_filtered' => $total_filtered];
		
	}
	
	public function countAllUsers($where = null) {
		$query = $this->db->query('SELECT COUNT(*) as jml FROM user' . $where)->getRow();
		return $query->jml;
	}
	
	public function getRoles() {
		$sql = 'SELECT * FROM role';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getSettingRegister() {
		$sql = 'SELECT * FROM setting WHERE type="register"';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getListModules() {
		
		$sql = 'SELECT * FROM module LEFT JOIN module_status USING(id_module_status) ORDER BY nama_module';
		return $this->db->query($sql)->getResultArray();
	}
		
	public function saveData($user_permission = []) 
	{ 
		$fields = ['nama', 'email', ];
		if (in_array('update_all', $user_permission)) {
			$add_field = ['username', 'status', 'verified', 'id_module', 'id_gudang'];
			$fields = array_merge($fields, $add_field);
		}

		foreach ($fields as $field) {
			$data_db[$field] = $this->request->getPost($field);
		}
		
		$this->db->transStart();
		
		if (!$this->request->getPost('id')) {
			$data_db['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
		}
		
		// Save database
		if ($this->request->getPost('id')) {
			$id_user = $this->request->getPost('id');
			$this->db->table('user')->update($data_db, ['id_user' => $id_user]);
		} else {
			$this->db->table('user')->insert($data_db);
			$id_user = $this->db->insertID();
		}
				
		if (in_array('update_all', $user_permission)) {
			$data_db = [];
			foreach ($_POST['id_role'] as $id_role) {
				$data_db[] = ['id_user' => $id_user, 'id_role' => $id_role];
			}
		
			$this->db->table('user_role')->delete(['id_user' => $id_user]);
			$this->db->table('user_role')->insertBatch($data_db);
		}
		
		$this->db->transComplete();
		$trans = $this->db->transStatus();
		
		$save = false;
		if ($trans) {
			
			$file = $this->request->getFile('avatar');
			$path = ROOTPATH . 'public/images/user/';
			
			$sql = 'SELECT avatar FROM user WHERE id_user = ?';
			$img_db = $this->db->query($sql, $id_user)->getRowArray();
			$new_name = $img_db['avatar'];
			
			if (!empty($_POST['avatar_delete_img'])) 
			{
				$del = delete_file($path . $img_db['avatar']);
				$new_name = '';
				if (!$del) {
					$result['message'] = 'Gagal menghapus gambar lama';
					$error = true;
				}
			}
					
			if ($file && $file->getName()) 
			{
				//old file
				if ($img_db['avatar']) {
					if (file_exists($path . $img_db['avatar'])) {
						$unlink = delete_file($path . $img_db['avatar']);
						if (!$unlink) {
							$result['msg']['status'] = 'error';
							$result['msg']['content'] = 'Gagal menghapus gambar lama';
						}
					}
				}
							
				helper('upload_file');
				$new_name =  get_filename($file->getName(), $path);
				$file->move($path, $new_name);
					
				if (!$file->hasMoved()) {
					$result['message'] = 'Error saat memperoses gambar';
					return $result;
				}
			}
			
			// Update avatar
			$data_db = [];
			$data_db['avatar'] = $new_name;
			$save = $this->db->table('user')->update($data_db, ['id_user' => $id_user]);
		}

		if ($save) {
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
			$result['id_user'] = $id_user;
			
			if ($this->session->get('user')['id_user'] == $id_user) {
				// Reload data user
				$this->session->set('user', $this->getUserById($this->session->get('user')['id_user']) );
			}
		} else {
			$result['status'] = 'error';
		}
								
		return $result;
	}
	
	public function deleteUser() 
	{
		$id_user = $this->request->getPost('id');
		$sql = 'SELECT * FROM user WHERE id_user = ?';
		$user = $this->db->query($sql, $id_user)->getRowArray();
		if (!$user) {
			return false;
		}
			
		$this->db->transStart();
		$this->db->table('user')->delete(['id_user' => $id_user]);
		$this->db->table('user_role')->delete(['id_user' => $id_user]);
		$delete = $this->db->affectedRows();
		$this->db->transComplete();
		$trans = $this->db->transStatus();
		
		if ($trans) {
			if (!empty($user['avatar'])) {
				delete_file(ROOTPATH . 'public/images/user/' . $user['avatar']);
			}
		}
		
		return true;
	}

	public function updatePassword() {
		$password_hash = password_hash($this->request->getPost('password_new'), PASSWORD_DEFAULT);
		$update = $this->db->query('UPDATE user SET password = ? 
									WHERE id_user = ? ', [$password_hash, $this->user['id_user']]
								);		
		return $update;
	}

	public function getgudang() {
		$sql = 'SELECT * FROM gudang';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
}
