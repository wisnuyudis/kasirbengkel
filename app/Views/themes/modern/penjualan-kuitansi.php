<html !DOCTYPE="HTML">
<head>
	<title><?php echo 'Cetak Antrian | ' . $setting_web->judul_web?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="shortcut icon" href="<?=base_url() . '/public/images/favicon.png'?>" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>/public/vendors/bootstrap/css/bootstrap.min.css?r=<?=time()?>"/>
	<meta name="description" content="Cetak kuitansi">
	<style>
		body {
			
			font-size: 15px;
		}
		.container {
			padding-top: 30px;
			width: 750px;
		}
		.title {
			font-size: 30px;
			font-weight: bold;
		}
		.alamat  p {
			margin:0;
		}
		table {
			font-size: 15px;
		}
	</style>
</head>
<body>
<div class="container">
	<div class="row mb-3">
		<div class="col-12">
			<div class="alamat">
				<p>KLINIK SUKAMANDI</p>
				<p>Blok Sukamandi RT 04 RW 01 Desa Mekarrahaja</p>
				<p>Kec Talaga Kabupaten Majalengka</p>
				<p>Telp. 08122328872</p>
			</div>
		</div>
	</div>
	<div class="row mb-3">
		<div class="col-12 text-center title">
			KUITANSI
		</div>
	</div>
	<div class="row">
		<div class="col-12">
		<?php

		$display = '';
		if (empty($barang) && empty($layanan)) {
			$display = ' ;display:none';
		}
		echo '
		<table style="width:auto' . $display . '" id="list-produk" class="table table-stiped table-bordered">
			<thead>
				<tr>
					<th>No</th>
					<th>Nama Barang</th>
					<th>Satuan</th>
					<th>Harga Satuan</th>
					<th>Qty</th>
					<th>Total Harga</th>
				</tr>
			</thead>
			<tbody>';
				$no = 1;
				
				// barang
				$display = '';
				if (empty($barang)) {
					$display = ' style="display:none"';
					$barang[] = ['nama_barang' => 'nama_barang', 'satuan' => 'satuan', 'qty' => '', 'harga' => 0];
				}
				
				echo '
				<tr id="barang-row-label" class="bg-light"' . $display . '>
					<td colspan="6"><h6>Barang</h6></td>
				</tr>';
				
				foreach ($barang as $val) {
					echo '
					<tr class="barang"'. $display .'>
						<td>' . $no . '</td>
						<td>' . $val['nama_barang'] . '</td>
						<td>' . $val['satuan'] . '</td>
						<td class="text-end">' . format_number((int) $val['harga']) . '</td>
						<td class="text-end">' . $val['qty'] . '</td>
						<td class="text-end">' . format_number((int) $val['harga']) . '</td>
						</tr>';

					$no++;
				}
				
				// LAYANAN
				$no = 1;
				$display = '';
				if (empty($layanan)) {
					$display = ' style="display:none"';
					$layanan[] = ['nama_layanan' => 'Layanan', 'nama_layanan_kategori' => 'Kategori', 'tarif' => 0];
				}
			
				echo 
				'<tr id="layanan-row-label" class="bg-light"' . $display . '>
						<td colspan="6"><h6>Layanan</h6></td>
					</tr>';
				
				foreach ($layanan as $val) {
					echo '
					
					<tr class="layanan"' . $display . '>
						<td>' . $no . '</td>
						<td>' . $val['nama_layanan'] . '</td>
						<td></td>
						<td class="text-end">' . format_number((int) $val['tarif']) . '</td>
						<td class="text-end">' . @$val['qty'] . '</td>
						<td class="text-end">' . format_number((int) $val['tarif']) . '</td>
					</tr>';

					$no++;
				}
				echo '</tbody>
						<tfoot>
							<tr>
								<th colspan="5" class="text-start">Sub Total</th>
								<th class="text-end">' . format_number( $penjualan['total'] ) . '</th>
							</tr>
							<tr>
								<th colspan="5" class="text-start">Penyesuaian</th>
								<th class="text-end">' . format_number( $penjualan['penyesuaian'] ) . '</th>
							</tr>
							<tr>
								<th colspan="5" class="text-start">Total</th>
								<th class="text-end">' . format_number($penjualan['neto']) . '</th>
							</tr>
						</tfoot>
			</table>';
		?>
		</div>
	</div>
	<div class="row">
		<div class="col-12">
		Terbilang: <em><?=terbilang($penjualan['neto'])?> rupiah</em>
		</div>
	</div>
	<div class="row">
		<div class="col-12">
		Terima kasih atas kepercayaan Anda menggunakan jasa kami, semoga lekas sembuh
		</div>
	</div>
</div>
<script type="text/javascript">
window.print();
</script>

</body>
</html>