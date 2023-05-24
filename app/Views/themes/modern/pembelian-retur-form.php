<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php 
			helper ('html');
			echo btn_link(['attr' => ['class' => 'btn btn-light btn-xs'],
				'url' => $config->baseURL . 'pembelian-retur',
				'icon' => 'fa fa-arrow-circle-left',
				'label' => 'Retur Pembelian'
			]);
		?>
		<hr/>
		<?php

		if (!empty($message)) {
			show_message($message);
			if ($message['status'] == 'ok') {
				echo '<a href="' . base_url() . '/penjualan/kuitansi?id=' . $id_penjualan . '" target="_blank" class="btn btn-success"/>Cetak Kuitansi</a><hr/>';
			}
		}
	
		if (!@$pembelian_retur['tgl_nota_retur']) {
			$pembelian_retur['tgl_nota_retur'] = date('d-m-Y');
		}
		?>
		<form method="post" action="" class="form-horizontal" enctype="multipart/form-data">
			<div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">No. Nota Retur</label>
					<div class="col-sm-6">
						<input class="form-control" id="no-nota-retur" type="text" name="no_nota_retur" readonly="readonly" value="<?=set_value('no_nota_retur', @$pembelian_retur['no_nota_retur'])?>"/>
						<small class="text-muted">Otomatis digenerate oleh sistem</small>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tgl. Nota Retur</label>
					<div class="col-sm-6">
						<input class="form-control flatpickr tanggal-nota-retur flatpickr" type="text" name="tgl_nota_retur" value="<?=set_value('tgl_nota_retur', format_tanggal(@$pembelian_retur['tgl_nota_retur'], 'dd-mm-yyyy'))?>" required="required"/>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">No. Invoice Pebelian</label>
					<div class="col-sm-6">
						<div class="input-group">
							<input type="text" name="no_invoice" id="no-invoice" readonly="readonly" class="form-control barcode" value="<?=set_value('no_invoice', @$pembelian_retur['no_invoice'])?>"/>
							<button type="button" class="btn btn-outline-secondary cari-invoice"><i class="fas fa-search"></i> Cari Invoice</button>
							<input type="hidden" name="id_pembelian" value="<?=set_value('id_pembelian', @$pembelian_retur['id_pembelian'])?>" id="id-pembelian"/>
						</div>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Supplier</label>
					<div class="col-sm-6">
						<input class="form-control" type="text" name="nama_supplier" readonly="readonly" id="nama-supplier" value="<?=set_value('nama_supplier', @$pembelian_retur['nama_supplier'])?>"/>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Gudang</label>
					<div class="col-sm-6">
						<?=options(['name' => 'id_gudang', 'id' => 'id-gudang', 'disabled' => 'disabled'], $gudang)?>
					</div>
				</div>
				<div class="form-group row mb-3">
					<div class="col">
						<?php
						$display = '';
						if (empty($barang)) {
							$display = ' ;display:none';
						}
						
						echo '
						<table style="width:auto' . $display . '" id="list-produk" class="table table-stiped table-bordered mt-3">
							<thead>
								<tr>
									<th>No</th>
									<th>Nama Barang</th>
									<th>Satuan</th>
									<th>Harga Satuan</th>
									<th>Qty Beli</th>
									<th>Qty Kembali</th>
									<th>Diskon</th>
									<th style="width: 175px">Total Beli</th>
									<th style="width: 175px">Total Retur</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>';
								$no = 1;
								
								// Barang
								$display = '';
								$sub_total = 0;
								if (empty($barang)) {
									// $display = ' style="display:none"';
									$barang[] = [];
								}
								// echo '<pre>'; print_r($barang); die;
								foreach ($barang as $val) {
									echo '
									<tr class="barang"'. $display .'>
										<td>' . $no . '</td>
										<td><span class="nama-barang">' . @$val['nama_barang'] . '</span>
											<input type="hidden" name="id_pembelian_detail[]" class="id-pembelian-detail" value="' . @$val['id_pembelian_detail'] . '"/>
										</td>
										<td>' . @$val['satuan'] . '</td>
										<td>
											<input type="text" size="4" class="form-control text-end harga-satuan" readonly="readonly" name="harga_satuan[]" value="' . format_number((int) @$val['harga_satuan']) . '"/>
										</td>
										<td>
											<input type="text" class="form-control text-end qty-beli" size="1" name="qty_barang[]" readonly="readonly" value="' . format_number(@$val['qty']) . '"/>
										</td>
										<td>
											<input type="text" class="form-control text-end qty-retur" size="1" name="qty_barang_retur[]" value="' . format_number(@$val['qty_retur']) . '"/>
										</td>
										<td>
											<div class="input-group" style="width:150px">'
											. options(['name' => 'diskon_barang_jenis[]', 'class' => 'diskon-barang-jenis', 'style' => 'flex: 0 0 auto;width: 65px'],['%' => '%', 'rp' => 'Rp'], @$val['diskon_jenis_retur']) 
											. '
											<input type="text" size="4" class="form-control text-end diskon-barang" style="width:80px" name="diskon_barang[]" value="'. format_number(@$val['diskon_nilai_retur']) . '"/>
											</div>
										</td>
										<td>
											<input type="text" size="4" class="form-control text-end harga-total-beli" name="harga_total_beli[]" value="' . format_number((int) @$val['harga_neto']) . '" readonly/>
										</td>
										<td>
											<input type="text" size="4" class="form-control text-end harga-total-retur" name="harga_total_retur[]" value="' . format_number((int) @$val['harga_neto_retur']) . '" readonly/></td>
										<td class="text-center">
											<button type="button" class="btn text-danger del-row"><i class="fas fa-times"></i></button>
										</td>
										</tr>';
									
									$sub_total += @$val['harga_neto'];
									$no++;
								}
								
								$penyesuaian_operator = '-';
								$penyesuaian_nilai = 0;
								if (@$pembelian_retur['penyesuaian']) {
									$penyesuaian_operator = $pembelian_retur['penyesuaian'] > 0 ? '+' : '-';
									$penyesuaian_nilai = format_number( (int) $pembelian_retur['penyesuaian'] );
									
								}
								echo '</tbody>
										
											<tr>
												<th colspan="8" class="text-start">Sub Total</th>
												<th><input name="sub_total_retur" class="form-control text-end" id="subtotal" type="text" value="' . format_number( set_value('sub_total_retur', @$pembelian_retur['sub_total_retur']) ) . '" readonly/></th>
												<th></th>
											</tr>
											<tr>
												<td colspan="8" class="text-start">Diskon</td>
												<td>
													<div class="input-group">'
														. options(['name' => 'diskon_total_jenis', 'id' => 'diskon-total-jenis', 'style' => 'flex: 0 0 auto;width: 70px'],['%' => '%', 'rp' => 'Rp'], set_value('diskon_total_jenis', @$pembelian_retur['diskon_jenis']) ) 
														. '<input name="diskon_total_nilai" id="diskon-total" class="form-control text-end" value="' . set_value('diskon_total_nilai', @$pembelian_retur['diskon_nilai']) . '" type="text"/>
													</div>
												</td>
												<td></td>
											</tr>
											<tr>
												<td colspan="8" class="text-start">Penyesuaian</td>
												<td>
													<div class="input-group">'
														. options(['name' => 'penyesuaian_operator', 'id' => 'operator-penyesuaian', 'style' => 'flex: 0 0 auto;width: 70px'],['-' => '-', '+' => '+'], set_value('penyesuaian_operator', $penyesuaian_operator)) 
														. '<input name="penyesuaian_nilai" class="form-control text-end" id="penyesuaian" value="'  . set_value('penyesuaian_nilai', $penyesuaian_nilai) . '" type="text"/>
													</div>
												</td>
												<td></td>
											</tr>
											<tr>
												<th colspan="8" class="text-start">Total</th>
												<th><input name="neto" class="form-control text-end" id="total" type="text" value="' . format_number(set_value('neto', @$pembelian_retur['neto_retur'])) . '" readonly/></th>
												<th></th>
											</tr>
										</tbody>
							</table>';
						?>
						
					</div>
				</div>
				<div class="form-group row mb-0">
					<div class="col-sm-6">
						<button type="submit" name="submit" value="submit" id="submit" class="btn btn-primary">Simpan</button>
						<input type="hidden" name="id" id="id-pembelian-retur" value="<?=@$_GET['id']?>"/>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>