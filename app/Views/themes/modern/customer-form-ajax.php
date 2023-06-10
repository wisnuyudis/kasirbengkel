<?php
helper('html');
?>
<form method="post" action="" class="form-horizontal mx-3" enctype="multipart/form-data">
	<div class="tab-content" id="myTabContent">
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Nama Customer</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="nama_customer" value="<?= set_value('nama_customer', @$result['nama_customer']) ?>" required="required" />
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Alamat</label>
			<div class="col-sm-9">
				<textarea class="form-control" name="alamat_customer" required><?= set_value('alamat_customer', @$result['alamat_customer']) ?></textarea>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">No. HP (Whatsapp)</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="no_telp" value="<?= set_value('no_telp', @$result['no_telp']) ?>" required="required" />
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Email</label>
			<div class="col-sm-9">
				<input class="form-control" type="email" name="email" value="<?= set_value('email', @$result['email']) ?>" required="required" />
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Propinsi</label>
			<div class="col-sm-9">
				<?= options(['name' => 'id_wilayah_propinsi', 'class' => 'propinsi select2'], $propinsi, set_value('id_wilayah_propinsi', @$result['id_wilayah_propinsi'] ?: $default_propinsi)) ?>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Kabupaten</label>
			<div class="col-sm-9">
				<?= options(['name' => 'id_wilayah_kabupaten', 'class' => 'kabupaten select2'], $kabupaten, set_value('id_wilayah_kabupaten', @$result['id_wilayah_kabupaten'] ?: $default_kabupaten)) ?>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Kecamatan</label>
			<div class="col-sm-9">
				<?= options(['name' => 'id_wilayah_kecamatan', 'class' => 'kecamatan select2'], $kecamatan, set_value('id_wilayah_kecamatan', @$result['id_wilayah_kecamatan'] ?: $default_kecamatan)) ?>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Kelurahan</label>
			<div class="col-sm-9" style="position:relative">
				<?= options(['name' => 'id_wilayah_kelurahan', 'class' => 'kelurahan select2'], $kelurahan, set_value('id_wilayah_kelurahan', @$result['id_wilayah_kelurahan'] ?: $default_kelurahan)) ?>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Nama Sales</label>
			<div class="col-sm-9">
				<?php if ($cekrole['nama_role'] == 'kasir') { ?>
					<input type="text" class="form-control" name="nama_sales" value="<?php echo $_SESSION['user']['nama'] ?>" readonly id="nama_sales" />
					<input type="hidden" name="id_sales" value="<?php echo $_SESSION['user']['nama'] ?>" id="id_sales" />
				<?php } else { ?>
					<select style="width: 100%;" name="id_sales" class="salesx">
						<?php
						foreach ($data_sales as $sales) {
							if (@$result['id_sales']) {
								if ($result['id_sales'] == $sales['id_user']) {
									$s = "selected";
								} else {
									$s = "";
								}
							} else {
								$s = "";
							}
							echo '<option nama_sales="' . $sales['nama'] . '" ' . $s . ' value="' . $sales['id_user'] . '">' . $sales['nama'] . '</option>';
						} ?>
					</select>
					<input type="hidden" name="nama_sales" id="nama_salesx" />
				<?php } ?>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Jenis Harga</label>
			<div class="col-sm-9">
				<select style="width: 100%;" name="id_jenis_harga" class="jenis_hargax">
					<?php $sl = "";
					foreach ($jenis_harga as $harga) {
						if (@$result['id_jenis_harga']) {
							if ($result['id_jenis_harga'] == $harga['id_jenis_harga']) {
								$sl = "selected";
							} else {
								$sl = "";
							}
						} else {
							$sl = "";
						}
						echo '<option nama_jenis="' . $harga['nama_jenis_harga'] . '" ' . $sl . ' value="' . $harga['id_jenis_harga'] . '">' . $harga['nama_jenis_harga'] . '</option>';
					} ?>
				</select>
				<input type="hidden" name="nama_jenis_harga" id="nama_jenis_hargax" />
			</div>
		</div>
	</div>
</form>

<script>
	$('.salesx').change(function() {
		$('#nama_salesx').val($(this).find(":selected").attr("nama_sales"));
	});

	$('.salesx').trigger("change");

	$('.jenis_hargax').change(function() {
		$('#nama_jenis_hargax').val($(this).find(":selected").attr("nama_jenis"));
	});

	$('.jenis_hargax').trigger("change");
</script>
