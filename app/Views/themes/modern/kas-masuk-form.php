<?php
helper('html');
?>
<form method="post" action="" class="form-horizontal p-3" enctype="multipart/form-data">
	<div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Nama Gudang</label>
			<div class="col-sm-9">
			<?=options(['name' => 'id_gudang', 'id' => 'gudang'], $gudang, set_value('id_gudang', @$kas['id_gudang']))?>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Nilai</label>
			<div class="col-sm-9">
				<input class="form-control" type="number" name="nilai" value="<?=@$kas['nilai']?>" required="required"/>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Keterangan</label>
			<div class="col-sm-9">
				<textarea class="form-control" name="keterangan" required="required"/><?=@$kas['keterangan']?></textarea>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Tanggal</label>
			<div class="col-sm-9">
				<div class="form-inline">
					<input type="date" name="date" id="date" class="form-control" value="<?=@$kas['date']?>">
				</div>
			</div>
		</div>
	</div>
	<input type="hidden" name="id" value="<?=@$_GET['id']?>"/>
</form>