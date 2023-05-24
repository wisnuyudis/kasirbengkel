<?php

?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title">Laporan Penjualan</h5>
	</div>
	<div class="card-body">
		<form method="post" action="" class="form-horizontal p-3" enctype="multipart/form-data">
			<div class="row mb-3">
				<label class="col-sm-2 col-form-label">Tanggal</label>
				<div class="col-sm-5">
					<input type="text" class="form-control" name="daterange" id="daterange" value="<?=$start_date?> s.d <?=$end_date?>" />
					<input type="hidden" value="<?=$start_date_db?>" id="start-date"/>
					<input type="hidden" value="<?=$end_date_db?>" id="end-date"/>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-2 col-form-label">Total Penjualan</label>
				<div class="col-sm-5">
					<span id="total-nilai"><?=format_number($total_penjualan['total_neto'])?></span>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-2 col-form-label">Total Untung</label>
				<div class="col-sm-5">
					<span id="total-untung-rugi"><?=format_number($total_penjualan['total_untung_rugi'])?></span>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-2 col-form-label">Jumlah Penjualan</label>
				<div class="col-sm-5">
					<span id="total-qty"><?=format_number($total_penjualan['total_qty'])?></span>
				</div>
			</div>
			<div class="row mb-3">
				<div class="col-sm-12">
				<?php 
				
				$column =[
							'ignore_urut' => 'No'
							, 'nama_customer' => 'Nama Customer'
							, 'no_invoice' => 'No. Invoice'
							, 'tgl_penjualan' => 'Tgl. Transkasi'
							, 'neto' => 'Neto'
							, 'untung_rugi' => 'Untung/Rugi'
							, 'kurang_bayar' => 'Kurang Bayar'
						];
				
				$settings['order'] = [3,'desc'];
				$index = 0;
				$th = '';
				foreach ($column as $key => $val) {
					$th .= '<th>' . $val . '</th>'; 
					if (strpos($key, 'ignore') !== false) {
						$settings['columnDefs'][] = ["targets" => $index, "orderable" => false];
					}
					$index++;
				}
				
				?>
				<div class="d-flex mb-3" style="justify-content:flex-end">
					<div class="btn-group">
					<button class="btn btn-outline-secondary me-0 btn-export btn-xs" type="button" id="btn-pdf" disabled="disabled"><i class="fas fa-file-pdf me-2"></i>PDF</button>
					<button class="btn btn-outline-secondary me-0 btn-export btn-xs" type="button" id="btn-excel" disabled="disabled"><i class="fas fa-file-excel me-2"></i>XLSX</button>
					<!-- <button class="btn btn-outline-secondary btn-export btn-xs" type="button" id="btn-send-email" disabled="disabled"><i class="fas fa-paper-plane me-2"></i>Email</button> -->
					</div>
				</div>
				<table id="table-result" class="table display table-striped table-bordered table-hover" style="width:100%">
				<thead>
					<tr>
						<?=$th?>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<?=$th?>
					</tr>
				</tfoot>
				</table>
				<?php
					foreach ($column as $key => $val) {
						$column_dt[] = ['data' => $key];
					}
				?>
				<span id="dataTables-column" style="display:none"><?=json_encode($column_dt)?></span>
				<span id="dataTables-setting" style="display:none"><?=json_encode($settings)?></span>
				<span id="dataTables-url" style="display:none"><?=base_url() . '/laporan-penjualan-perinvoice/getDataDTPenjualanPerinvoice?start_date=' . $start_date_db . '&end_date=' . $end_date_db?></span>
				</div>
			</div>
		</form>
	</div>
</div>