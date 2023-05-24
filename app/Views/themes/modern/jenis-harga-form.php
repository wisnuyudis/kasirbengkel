<form method="post" action="" class="form-horizontal p-3" enctype="multipart/form-data">
	<div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Jenis Harga</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="nama_jenis_harga" value="<?=@$form_data['nama_jenis_harga']?>" required="required"/>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Deskripsi</label>
			<div class="col-sm-9">
				<textarea class="form-control" name="deskripsi" required="required"/><?=@$form_data['deskripsi']?></textarea>
			</div>
		</div>
	</div>
	<input type="hidden" name="id" value="<?=@$_GET['id']?>"/>
</form>