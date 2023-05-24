<?php
helper('html');
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title">Setting Pajak</h5>
	</div>
	<div class="card-body">
		<?php
		if (!empty($message)) {
			show_message($message);
		}
		?>
		<form method="post" action="" style="max-width: 750px" class="form-horizontal p-3" enctype="multipart/form-data">
			<div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Display Text</label>
					<div class="col-sm-9">
						<input class="form-control" type="text" name="display_text" value="<?=@$setting_pajak['display_text']?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Tarif</label>
					<div class="col-sm-9">
						<input class="form-control" type="text" name="tarif" id="tarif" value="<?=@$setting_pajak['tarif']?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Status</label>
					<div class="col-sm-9">
						<?=options(['name' => 'status'], ['aktif' => 'Aktif', 'non_aktif' => 'Tidak Aktif'], @$setting_pajak['status'])?>
					</div>
				</div>
				<input type="submit" class="btn btn-primary" name="submit" value="Submit"/>
			</div>
		</form>
	</div>
</div>