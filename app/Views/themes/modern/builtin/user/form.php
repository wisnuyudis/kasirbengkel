<?php
if (@$_GET['mobile'] == 'true') {
	echo $this->extend('themes/modern/layout-mobile');
	echo $this->section('content');
}
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	<div class="card-body">
		<?php 
			helper('html');
			helper('builtin/util');
			if (empty($_GET['mobile'])) {
				if (in_array('create', $user_permission)) {
					echo btn_link(['attr' => ['class' => 'btn btn-success btn-xs'],
						'url' => $module_url . '/add',
						'icon' => 'fa fa-plus',
						'label' => 'Tambah User'
					]);
				}
				
				echo btn_link(['attr' => ['class' => 'btn btn-light btn-xs'],
					'url' => $module_url,
					'icon' => 'fa fa-arrow-circle-left',
					'label' => 'Daftar User'
				]);
				
				echo '<hr/>';
			}

			if (!empty($message)) {
				show_message($message);
			}
		?>
		<form method="post" action="" class="form-horizontal" enctype="multipart/form-data">
			<div class="tab-content">
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Foto</label>
					<div class="col-sm-8 col-md-6 col-lg-4">
						<?php 
						$avatar = @$_FILES['file']['name'] ?: @$user_edit['avatar'];
						if (!empty($avatar) ) {
							echo '<div class="img-choose" style="margin:inherit;margin-bottom:10px">
									<div class="img-choose-container">
										<img src="'.$config->baseURL. '/public/images/user/' . $avatar . '?r=' . time() . '"/>
										<a href="javascript:void(0)" class="remove-img"><i class="fas fa-times"></i></a>
									</div>
								</div>
								';
						}
						?>
						<input type="hidden" class="avatar-delete-img" name="avatar_delete_img" value="0">
						<input type="file" class="file form-control" name="avatar">
							<?php if (!empty($form_errors['avatar'])) echo '<small style="display:block" class="alert alert-danger mb-0">' . $form_errors['avatar'] . '</small>'?>
						<small class="small" style="display:block">Maksimal 300Kb, Minimal 100px x 100px, Tipe file: .JPG, .JPEG, .PNG</small>
						<div class="upload-img-thumb mb-2"><span class="img-prop"></span></div>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Username</label>
					<div class="col-sm-8 col-md-6 col-lg-4">
						<?php 
						$readonly = 'readonly="readonly" class="disabled"';
						if (@$user_permission['update_all']) {
							$readonly = '';
						}
						?>
						<input class="form-control" type="text" name="username" <?=$readonly?> value="<?=set_value('username', @$user_edit['username'])?>" placeholder="" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama</label>
					<div class="col-sm-8 col-md-6 col-lg-4">
						<input class="form-control" type="text" name="nama" value="<?=set_value('nama', @$user_edit['nama'])?>" placeholder="" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Email</label>
					<div class="col-sm-8 col-md-6 col-lg-4">
						<input class="form-control" type="text" name="email" value="<?=set_value('email', @$user_edit['email'])?>" placeholder="" required="required"/>
						<input type="hidden" name="email_lama" value="<?=set_value('email', @$user_edit['email'])?>" />
					</div>
				</div>
				<?php
				if (@$user_permission['update_all']) {
					?>
					<div class="row mb-3">
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Verified</label>
						<div class="col-sm-8 col-md-6 col-lg-4">
							<?php
							if (!isset($user_edit['verified']) && !key_exists('verified', $_POST) ) {
								$selected = 1;
							} else {
								$selected = set_value('verified', @$user_edit['verified']);
							}
							?>
							<?php echo options(['name' => 'verified'], [1=>'Ya', 0 => 'Tidak'], $selected); ?>
						</div>
					</div>
					<div class="row mb-3">
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Status</label>
						<div class="col-sm-8 col-md-6 col-lg-4">
							<?php echo options(['name' => 'status'], [1 => 'Aktif', 2 => 'Suspended', 3 => 'Deleted'], set_value('status', @$user_edit['status'])); ?>
						</div>
					</div>
					<div class="row mb-3">
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Role</label>
						<div class="col-sm-8 col-md-6 col-lg-4">
							<?php
							foreach ($roles as $key => $val) {
								$options[$val['id_role']] = $val['judul_role'];
							}
							
							if (!empty($user_edit['role'])) {
								foreach ($user_edit['role'] as $val) {
									$id_role_selected[] = $val['id_role'];
								}
							}
							
							echo options(['name' => 'id_role[]', 'multiple' => 'multiple', 'class' => 'select2'], $options, set_value('id_role', @$id_role_selected));
							?>
						</div>
					</div>
					<div class="row mb-3">
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Halaman Default</label>
						<div class="col-sm-8 col-md-6 col-lg-4">
							<?php
							foreach ($list_module as $val) {
								$options[$val['id_module']] = $val['nama_module'] . ' - ' . $val['judul_module'];
							}
							if (empty($user_edit) && empty($_POST)) {
								$selected = $setting_registrasi['id_module'];
							} else {
								$selected = set_value('id_module', @$user_edit['id_module']);
							}
							echo options(['name' => 'id_module'], $options, set_value('id_module', $selected)); 
							?>
							<span class="text-muted">Pastikan user memiliki hak akses ke module</span>
						</div>
					</div>
				<?php
				}
				
				if (empty($user_edit['id_user'])) {
					?>
					<div class="row mb-3">
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Password Baru</label>
						<div class="col-sm-8 col-md-6 col-lg-4">
							<input class="form-control" type="password" name="password" required="required"/>
						</div>
					</div>
					<div class="row mb-3">
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Ulangi Password Baru</label>
						<div class="col-sm-8 col-md-6 col-lg-4">
							<input class="form-control" type="password" name="ulangi_password" required="required"/>
						</div>
					</div>
				<?php
				}
				?>
				<div class="row">
					<div class="col-sm-8">
						<button id="btn-submit-edit-profile" type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
						<input type="hidden" name="id" value="<?=@$user_edit['id_user']?>"/>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<?php
if (@$_GET['mobile'] == 'true') {
	echo $this->endSection();
}
?>