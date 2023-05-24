<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php 
			helper ('html');
			echo btn_link(['attr' => ['class' => 'btn btn-success btn-xs'],
				'url' => $config->baseURL . $current_module['nama_module'] . '/add',
				'icon' => 'fa fa-plus',
				'label' => 'Tambah Data'
			]);
			
			echo btn_link(['attr' => ['class' => 'btn btn-light btn-xs'],
				'url' => $config->baseURL . $current_module['nama_module'],
				'icon' => 'fa fa-arrow-circle-left',
				'label' => $current_module['judul_module']
			]);
		?>
		<hr/>
		<?php
		// echo '<pre>'; print_r($result); die;
		if (!empty($msg)) {
			show_message($msg['content'], $msg['status']);
		}
		?>
		<form method="post" action="" class="form-horizontal" enctype="multipart/form-data">
			<div class="tab-content" id="myTabContent">
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Supplier</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="nama_supplier" value="<?=set_value('nama_supplier', @$result['nama_supplier'])?>" required="required"/>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Alamat</label>
					<div class="col-sm-5">
						<textarea class="form-control" name="alamat_supplier" required><?=set_value('alamat_supplier', @$result['alamat_supplier'])?></textarea>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">No. Telp</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="no_telp" value="<?=set_value('no_telp', @$result['no_telp'])?>" required="required"/>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Propinsi</label>
					<div class="col-sm-5">
						<?=options(['name' => 'id_wilayah_propinsi', 'class' => 'propinsi select2'], $propinsi, set_value('id_wilayah_propinsi', @$result['id_wilayah_propinsi'] ?: $default_propinsi) )?>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Kabupaten</label>
					<div class="col-sm-5">
						<?=options(['name' => 'id_wilayah_kabupaten', 'class' => 'kabupaten select2'], $kabupaten, set_value('id_wilayah_kabupaten', @$result['id_wilayah_kabupaten'] ?: $default_kabupaten))?>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Kecamatan</label>
					<div class="col-sm-5">
						<?=options(['name' => 'id_wilayah_kecamatan', 'class' => 'kecamatan select2'], $kecamatan, set_value('id_wilayah_kecamatan', @$result['id_wilayah_kecamatan'] ?: $default_kecamatan))?>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Kelurahan</label>
					<div class="col-sm-5" style="position:relative">
						<?=options(['name' => 'id_wilayah_kelurahan', 'class' => 'kelurahan select2'], $kelurahan, set_value('id_wilayah_kelurahan', @$result['id_wilayah_kelurahan'] ?: $default_kelurahan))?>
					</div>
				</div>
				<div class="form-group row mb-0">
					<div class="col-sm-5">
						<button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
						<input type="hidden" name="id" value="<?=@$_GET['id']?>"/>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>