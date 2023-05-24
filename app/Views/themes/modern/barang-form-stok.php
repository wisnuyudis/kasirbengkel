<?php
if (@$_GET['mobile'] == 'true') {
	echo $this->extend('themes/modern/layout-mobile');
	echo $this->section('content');
}
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	<div class="card-body">
		<?php
			helper (['html', 'format']);
			// echo '<pre>'; print_r($form_data); die;
			
			if (empty($_GET['mobile'])) {
				echo btn_link(['attr' => ['class' => 'btn btn-success btn-xs'],
					'url' => $config->baseURL . 'barang/add',
					'icon' => 'fa fa-plus',
					'label' => 'Tambah Barang'
				]);
				
				echo btn_link(['attr' => ['class' => 'btn btn-light btn-xs'],
					'url' => $config->baseURL . 'barang',
					'icon' => 'fa fa-arrow-circle-left',
					'label' => 'Data Barang'
				]);
				
				echo '<hr/>';
			}
		?>
		<?php
		if (!empty($message)) {
			show_message($message);
		}
		/* echo '<pre>';
		print_r($stok); die; */
		?>
		
		<form method="post" action="" class="form-horizontal" enctype="multipart/form-data">
			<div class="ps-3">
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Barang</label>
					<div class="col-sm-5"><?=$form_data['nama_barang']?></div>
				</div>
				<hr/>
				<?php
				foreach ($gudang as $index => $val) {
				?>
					<div class="stok-container">
						<div class="row mb-3">
							<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Gudang</label>
							<div class="col-sm-5 stok"><?=$val['nama_gudang']?></div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Adjusment</label>
							<div class="col-sm-5 stok-number">
								<!-- <div class="form-inline">
									<?=options(['name' => 'operator[]', 'class' => 'operator me-2'], ['plus' => '+', 'minus' => '-'])?>
									<div class="input-group"  style="width:130px">
									  <button type="button" class="input-group-text decrement">-</button>
									  <input type="text" size="2" value="" class="form-control text-end stok">
									  <button type="button" class="input-group-text increment">+</button>
									</div>
								</div> -->
								<div class="text-muted adjusment fst-italic">
									<?php
									$stok_awal = key_exists($val['id_gudang'], $stok) ? $stok[$val['id_gudang']]['total_stok'] : 0;
									?>
									Stok Awal: <span class="stok-awal"><?=format_ribuan($stok_awal)?></span>, Adjusment: <span class="stok-adjusment">0</span>, Stok Akhir: <span class="stok-akhir"><?=format_ribuan($stok_awal)?></span>
								</div>
								<input type="hidden" name="id_gudang[]" value="<?=$val['id_gudang']?>"/>
								<input type="hidden" name="adjusment[]" value="0"/>
							</div>
						</div>
					</div>
				<?php
					if ( ($index + 1) < count($gudang)) {
						echo '<hr/>';
					}
				}
				?>
			</div>
			<input type="submit" class="btn btn-primary" name="submit" value="Submit"/>
			<input type="hidden" name="id" value="<?=@$_GET['id']?>"/>
		</form>
	</div>
</div>
<?php
if (@$_GET['mobile'] == 'true') {
	echo $this->endSection();
}
?>