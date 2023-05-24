<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php 
			helper ('html');
			echo btn_link(['attr' => ['class' => 'btn btn-success btn-xs'],
				'url' => $config->baseURL . 'transfer-barang/add',
				'icon' => 'fa fa-plus',
				'label' => 'Tambah Data'
			]);
			
			echo btn_link(['attr' => ['class' => 'btn btn-light btn-xs'],
				'url' => $config->baseURL . 'transfer-barang',
				'icon' => 'fa fa-arrow-circle-left',
				'label' => 'List Transfer Barang'
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
		
		if (!@$penjualan['nama_customer']) {
			$penjualan['nama_customer'] = 'Tamu';
		}
		
		if (!@$transfer_barang['tgl_nota_transfer']) {
			$transfer_barang['tgl_nota_transfer'] = date('Y-m-d');
		}
		?>
		<form method="post" action="" class="form-horizontal" enctype="multipart/form-data">
			<div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">No. Nota Transfer</label>
					<div class="col-sm-6">
						<input class="form-control" type="text" name="no_nota_transfer" id="no-nota-transfer" value="<?=set_value('no_nota_transfer', @$transfer_barang['no_nota_transfer'])?>" readonly="readonly"/>
						<small class="text-muted">Digenerate otomatis oleh sistem</small>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tanggal</label>
					<div class="col-sm-6">
						<input class="form-control flatpickr tanggal-nota-transfer flatpickr" type="text" name="tgl_nota_transfer" value="<?=set_value('tgl_nota_transfer', format_tanggal(@$transfer_barang['tgl_nota_transfer'], 'dd-mm-yyyy'))?>" required="required"/>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Gudang Asal</label>
					<div class="col-sm-6">
						<?=options(['name' => 'id_gudang_asal', 'id' => 'gudang-asal'], $gudang, set_value('id_gudang', @$transfer_barang['id_gudang_asal']))?>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Gudang Tujuan</label>
					<div class="col-sm-6">
						<?=options(['name' => 'id_gudang_tujuan', 'id' => 'gudang-tujuan'], $gudang, set_value('id_gudang', @$transfer_barang['id_gudang_tujuan']))?>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Harga</label>
					<div class="col-sm-6">
						<?=options(['name' => 'id_jenis_harga', 'id' => 'jenis-harga'], $jenis_harga, set_value('id_jenis_harga', @$jenis_harga_selected))?>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Keterangan</label>
					<div class="col-sm-6">
						<textarea name="keterangan" class="form-control"><?=set_value('keterangan', @$transfer_barang['keterangan'])?></textarea>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Cari Barang</label>
					<div class="col-sm-6" style="position:relative">
						<div class="input-group">
							<input type="text" name="barcode" class="form-control barcode" value="" placeholder="13 Digit Barcode"/>
							<button type="button" class="btn btn-outline-secondary add-barang"><i class="fas fa-search"></i> Cari Barang</button>
							<a class="btn btn-outline-success" target="_blank" href="<?=base_url()?>/barang/add"><i class="fas fa-plus"></i> Tambah Barang</a>
						</div>
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
									<th>Stok</th>
									<th>Satuan</th>
									<th>Harga Satuan</th>
									<th>Qty</th>
									<th>Diskon</th>
									<th style="width: 200px">Total Harga</th>
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
									
									$stok = @$val['list_stok'][$transfer_barang['id_gudang_asal']] . ' ';

									echo '
									<tr class="barang"'. $display .'>
										<td>' . $no . '</td>
										<td>' . @$val['nama_barang'] . '</td>
										<td><span class="jml-stok">' . @$stok . '</span></td>
										<td>'. @$val['satuan'] . '</td>
										<td>
											<input type="text" size="4" class="form-control text-end harga-satuan" readonly="readonly" name="harga_satuan[]" value="' . format_number((int) @$val['harga_satuan']) . '"/>
										</td>
										<td>
											<input type="text" class="form-control text-end qty" size="1" name="qty_barang[]" value="' . format_number(@$val['qty_transfer']) . '"/>
											<input type="hidden" name="id_barang[]" class="id-barang" value="' . @$val['id_barang'] . '"/>
										</td>
										<td>
											<div class="input-group">'
											. options(['name' => 'diskon_barang_jenis[]', 'class' => 'diskon-barang-jenis', 'style' => 'width:auto'],['%' => '%', 'rp' => 'Rp'], @$val['diskon_jenis_transfer']) 
											. '
											<input type="text" size="4" class="form-control text-end diskon-barang" style="width:100px" name="diskon_barang[]" value="'. format_number(@$val['diskon_nilai_transfer']) . '"/>
											</div>
										</td>
										<td>
											<input type="text" size="4" class="form-control text-end harga-total" name="harga_total[]" value="' . format_number((int) @$val['harga_neto_transfer']) . '" readonly/></td>
										<td class="text-center">
											<button type="button" class="btn text-danger del-row"><i class="fas fa-times"></i></button>
										</td>
										</tr>';
									
									$sub_total += @$val['harga_neto_transfer'];
									$no++;
								}
								
								$penyesuaian_operator = '-';
								$penyesuaian_nilai = 0;
								if (@$transfer_barang['penyesuaian_transfer']) {
									$penyesuaian_operator = $transfer_barang['penyesuaian_transfer'] > 0 ? '+' : '-';
									if ($transfer_barang['penyesuaian_transfer'] < 0) {
										$transfer_barang['penyesuaian_transfer'] = $transfer_barang['penyesuaian_transfer'] * -1;
									}
									$penyesuaian_nilai = format_number( (int) $transfer_barang['penyesuaian_transfer'] );
									
								}
								echo '</tbody>
										
											<tr>
												<th colspan="7" class="text-start">Sub Total</th>
												<th><input name="sub_total" class="form-control text-end" id="subtotal" type="text" value="' . format_number( set_value('sub_total', $sub_total) ) . '" readonly/></th>
												<th></th>
											</tr>
											<tr>
												<td colspan="7" class="text-start">Diskon</td>
												<td>
													<div class="input-group">'
														. options(['name' => 'diskon_total_jenis', 'id' => 'diskon-total-jenis', 'style' => 'flex: 0 0 auto;width: 70px'],['%' => '%', 'rp' => 'Rp'], set_value('diskon_total_jenis', @$transfer_barang['diskon_jenis_transfer']) ) 
														. '<input name="diskon_total_nilai" id="diskon-total" class="form-control text-end" value="' . set_value('diskon_total_nilai', @$transfer_barang['diskon_nilai_transfer']) . '" type="text"/>
													</div>
												</td>
												<td></td>
											</tr>
											<tr>
												<td colspan="7" class="text-start">Penyesuaian</td>
												<td>
													<div class="input-group">'
														. options(['name' => 'penyesuaian_operator', 'id' => 'operator-penyesuaian', 'style' => 'flex: 0 0 auto;width: 70px'],['-' => '-', '+' => '+'], set_value('penyesuaian_operator', $penyesuaian_operator)) 
														. '<input name="penyesuaian_nilai" class="form-control text-end" id="penyesuaian" value="'  . set_value('penyesuaian_nilai', $penyesuaian_nilai) . '" type="text"/>
													</div>
												</td>
												<td></td>
											</tr>
											<tr>
												<th colspan="7" class="text-start">Total</th>
												<th><input name="neto" class="form-control text-end" id="total" type="text" value="' . format_number(set_value('neto', @$transfer_barang['neto_transfer'])) . '" readonly/></th>
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
						<input type="hidden" name="id" id="id-transfer-barang" value="<?=@$_GET['id']?>"/>
					</div>
				</div>
			</div>
			<span style="display:none" id="list-barang-terpilih"><?=json_encode($barang)?></span>
		</form>
	</div>
</div>