<?php
helper('html');?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title">Data Kategori</h5>
	</div>
	
	<div class="card-body">
		<a href="<?=$module_url?>" class="btn btn-success btn-xs" id="add-menu"><i class="fa fa-plus pe-1"></i> Tambah Kategori</a>
		<hr/>
		<form style="display:none" method="post" class="modal-form" id="add-form" action="<?=current_url()?>" >
			<div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Nama Menu</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="nama_menu" value="" placeholder="Nama Menu" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">URL</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="url" value="" placeholder="URL" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Aktif</label>
					<div class="col-sm-8">
						<?php
							$checked = @$menu['aktif'] ? 'checked="checked"' : '';
						?>
						<div class="form-check-input-sm form-switch"><input name="aktif" type="checkbox" class="form-check-input" value="1" <?=$checked?>></div>
						<small class="form-text text-muted"><em>Jika tidak aktif, semua children tidak akan dimunculkan</em></small>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Use icon</label>
					<div class="col-sm-8 form-inline">
						<input type="hidden" name="icon_class" value="far fa-circle"/>
						<?php 
							$options = array(1 => 'Ya', 0 => 'Tidak');
							echo options(['name' => 'use_icon'], $options);
						?>
						<a href="javascript:void(0)" class="icon-preview" data-action="faPicker"><i class="far fa-circle"></i></a>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Role</label>
					<div class="col-sm-8 form-inline">
						Untuk memunculkan menu, assign role ke menu
					</div>
				</div>
				<input type="hidden" name="id" value="<?=@$_GET['id']?>"/>
				
			</div>
		</form>
		<?php

		if (!empty($message)) {
			show_message($message['content'], $message['status']);
		}
		?>
		
		<div class="dd" id="list-menu">
			<?=$list_kategori?>
		</div>

		<span style="display:none" id="url-delete"><?=$config->baseURL . 'builtin/menu/delete'?></span>
		<span style="display:none" id="url-edit"><?=$config->baseURL . 'builtin/menu/edit'?></span>
		<span style="display:none" id="url-detail"><?=$config->baseURL . 'builtin/menu/menuDetail?ajax=true&id='?></span>
	</div>
</div>