<?php
helper ('html');
?>
<form method="post" action="" class="form-horizontal mx-3" enctype="multipart/form-data">
	<div class="tab-content" id="myTabContent">
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Nama Customer</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="nama_customer" value="<?=set_value('nama_customer', @$result['nama_customer'])?>" required="required"/>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Alamat</label>
			<div class="col-sm-9">
				<textarea class="form-control" name="alamat_customer" required><?=set_value('alamat_customer', @$result['alamat_customer'])?></textarea>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">No. HP (Whatsapp)</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="no_telp" value="<?=set_value('no_telp', @$result['no_telp'])?>" required="required"/>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Email</label>
			<div class="col-sm-9">
				<input class="form-control" type="email" name="email" value="<?=set_value('email', @$result['email'])?>" required="required"/>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Propinsi</label>
			<div class="col-sm-9">
				<?=options(['name' => 'id_wilayah_propinsi', 'class' => 'propinsi select2'], $propinsi, set_value('id_wilayah_propinsi', @$result['id_wilayah_propinsi'] ?: $default_propinsi) )?>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Kabupaten</label>
			<div class="col-sm-9">
				<?=options(['name' => 'id_wilayah_kabupaten', 'class' => 'kabupaten select2'], $kabupaten, set_value('id_wilayah_kabupaten', @$result['id_wilayah_kabupaten'] ?: $default_kabupaten))?>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Kecamatan</label>
			<div class="col-sm-9">
				<?=options(['name' => 'id_wilayah_kecamatan', 'class' => 'kecamatan select2'], $kecamatan, set_value('id_wilayah_kecamatan', @$result['id_wilayah_kecamatan'] ?: $default_kecamatan))?>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Kelurahan</label>
			<div class="col-sm-9" style="position:relative">
				<?=options(['name' => 'id_wilayah_kelurahan', 'class' => 'kelurahan select2'], $kelurahan, set_value('id_wilayah_kelurahan', @$result['id_wilayah_kelurahan'] ?: $default_kelurahan))?>
			</div>
		</div>
	</div>
</form>