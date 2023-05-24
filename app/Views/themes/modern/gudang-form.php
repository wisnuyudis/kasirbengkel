<?php
helper('html');
?>
<form method="post" action="" class="form-horizontal p-3" enctype="multipart/form-data">
	<div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Nama Gudang</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="nama_gudang" value="<?=@$gudang['nama_gudang']?>" required="required"/>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Alamat</label>
			<div class="col-sm-9">
				<textarea class="form-control" name="alamat_gudang" required="required"/><?=@$gudang['alamat_gudang']?></textarea>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Propinsi</label>
			<div class="col-sm-9">
				<?=options(['name' => 'id_wilayah_propinsi', 'class' => 'propinsi select2'], $propinsi, set_value('id_wilayah_propinsi', $id_wilayah_propinsi) )?>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Kabupaten</label>
			<div class="col-sm-9">
				<?=options(['name' => 'id_wilayah_kabupaten', 'class' => 'kabupaten select2'], $kabupaten, set_value('id_wilayah_kabupaten', $id_wilayah_kabupaten))?>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Kecamatan</label>
			<div class="col-sm-9">
				<?=options(['name' => 'id_wilayah_kecamatan', 'class' => 'kecamatan select2'], $kecamatan, set_value('id_wilayah_kecamatan',$id_wilayah_kecamatan))?>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Kelurahan</label>
			<div class="col-sm-9">
				<?=options(['name' => 'id_wilayah_kelurahan', 'class' => 'kelurahan select2'], $kelurahan, set_value('id_wilayah_kelurahan', $id_wilayah_kelurahan))?>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Deskripsi</label>
			<div class="col-sm-9">
				<textarea class="form-control" name="deskripsi" required="required"/><?=@$gudang['deskripsi']?></textarea>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Defalt</label>
			<div class="col-sm-9">
				<div class="form-inline">
					<?=options(['name' => 'default_gudang'], ['N' => 'Tidak', 'Y' => 'Ya'], @$gudang['default_gudang'])?>
				</div>
				<div class="text-muted">Default pilihan gudang ketika input form</div>
			</div>
		</div>
	</div>
	<input type="hidden" name="id" value="<?=@$_GET['id']?>"/>
</form>