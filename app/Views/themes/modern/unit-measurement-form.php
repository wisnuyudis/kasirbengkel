<?php
helper('html');
?>
<form method="post" action="" class="form-horizontal p-3" enctype="multipart/form-data">
	<div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Nama Satuan</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="nama_satuan" value="<?=@$satuan['nama_satuan']?>" required="required"/>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Satuan</label>
			<div class="col-sm-9">
				<textarea class="form-control" name="satuan" required="required"/><?=@$satuan['satuan']?></textarea>
			</div>
		</div>
	</div>
	<input type="hidden" name="id" value="<?=@$_GET['id']?>"/>
</form>