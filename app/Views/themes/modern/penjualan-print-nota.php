<html>
<head>
	<title>Print Nota</title>
	<style>
	body {
		font-size: 11px;
		font-family: helvetica;
	}
	table {
		font-size: 11px;
	}
	.container {
		max-width: 155px;
	}
	header {
		text-align: center;
		margin: auto;
	}
	footer {
		width: 100%;
		text-align: center;
	}
	hr {
		margin: 10px 0;
		padding: 0;
		height: 1px;
		border: 0;
		border-bottom: 1px solid rgb(49,49,49);
		width: 100%;
		
	}
	.nama-item {
		font-weight: bold;
	}
	
	.harga-item {
		display: flex;
		justify-content: flex-end;
		margin: 0;
		padding: 0;
	}
	
	
	table {
		border-collapse: collapse;
	}
	table td {
		border: 0;
	}
	
	.text-right {
		text-align: right;
	}
	
	.nama-perusahaan {
		font-weight: bold;
		font-size: 120%;
		margin-bottom: 3px;
	}
	
	.text-bold {
		font-weight: bold;
	}
	
	
	</style>
	
</head>
<body onload="window.print()">
	<?php
		$pelanggan = $penjualan['nama_customer'] ? $penjualan['nama_customer'] : 'Umum';
		// echo '<pre>'; print_r($petugas); die;
	?>
	<div class="container">
		
		<header>
			<div class="nama-perusahaan"><?=$identitas['nama']?></div>
			<div><?=$identitas['alamat']?></div>
			<div>Telp/WA <?=$identitas['no_telp']?></div>
		</header>
		<hr/>
		<div class="metadata">
			<table >
				<tr>
					<td>Tanggal</td>
					<td>:</td>
					<td><?=$penjualan['tgl_penjualan']?></td>
				</tr>
				<tr>
					<td>Kasir</td>
					<td>:</td>
					<td><?= !empty($petugas['nama']) ? $petugas['nama'] : '-'?></td>
				</tr>
				<tr>
					<td>Plg.</td>
					<td>:</td>
					<td><?=$pelanggan?></td>
				</tr>
			</table>
		</div>
		<hr/>
		<div class="item-container">
			<table>
				<?php
				$num = 0;
				foreach($barang as $val) {
					
					$style = '';
					if ($num > 0) {
						$style = 'style="padding-top: 10px"';
					}
					$harga_total = $val['harga_satuan'] * $val['qty'];
					echo '<tr>
								<td colspan="4" ' . $style . '><span class="nama-item">' .$val['nama_barang'] . '</span></td>
							</tr>
							<tr class="text-right">
								<td>' . format_number($val['harga_satuan']) . '</td>
								<td style="width:1px;padding-left:5px">x</td>
								<td style="width:5px;padding-left:5px">' . format_number($val['qty']) . '</td>
								<td style="width:50px;padding-left:10px">' . format_number($harga_total) . '</td>
							</tr>';
					if ($val['diskon']) {
						echo '<tr class="text-right">
								<td colspan="3">Diskon</td>
								<td>-' . format_number($val['diskon']) . '</td>
							</tr>';
					}
					
					$num++;
				}
				
				if ($penjualan['diskon']) {
					$penjualan['diskon'] = '-' . $penjualan['diskon'];
				}
				
				if ($penjualan['kurang_bayar'] < 0) {
					$penjualan['kurang_bayar'] = $penjualan['kurang_bayar'] * -1;
				}
				?>
				<tr>
					<td colspan="4"><hr/></td>
				</tr>
				<tr>
					<td colspan="3">Sub Total</td>
					<td class="text-right"><?=format_number($penjualan['sub_total'])?></td>
				</tr>
				<tr>
					<td colspan="3">Diskon</td>
					<td class="text-right"><?=format_number($penjualan['diskon'])?></td>
				</tr>
				<tr>
					<td colspan="3">Penyesuaian</td>
					<td class="text-right"><?=format_number($penjualan['penyesuaian'])?></td>
				</tr>
				<?php
				if ($penjualan['pajak_display_text']) {
				?>
					<tr>
						<td colspan="3"><?=$penjualan['pajak_display_text']?></td>
						<td class="text-right"><?=format_number($penjualan['pajak_persen'])?>%</td>
					</tr>
				<?php
				}?>
				<tr class="text-bold">
					<td colspan="3">Neto</td>
					<td class="text-right"><?=format_number($penjualan['neto'])?></td>
				</tr>
				<tr class="text-bold">
					<td colspan="3">Dibayar</td>
					<td class="text-right"><?=format_number($penjualan['total_bayar'])?></td>
				</tr>
				<tr class="text-bold">
					<td colspan="3">Kembali</td>
					<td class="text-right"><?=format_number($penjualan['kembali'])?></td>
				</tr>
			</table>
		</div>
		<hr/>
		<footer>
			<?=$setting['footer_text']?>
		</footer>
	</div>
</body>
<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', () => {
		setTimeout(function() {
			window.close();
		}, 7000);
		
	});		
</script>
</html>