<?= $this->extend('themes/modern/layout-mobile') ?>
<?= $this->section('content') ?>
<div class="row">
<div class="col-sm-12 col-xl-6 left-panel">
	<div class="tabel-penjualan-container">
		<?php
		$column =[
					'ignore_urut' => 'No'
					, 'no_invoice' => 'No. Invoice'
					, 'tgl_invoice' => 'Tanggal'
					, 'neto' => 'Total'
					, 'nama' => 'Input'
				];
		
		$settings['order'] = [2,'desc'];
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
		<table id="tabel-data" data-tabel-jenis="tabel-penjualan" class="tabel-data table table-hover" style="width:100%;opacity:0">
			<thead>
				<tr>
					<th>No</th>
					<th scope="col">No. Invoice</th>
					<th scope="col">Tanggal</th>
					<th scope="col">Total</th>
					<th scope="col">Nama Kasir</th>
				</tr>
			</thead>
		</table>
		<?php
			foreach ($column as $key => $val) {
				$column_dt[] = ['data' => $key];
			}
		?>
		<span id="dataTables-column" style="display:none"><?=json_encode($column_dt)?></span>
		<span id="dataTables-setting" style="display:none"><?=json_encode($settings)?></span>
		<span id="dataTables-url" style="display:none"><?=module_url() . '/getDataDTPenjualan'?></span>
	</div>
</div>
<div class="col-sm-12 col-xl-6 right-panel">
	<div class="row">
		<div class="col-sm-12">
			<div class="right-panel-header ps-4 pe-3 rounded-top shadow d-flex justify-content-end">
				<div class="title">Detil Penjualan</div>
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
							<li>Untuk menampilkan data penjualan silakan <span class="hide-mobile">pilih invoice penjualan disamping</span> atau <span>klik icon <i class="fas fa-search"></i></span> (tampilan mobile)</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<div class="right-panel-footer d-flex justify-content-end shadow-sm rounded-bottom px-4 py-3">
				<?php
					$display_edit = $action == 'edit' ? '' : 'style="display:none"';
					$display_detail = $action == 'edit' ? 'style="display:none"' : '';
				?>
				<div class="btn-save btn-container" <?=$display_edit?>>
					<button class="btn-cancel btn btn-secondary me-1" disabled><i class="fas fa-save me-2"></i>Cancel</button>
					<button class="btn-submit btn btn-primary me-1" disabled><i class="fas fa-save me-2"></i>Simpan</button>
					<span style="display:none" class="invoice-detail"><?=json_encode($penjualan_detail)?></span>
				</div>
				<div class="btn-detail btn-container" <?=$display_detail?>>
					<a href="#" class="link-edit btn btn-info me-1 disabled" disabled><i class="fas fa-edit me-2"></i>Edit</a>
					<button class="btn-print-nota btn btn-success me-1" disabled><i class="fas fa-print me-2"></i>Invoice</button>
					<button class="btn-download-invoice-pdf btn btn-danger me-1" disabled><i class="fas fa-file-pdf me-2"></i>PDF</button>
					<!-- <button class="btn-kirim-email-invoice btn btn-primary me-1" disabled><i class="fas fa-paper-plane me-2"></i>Email</button> -->
				</div>
			</div>
		</div>
		<span style="display:none" class="penjualan-detail"><?=json_encode(@$penjualan_detail)?></span>
	</div>
</div>
</div>
<?= $this->endSection() ?>