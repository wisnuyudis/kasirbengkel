<?= $this->extend('themes/modern/layout-mobile') ?>
<?= $this->section('content') ?>
<div class="row">
<div class="col-sm-12 col-xl-6 left-panel">
	<div class="tabel-barang-container">
		<?php
		$column =[
					// 'ignore_urut' => 'No'
					'ignore_foto' => 'foto'
					, 'nama_barang' => 'Nama Barang'
					// , 'stok' => 'Stok'
					, 'ignore_harga' => 'Harga'
				];
		
		$settings['order'] = [1,'asc'];
		$index = 0;
		$th = '';
		helper('html');
		
		foreach ($column as $key => $val) {
			$th .= '<th>' . $val . '</th>'; 
			if (strpos($key, 'ignore') !== false) {
				$settings['columnDefs'][] = ["targets" => $index, "orderable" => false];
			}
			$index++;
		}
		
		?>
		<div>
		<table id="tabel-data" data-tabel-jenis="tabel-barang-list" class="tabel-data table table-hover" style="width:100%;opacity:0">
			<thead>
				<tr>
					<th style="width:64px">Foto</th>
					<th scope="col">Barang</th>
					<th scope="col" class="text-end" style="width:128px">Harga</th>
				</tr>
			</thead>
		</table>
		<?php
			foreach ($column as $key => $val) {
				$column_dt[] = ['data' => $key];
			}
		?>
		</div>
		<span id="dataTables-column" style="display:none"><?=json_encode($column_dt)?></span>
		<span id="dataTables-setting" style="display:none"><?=json_encode($settings)?></span>
		<span id="dataTables-url" style="display:none"><?=base_url() . '/barang-mobile/getDataDTBarang'?></span>
	</div>
</div>
<div class="col-sm-12 col-xl-6 right-panel">
	<div class="row">
		<div class="col-sm-12">
			<div class="right-panel-header ps-4 pe-3 rounded-top shadow">
				<div class="title">Detail Barang</div>
				<button class="show-mobile-d-flex btn-clear-success show-left-panel rounded-circle me-2 border-0"><i class="fas fa-search"></i></button>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<div class="right-panel-body shadow-sm">
				<?php
				$display = @$loading_data ? ' style="display:none"' : '';
				?>
				<div class="barang-pilih-empty" <?=$display?>>
					<div class="alert alert-success">
						<p><i class="fas fa-info-circle me-2"></i> Petunjuk</p>
						<ul>
							<li>Untuk menampilkan detail barang silakan <span class="hide-mobile">pilih invoice penjualan disamping</span> atau <span>klik icon <i class="fas fa-search"></i></span> (tampilan mobile)</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<div class="right-panel-footer d-flex justify-content-end shadow-sm rounded-bottom px-4 py-3">
				<div class="btn-container">
					<button class="btn-submit btn btn-primary" disabled><i class="fas fa-save me-2"></i>Simpan</button>
				</div>
			</div>
			<span style="display:none" class="detail-barang"><?=@json_encode($detail_barang)?></span>
		</div>
	</div>
</div>
</div>
<?= $this->endSection() ?>