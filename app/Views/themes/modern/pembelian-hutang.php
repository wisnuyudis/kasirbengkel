<div class="card">
	<div class="card-header">
		<h5 class="card-title">Pembelian hutang</h5>
	</div>
	<div class="card-body">
		<div class="row mb-3">
			<label class="col-sm-2 col-form-label">Tanggal Awal</label>
			<div class="col-sm-5">
				<input type="date" class="form-control" name="tgl_awal" value="" id="tgl_awal" placeholder="Tanggal Awal" />
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-2 col-form-label">Tanggal Akhir</label>

			<div class="col-sm-5">
				<input type="hidden" name="tempo" value="<?php echo $tempo; ?>" id="tempo" />
				<input type="date" class="form-control" name="tgl_akhir" value="" id="tgl_akhir" placeholder="Tanggal Akhir" />
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-2 col-form-label">Total Pembelian</label>
			<div class="col-sm-5">
				<span id="total-pembelian"></span>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-2 col-form-label">Total Dibayar</label>
			<div class="col-sm-5">
				<span id="total-bayar"></span>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-2 col-form-label">Total Hutang</label>
			<div class="col-sm-5">
				<span id="total-hutang"></span>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-2 col-form-label"></label>
			<div class="col-sm-5">
				<button class="btn btn-primary me-0 btn-xs" type="button" id="btn-cari"><i class="fas fa-search me-2"></i>Search</button>
			</div>
		</div>
		<div class="d-flex mb-3" style="justify-content:flex-end">
			<div class="btn-group">
				<!-- <button class="btn btn-outline-secondary me-0 btn-export btn-xs" type="button" id="btn-pdf" disabled="disabled"><i class="fas fa-file-pdf me-2"></i>PDF</button>
				<button class="btn btn-outline-secondary me-0 btn-export btn-xs" type="button" id="btn-excel" disabled="disabled"><i class="fas fa-file-excel me-2"></i>XLSX</button> -->
				<!-- <button class="btn btn-outline-secondary btn-export btn-xs" type="button" id="btn-send-email" disabled="disabled"><i class="fas fa-paper-plane me-2"></i>Email</button> -->
			</div>
		</div>
		<table id="tableHt" class="table display table-striped table-bordered table-hover" style="width:100%">
			<thead>
				<th>No</th>
				<th>Tgl.Transaksi</th>
				<th>No.Invoice</th>
				<th>Supplier</th>
				<th>Total</th>
				<th>Bayar</th>
				<th>Kurang</th>
			</thead>
			<tbody>
			</tbody>
		</table>
		<?php // print_r($hutang) 
		?>
	</div>
</div>
<script>
	$(document).ready(function() {
		table = $('#tableHt').DataTable({
			"ajax": {
				"url": '<?php echo base_url('pembelian/ajaxdttabletempo') ?>',
				"type": "POST",
				"data": function(data) {
					data.tgl_awal = $('#tgl_awal').val();
					data.tgl_akhir = $('#tgl_akhir').val();
					data.tempo = $('#tempo').val();
				}
			},

			"processing": true,
			"serverSide": true,
			"order": [],
		});

		$('#btn-cari').click(function() {
			table.ajax.reload();

			$.ajax({
				type: 'POST',
				url: '<?php echo base_url('pembelian/gethutang') ?>',
				dataType: "Json",
				data: {
					'tgl_awal': $('#tgl_awal').val(),
					'tgl_akhir': $('#tgl_akhir').val(),
					'tempo': $('#tempo').val(),
				},
				success: function(data) {
					$('#total-pembelian').html(data['pembelian']);
					$('#total-bayar').html(data['bayar']);
					$('#total-hutang').html(data['hutang']);
				}

			});
		});

		$.ajax({
			type: 'GET',
			url: '<?php echo base_url('pembelian/gethutang') ?>',
			dataType: "Json",
			success: function(data) {
				$('#total-pembelian').html(data['pembelian']);
				$('#total-bayar').html(data['bayar']);
				$('#total-hutang').html(data['hutang']);
			}

		});
	});
</script>