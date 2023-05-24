<div class="row mb-3">
	<label class="col-sm-4">Nama Pelanggan</label>
	<div class="col-sm-8"><?=$penjualan['nama_customer']?></div>
</div>
<div class="row mb-3">
	<label class="col-sm-4">No. Invoice</label>
	<div class="col-sm-8"><?=$penjualan['no_invoice']?></div>
</div>
<div class="row mb-3">
	<label class="col-sm-4">Tanggal</label>
	<div class="col-sm-8"><?=$penjualan['tgl_invoice']?></div>
</div>
<div class="row mb-3">
	<label class="col-sm-4">Gudang</label>
	<div class="col-sm-8"><?=$gudang[$penjualan['id_gudang']]?></div>
</div>
<div class="row mb-3">
	<label class="col-sm-4">Harga</label>
	<div class="col-sm-8"><?=$jenis_harga[$penjualan['id_jenis_harga']]?></div>
</div>
<table id="barang-pilih-tabel" class="tabel-barang-pilih">
	<?php
	// echo '<pre>'; print_r($barang); die;
	helper('html');
	foreach ($barang as $val) {
		?>
		<tbody class="barang-pilih-detail">
			<tr>
				<td>
					<div class="nama-barang-container">
						<div class="barang-pilih-nama-container">
							<span class="nama-barang"><?=$val['nama_barang']?></span>
							<div>
								<span style="font-weight:bold;font-size:105%"><span>Rp. </span><span class="harga-satuan-text"><?=format_number($val['harga_satuan'])?></span></span>
							</div>
						</div>
					</div>
				</td>
				<td><?=$val['qty']?>x</td>
				<td class="fw-bold" style="font-size:105%">Rp</td>
				<td class="text-end fw-bold" style="font-size:105%">
					<span class="harga-barang-text number-display"><?=format_number($val['harga_total'])?></span>
				</td>
			</tr>
			<?php
			$display = $val['diskon_nilai'] ? '' : ' style="display:none"';
			$diskon_text =  $val['diskon_nilai'];
			$rp = '';
			$persen = '%';
			if ($val['diskon_jenis'] == 'rp') {
				$rp = 'Rp';
				$persen = '';
				$diskon_text =  $val['diskon_nilai'] * -1;
			}
			?>
			<tr class="diskon-row" <?=$display?>>
				<td colspan="2">
					<div class="d-flex diskon-barang-row" style="justify-content: space-between;">
						<div>Diskon</div>
					</div>
				</td> 
				<td class="diskon-barang-simbol-rp"><?=$rp?></td>
				<td class="text-end">
					<span class="diskon-barang-text number-display"><?=format_number($val['diskon_nilai']) . $persen?></span>
				</td>
			</tr>
		</tbody>
	<?php
	}
	?>
	<tbody id="subtotal-tbody">
		<tr>
			<td colspan="4"><hr/></td>
		</tr>
		<tr class="fw-bold row-number" style="font-size:105%">
			<td colspan="2">Sub Total</td>
			<td>Rp</td>
			<td>
				<div id="subtotal-text" class="text-end number-display"><?=format_number($penjualan['sub_total'])?></div>
			</td>
		</tr>
	</tbody>
	<tbody>
		<?php
		$persen = $penjualan['diskon_jenis'] == '%' ? '%' : '';
		$rp = $penjualan['diskon_jenis'] == '%' ? '' : 'Rp';
		?>
		<tr class="row-number">
			<td colspan="2">Diskon</td>
			<td id="diskon-total-simbol-rp"><?=$rp?></td>
			<td>
				<div id="diskon-total-text" class="text-end number-display"><?=format_number($penjualan['diskon_nilai']) . $persen?></div>
			</td>
		</tr>
	</tbody>
	<tbody>
		<tr class="row-number">
			<td  colspan="2">Penyesuaian</td>
			<td id="penyesuaian-simbol-rp">Rp</td>
			<td>
				<div id="penyesuaian-text" class="text-end number-display"><?=format_number($penjualan['penyesuaian'])?></div>
			</td>
		</tr>
		<?php
		if ($penjualan['pajak_display_text']) {
		?>
			<tr class="row-number">
				<td  colspan="2"><?=$penjualan['pajak_display_text']?></td>
				<td id="pajak-simbol-rp">Rp</td>
				<td>
					<div id="pajak-text" class="text-end number-display"><?=format_number($penjualan['pajak_persen'])?>%</div>
				</td>
			</tr>
		<?php
		}
		?>
		<tr class="fw-bold text-info row-number" style="font-size:110%">
			<td colspan="2">Total</td>
			<td>Rp</td>
			<td>
				<div id="total-text" class="total-text text-end number-display"><?=format_number($penjualan['neto'])?></div>
			</td>
		</tr>
	</tbody>
	<tbody class="form-bayar" style="font-size:110%">
		<tr>
			<td colspan="4"><hr/></td>
		</tr>
		<tr class="fw-bold text-success row-number">
			<td colspan="2">Bayar</td>
			<td>Rp</td>
			<td>
				<div id="total-text" class="total-text text-end number-display"><?=format_number($penjualan['total_bayar'])?></div>
			</td>
		</tr>
		<?php
		if( $penjualan['total_bayar'] < $penjualan['neto'] ) {
			echo 
			'<tr class="fw-bold text-danger">
				<td colspan="2">Kurang</td>
				<td>Rp</td>
				<td class="text-end kurang-bayar">
					' . format_number($penjualan['kurang_bayar']) . '
				</td>
			</tr>';
			
		}
		
		if( $penjualan['total_bayar'] > $penjualan['neto'] ) {
			echo 
			'<tr class="fw-bold">
				<td colspan="2">Kembali</td>
				<td>Rp</td>
				<td class="text-end kembali">
					' . format_number($penjualan['kembali']) . '
				</td>
			</tr>';
			
		}
		
		?>
	</tbody>
</table>