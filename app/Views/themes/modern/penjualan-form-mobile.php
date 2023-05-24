<?php
helper('html');
if (!$penjualan) {
	exit;
}

$display = $penjualan['id_customer'] ? '' : ' style="display:none"';
?>
<form method="post" action="" class="form-horizontal" enctype="multipart/form-data">
	<div class="row mb-3">
		<label class="col-sm-4">Nama Pelanggan</label>
		<div class="col-sm-8">
			<div class="input-group">
				<input class="form-control" type="text" id="nama-customer" name="nama_customer" disabled="disabled" readonly="readonly" value="<?=set_value('nama_customer', @$penjualan['nama_customer'])?>" required="required"/>
				<a class="btn btn-outline-secondary" id="del-customer" <?=$display?> href="javascript:void(0)"><i class="fas fa-times"></i></a>
				<button type="button" class="btn btn-outline-secondary cari-customer"><i class="fas fa-search"></i> Cari</button>
			</div>
			<input class="form-control" type="hidden" name="id_customer" id="id-customer" value="<?=set_value('id_customer', @$penjualan['id_customer'])?>" required="required"/>
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-sm-4">No. Invoice</label>
		<div class="col-sm-8">
			<input class="form-control" type="text" name="no_invoice" id="no-invoice" value="<?=set_value('no_invoice', @$penjualan['no_invoice'])?>" readonly="readonly"/>
			<small class="text-muted">Digenerate otomatis oleh sistem</small>
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-sm-4">Tanggal</label>
		<div class="col-sm-8">
			<input class="form-control flatpickr tanggal-invoice flatpickr" type="text" name="tgl_invoice" value="<?=set_value('tgl_invoice', format_tanggal(@$penjualan['tgl_invoice'], 'dd-mm-yyyy'))?>" required="required"/>
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-sm-4">Gudang</label>
		<div class="col-sm-8">
			<?=options(['name' => 'id_gudang', 'id' => 'id-gudang'], $gudang, set_value('id_gudang', @$penjualan['id_gudang']))?>
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-sm-4">Harga</label>
		<div class="col-sm-6">
			<?=options(['name' => 'id_jenis_harga', 'id' => 'id-jenis-harga'], $jenis_harga, set_value('id_jenis_harga', @$jenis_harga_selected))?>
		</div>
	</div>
	<div class="form-group row mb-3">
		<label class="col-sm-4">Cari Barang</label>
		<div class="col-sm-6">
			<div class="input-group">
				<input type="text" name="barcode" class="form-control barcode" value="" placeholder="13 Digit Barcode"/>
				<button type="button" class="btn btn-outline-secondary add-barang"><i class="fas fa-search"></i></button>
			</div>
		</div>
	</div>
	<div class="row mb-3 barang-pilih-empty" style="display:none">
		<div class="col-sm-12">
			<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Barang belum dipilih</div>
		</div>
	</div>
	<table id="barang-pilih-tabel" class="tabel-barang-pilih">
		<?php
		foreach ($barang as $val) {
			?>
			<tbody class="barang-pilih-detail">
				<tr>
					<td>
						<div class="nama-barang-container d-flex justify-content-between">
							<div class="barang-pilih-nama-container">
								<span class="nama-barang"><?=$val['nama_barang']?></span>
								<div>
									<span style="font-weight:bold;font-size:105%"><span>Rp. </span><span class="harga-satuan-text"><?=format_number($val['harga_satuan'])?></span></span>
									<small>Stok: <span class="stok-text"><?=format_number($val['list_stok'][$penjualan['id_gudang']])?></span></small>
								</div>
								<div></div>
								<span class="barang-pilih-item-detail" style="display:none"><?=json_encode($val)?></span>
								<input type="hidden" class="id-barang" name="id_barang[]" value="<?=$val['id_barang']?>"/>
								<input type="hidden" class="harga-satuan" name="harga_satuan[]" value="<?=$val['harga_satuan']?>"/>
								<input type="hidden" class="harga-pokok" name="harga_pokok[]" value="<?=$val['harga_pokok']?>"/>
								<input type="hidden" class="stok" name="stok[]" value="<?=$val['list_stok'][$penjualan['id_gudang']]?>"/>
								<input type="hidden" class="satuan" name="satuan[]" value="<?=$val['satuan']?>"/>
							</div>
							<div class="input-group input-group-counter d-flex align-items-start" style="width:120px">
								<button type="button" class="input-group-text min-jml-barang" disabled>-</button>
								<input type="text" size="4" class="form-control text-end qty" style="width:40px" name="qty[]" value="<?=$val['qty']?>"/>
								<button type="button" class="input-group-text plus-jml-barang">+</button>
							</div>
						</div>
					</td>
					<td class="fw-bold">Rp</td>
					<td class="text-end">
						<span class="harga-barang-text number-display fw-bold"><?=format_number($val['harga_total'])?></span>
						<input type="hidden" class="form-control harga-barang-input" value="<?=$val['harga_total']?>"/>
					</td>
					<td>
						<div class="item-menu">
							<button class="btn shadow-none text-secondary btn-item-option" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></button>
							<ul class="dropdown-menu">
								<li><button type="button" class="dropdown-item del-item"><i class="far fa-trash-alt me-2"></i>Hapus Item</button></li>
								<li><button type="button" class="dropdown-item add-discount"><i class="fas fa-plus me-2"></i>Tambah Diskon</button></li>
								<li><button type="button" class="dropdown-item edit-item"><i class="fas fa-edit me-2"></i>Edit Harga</button></li>
							</ul>
						</div>
					</td>
				</tr>
				<?php
				$display = $val['diskon_nilai'] ? '' : ' style="display:none"';
				$diskon_text =  $val['diskon_nilai'];
				if ($val['diskon_jenis'] == 'rp') {
					$diskon_text =  $val['diskon_nilai'] * -1;
				}
				?>
				<tr class="diskon-row" <?=$display?>>
					<td>
						<div class="d-flex diskon-barang-row" style="justify-content: space-between;">
							<div>Diskon</div>
							<div class="diskon-barang-container">
								<div class="d-flex" style="align-items: center;">
									<?=options(['name' => 'diskon_barang_jenis[]"', 'class' => 'diskon-barang-jenis me-2', 'style' => 'width:auto'],['%' => '%', 'rp' => 'Rp'], $val['diskon_jenis'])?>
									<div class="input-group input-group-counter-warning diskon-nilai-container" style="display: flex;flex-wrap: nowrap;align-items: center;">
										<button type="button" class="input-group-text minus-diskon-barang">-</button>
										<input type="text" size="4" class="form-control text-end diskon-barang-nilai" style="width:72px" name="diskon_barang_nilai[]" value="<?=format_number($val['diskon_nilai'])?>">
										<button type="button" class="input-group-text plus-diskon-barang">+</button>
									</div>
								</div>
							</div>
						</div>
					</td> 
					<td class="diskon-barang-simbol-rp"></td>
					<td class="text-end">
						<span class="diskon-barang-text number-display"><?=format_number($diskon_text)?></span>
					</td>
					<td><div class="item-menu"><button type="button" class="btn shadow-none btn-item-option text-secondary del-diskon"><i class="fas fa-times"></i></button></div></td>
				</tr>
			</tbody>
		<?php
		}
		?>
		
		<tbody id="subtotal-tbody">
			<tr>
				<td colspan="4"><hr/></td>
			</tr>
			<tr class="fw-bold">
				<td>Sub Total</td>
				<td>Rp</td>
				<td>
					<div id="subtotal-text" class="text-end number-display fw-bold"><?=format_number($penjualan['sub_total'])?></div>
					<input id="subtotal-input" type="hidden" name="sub_total" class="form-control text-end" value="<?=$penjualan['sub_total']?>"/>
				</td>
				<td></td>
			</tr>
		</tbody>
		<tbody>
			<?php
			$rp = $penjualan['diskon_jenis'] == 'rp' ? 'Rp' : '';
			$persen = $penjualan['diskon_jenis'] == '%' ? '%' : '';
			?>
			<tr id="diskon-total-text-container">
				<td>Diskon</td>
				<td id="diskon-total-simbol-rp"><?=$rp?></td>
				<td>
					<div id="diskon-total-text" class="text-end number-display"><?=format_number($penjualan['diskon_nilai']) . $persen?></div>
				</td>
				<td></td>
			</tr>
			<tr id="diskon-total-input-container" style="display:none">
				<td colspan="4" style="padding-right:0">
					<div  class="d-flex" style="justify-content: space-between; align-items: center;">
						<div>Diskon</div>
						<div id="diskon-total-container" class="d-flex">
							<?=options(['name' => 'diskon_total_jenis"', 'id' => 'diskon-total-jenis', 'class' => 'diskon-total-jenis me-2','style' => 'width:auto;display:inline-block'],['%' => '%', 'rp' => 'Rp'], $penjualan['diskon_jenis'])?>
							<div class="input-group input-group-counter d-flex" style="flex-wrap: nowrap;">
								<button type="button" class="input-group-text" id="diskon-total-min">-</button>
								<input id="diskon-total-nilai" type="text" class="form-control number text-end" style="width:80px" name="diskon_total_nilai" value="<?=format_number($penjualan['diskon_nilai'])?>"/>
								<button type="button" class="input-group-text" id="diskon-total-plus">+</button>
							</div>
						</div>
					</div>
				</td>
				<td></td>
			</tr>
		</tbody>
		<tbody>
			<tr id="penyesuaian-text-container">
				<td>Penyesuaian</td>
				<td id="penyesuaian-simbol-rp"></td>
				<td>
					<div id="penyesuaian-text" class="text-end number-display"><?=format_number($penjualan['penyesuaian'])?></div>
				</td>
				<td></td>
			</tr>
			<tr id="penyesuaian-input-container" style="display:none">
				<td colspan="4" style="padding-right:0">
					<div  class="d-flex" style="justify-content: space-between; align-items: center;">
						<div>Penyesuaian</div>
						<div id="penyesuaian-container" class="d-flex">
							<?php
							if ( $penjualan['penyesuaian'] == 0 ) {
								$penyesuaian_selected = 'minus';
							} else {
								$penyesuaian_selected = $penjualan['penyesuaian'] < 0 ? 'minus' : 'plus';
							}
							?>
							<?=options(['name' => 'penyesuaian_operator"', 'id' => 'penyesuaian-operator', 'class' => 'me-2', 'style' => 'width:auto;display:inline-block'],['minus' => '-', 'plus' => '+'], $penyesuaian_selected)?>
							<div class="input-group input-group-counter d-flex" style="flex-wrap: nowrap;">
								<span class="input-group-text">Rp</span>
								<input type="text" id="penyesuaian-nilai" name="penyesuaian_nilai" style="width:100px" class="form-control text-end number" value="<?=format_number($penjualan['penyesuaian'])?>"/>
							</div>
						</div>
					</div>
				</td>
				<td></td>
			</tr>
			<?php
			if ($penjualan['pajak_display_text']) {
				?>
				<tr id="pajak-text-container">
					<td><?=$penjualan['pajak_display_text']?></td>
					<td id="pajak-simbol-rp"></td>
					<td>
						<div id="pajak-text" class="text-end number-display"><?=$penjualan['pajak_persen']?>%</div>
					</td>
					<td></td>
				</tr>
				<tr id="pajak-input-container" style="display:none">
					<td colspan="4" style="padding-right:0">
						<div  class="d-flex" style="justify-content: space-between; align-items: center;">
							<div><?=$penjualan['pajak_display_text']?></div>
							<div id="pajak-container" class="d-flex">
								<div class="input-group d-flex" style="flex-wrap: nowrap;">
									<button type="button" class="input-group-text" id="pajak-min">-</button>
									<input inputmode="numeric" id="pajak-nilai" type="text" class="form-control number text-end number" style="width:80px" name="pajak_nilai" value="<?=$penjualan['pajak_persen']?>"/>
									<span class="input-group-text">%</span>
									<button type="button" class="input-group-text" id="pajak-plus">+</button>
								</div>
							</div>
						</div>
					</td>
					<td></td>
				</tr>
			<?php
			}?>
			
			<tr class="fw-bold fs-5">
				<td>Total</td>
				<td>Rp</td>
				<td>
					<div id="total-text" class="total-text text-end number-display"><?=format_number($penjualan['neto'])?></div>
					<input id="total-input" type="hidden" name="total" class="form-control text-end" value="<?=$penjualan['neto']?>"/>
				</td>
				<td></td>
			</tr>
		</tbody>
		<tbody class="form-bayar">
			<tr>
				<td colspan="4"><hr/></td>
			</tr>
			<tr class="fw-bold fs-5">
				<td><div class="d-flex justify-content-between">Bayar</div></td>
				<td></td>
				<td>
					<?=options(['name' => 'jenis_bayar', 'style' => 'width:auto'], ['tunai' => 'Tunai', 'tempo' => 'Tempo'], $penjualan['jenis_bayar'])?>
				</td>
				<td></td>
			</tr>
			<?php
			foreach ($pembayaran as $index => $val) 
			{
					// $total_bayar += $val['jml_bayar'];
					if ($index == 0) {
						$button = '<button type="button" class="btn text-success add-pembayaran"><i class="fas fa-plus"></i></button>';
					} else {
						$button = '<button type="button" class="btn text-danger del-pembayaran"><i class="fas fa-times"></i></button>';
					}
					
				echo '<tr class="row-bayar">
							<td>
								<div class="input-group" style="width:250px; float:right">
									<span class="input-group-text">Tanggal</span>
									<input type="text" size="1" name="tgl_bayar[]" class="form-control flatpickr text-end format-ribuan" value="'. format_tanggal(@$val['tgl_bayar'], 'dd-mm-yyyy') .'"/>
								</div>
							</td>
							<td>Rp</td>
								
							<td>
								<input type="text" size="1" name="jml_bayar[]" class="form-control text-end format-ribuan item-bayar number" value="'. format_number(@$val['jml_bayar']) .'"/></td>
							<td class="text-center">
								' . $button . '
							</td>
						</tr>';
						
					// $no++;
			}
			?>
			<tr class="fw-bold fs-5">
				<td><div class="text-end"></div></td>
				<td>Rp</td>
				<td>
					<?php
					$len = strlen($penjualan['total_bayar']) + 2;
					if ($len < 7) $len = 7;
					?>
					
					<div class="text-end" id="total-bayar"><?=format_number($penjualan['total_bayar'])?></div>
					<input type="hidden" name="tgl_bayar[]" value="<?=date('d-m-Y')?>" />
					<input type="hidden" name="jml_tagihan" class="jml-tagihan" value="<?=$penjualan['neto']?>"/>
				</td>
				<td></td>
			</tr>
			<?php
			$display = $penjualan['total_bayar'] < $penjualan['neto'] ? '' : ' style="display:none"';
			echo '<tr class="fw-bold fs-5" id="kurang-bayar-row" ' . $display . '>
					<td>Kurang</td>
					<td>Rp</td>
					<td class="text-end text-danger" id="kurang-bayar-nilai">' .
						format_number($penjualan['neto'] - $penjualan['total_bayar']) . '
					</td>
					<td></td>
				</tr>';
				
			$display = $penjualan['kembali'] ? '' : ' style="display:none"';
			echo '<tr class="fw-bold fs-5" id="kembali-row" ' . $display . '>
				<td>Kembali</td>
				<td>Rp</td>
				<td class="text-end kembali" id="kembali-nilai">
					' . format_number($penjualan['kembali']) . '
				</td>
				<td></td>
			</tr>';
			?>
		</tbody>
	</table>
	<input type="hidden" name="id" value="<?=$_GET['id']?>"/>
	<input type="hidden" name="submit" value="submit"/>
</form>