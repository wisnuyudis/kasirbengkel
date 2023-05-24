<?php
helper('html');
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title">Setting Notifikasi Piutang</h5>
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
					<label class="col-sm-4 col-form-label">Tampilkan Piutang</label>
					<div class="col-sm-8">
						<?=options(['name' => 'notifikasi_show'], ['Y' => 'Ya', 'N' => 'Tidak'], @$setting_notifikasi['notifikasi_show'])?>
						<small>Tampilkan notifikasi piutang (pojok kanan atas) dan daftar piutang pada dashboard</small>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-4 col-form-label">Periode Piutang</label>
					<div class="col-sm-8">
						<div class="input-group">
							<input class="form-control text-end" type="number" name="piutang_periode" value="<?=@$setting_notifikasi['piutang_periode']?>" required="required"/>
							<span class="input-group-text">Hari</span>
						</div>
						<small class="text-muted">Periode jatuh tempo piutang</small>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-4 col-form-label">Notifikasi Jatuh Tempo</label>
					<div class="col-sm-8">
						<div class="input-group">
							<input class="form-control text-end" type="number" name="notifikasi_periode" value="<?=@$setting_notifikasi['notifikasi_periode']?>" required="required"/>
							<span class="input-group-text">Hari</span>
						</div>
						<small class="text-muted">Notifikasi piutang yang akan jatuh tempo</small>
					</div>
				</div>
				<input type="submit" class="btn btn-primary" name="submit" value="Submit"/>
			</div>
		</form>
	</div>
</div>