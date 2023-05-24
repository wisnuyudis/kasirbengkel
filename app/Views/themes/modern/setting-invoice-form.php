<?php
helper('html');
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title">Setting Dokumen Transaksi</h5>
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
					<label class="col-sm-3 col-form-label">Logo</label>
					<div class="col-sm-9">
						<?php
				
						if (!empty($setting_invoice['logo']) ) 
						{
							$note = '';
							if (file_exists(ROOTPATH . 'public/images/' . $setting_invoice['logo'])) {
								$image = $config->baseURL . 'public/images/' . $setting_invoice['logo'];
							} else {
								$image = $config->baseURL . 'public/images/noimage.png';
								$note = '<small><b>Note</strong>: File <strong>public/images/' . setting_invoice['logo'] . '</strong> tidak ditemukan</small>';
							}
							echo '<div class="img-choose" style="margin:inherit;margin-bottom:10px">
									<div class="img-choose-container">
										<img src="'. $image . '?r=' . time() . '"/>
										<a href="javascript:void(0)" class="remove-img"><i class="fas fa-times"></i></a>
									</div>
								</div>
								' . $note .'
								';
						}
						?>
						<input type="hidden" class="foto-delete-img" name="foto_delete_img" value="0">
						<input type="hidden" class="foto-max-size" name="foto_max_size" value="300000"/>
						<input type="file" class="file form-control" name="logo">
							<?php if (!empty($form_errors['logo'])) echo '<small class="alert alert-danger">' . $form_errors['foto'] . '</small>'?>
							<small class="small" style="display:block">Tipe file harus <strong>.JPG</strong> atau <strong>.JPEG</strong>, Dimensi sebaiknya tidak lebih dari <strong>60px</strong> x <strong>60px</strong>. Maksimal 300Kb, </small>
						<div class="upload-img-thumb"><span class="img-prop"></span></div>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Footer Text</label>
					<div class="col-sm-9">
						<textarea name="footer_text" class="form-control"><?=set_value('footer_text', @$setting_invoice['footer_text'])?></textarea>
					</div>
				</div>
				<div class="row mb-3">
					<div class="col-sm-12">
						<div class="px-4 py-2 bg-lightgrey">Invoice</div>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Nomor Invoice</label>
					<div class="col-sm-9">
						<input class="form-control" type="text" name="no_invoice" value="<?=@$setting_invoice['no_invoice']?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Jumlah Digit</label>
					<div class="col-sm-9">
						<?=options(['name' => 'jml_digit_invoice'], ['4' => '4', '5' => '5', '6' => '6'], @$setting_invoice['jml_digit'])?>
						<small class="text-muted">Jumlah digit nomor invoice, contoh 6 digit: 000001</small>
					</div>
				</div>
				<div class="row mb-3">
					<div class="col-sm-12">
						<div class="px-4 py-2 bg-lightgrey">Nota Retur</div>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Nomor Nota Retur</label>
					<div class="col-sm-9">
						<input class="form-control" type="text" name="no_nota_retur" value="<?=@$setting_nota_retur['no_nota_retur']?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Jumlah Digit</label>
					<div class="col-sm-9">
						<?=options(['name' => 'jml_digit_nota_retur'], ['4' => '4', '5' => '5', '6' => '6'], @$setting_nota_retur['jml_digit'])?>
						<small class="text-muted">Jumlah digit nota retur, contoh 6 digit: 000001</small>
					</div>
				</div>
				<div class="row mb-3">
					<div class="col-sm-12">
						<div class="px-4 py-2 bg-lightgrey">Nota Transfer Barang</div>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Nomor Nota Transfer</label>
					<div class="col-sm-9">
						<input class="form-control" type="text" name="no_nota_transfer" value="<?=@$setting_nota_transfer['no_nota_transfer']?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Jumlah Digit</label>
					<div class="col-sm-9">
						<?=options(['name' => 'jml_digit_nota_transfer'], ['4' => '4', '5' => '5', '6' => '6'], @$setting_nota_transfer['jml_digit'])?>
						<small class="text-muted">Jumlah digit nota transfer barang, contoh 6 digit: 000001</small>
					</div>
				</div>
				<input type="submit" class="btn btn-primary" name="submit" value="Submit"/>
			</div>
			<input type="hidden" name="id" value="<?=@$_GET['id']?>"/>
		</form>
	</div>
</div>