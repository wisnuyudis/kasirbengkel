<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php 
			helper ('html');
			echo btn_link(['attr' => ['class' => 'btn btn-light btn-xs'],
				'url' => $config->baseURL . 'penjualan',
				'icon' => 'fa fa-arrow-circle-left',
				'label' => 'Penjualan'
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
		
		if (!@$penjualan['tgl_invoice']) {
			$penjualan['tgl_invoice'] = date('d-m-Y');
		}
		?>
		<form method="post" action="" class="form-horizontal" enctype="multipart/form-data">
			<div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Customer</label>
					<div class="col-sm-6">
						<div class="input-group">
							<input class="form-control" type="text" id="nama-customer" name="nama_customer" disabled="disabled" readonly="readonly" value="<?=set_value('nama_customer', @$penjualan['nama_customer'] ?: 'Umum')?>" required="required"/>
							<?php
							$display = !empty(@$penjualan['nama_customer']) ? '' : 'style="display:none"';
							?>
							<a class="btn btn-outline-secondary" id="del-customer" <?=$display?> href="javascript:void(0)"><i class="fas fa-times"></i></a>
							<button type="button" class="btn btn-outline-secondary cari-customer"><i class="fas fa-search"></i> Cari</button>
							<a class="btn btn-outline-success add-customer" id="add-customer" href="javascript:void(0)"><i class="fas fa-plus"></i> Tambah</a>
						</div>
						<input class="form-control" type="hidden" name="id_customer" id="id-customer" value="<?=set_value('id_customer', @$penjualan['id_customer'])?>" required="required"/>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">No. Invoice</label>
					<div class="col-sm-6">
						<input class="form-control" type="text" name="no_invoice" id="no-invoice" value="<?=set_value('no_invoice', @$penjualan['no_invoice'])?>" readonly="readonly"/>
						<small class="text-muted">Digenerate otomatis oleh sistem</small>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tanggal</label>
					<div class="col-sm-6">
						<input class="form-control flatpickr tanggal-invoice flatpickr" type="text" name="tgl_invoice" value="<?=set_value('tgl_invoice', format_tanggal(@$penjualan['tgl_invoice'], 'dd-mm-yyyy'))?>" required="required"/>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Gudang</label>
					<div class="col-sm-6">
						<?=options(['name' => 'id_gudang', 'id' => 'gudang'], $gudang, set_value('id_gudang', @$penjualan['id_gudang']))?>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Harga</label>
					<div class="col-sm-6">
						<?=options(['name' => 'id_jenis_harga', 'id' => 'jenis-harga'], $jenis_harga, set_value('id_jenis_harga', @$jenis_harga_selected))?>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Cari Produk</label>
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
						// echo $penjualan['jenis_bayar']; die;
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
									$stok = 0;
									if (@$val['list_stok']) {
										$stok = $val['list_stok'][$penjualan['id_gudang']] . ' ';
									}
									
									echo '
									<tr class="barang"'. $display .'>
										<td>' . $no . '</td>
										<td>' . @$val['nama_barang'] . '<div class="list-barang-detail"><small class="rounded badge-clear-success">Stok: <span class="jml-stok-text">' . $stok . ' ' . @$val['satuan'] . '</small></div></td>
										<td>
											<input type="text" size="4" class="form-control text-end harga-satuan" name="harga_satuan[]" value="' . format_number((int) @$val['harga_satuan']) . '"/>
											<input type="hidden" name="harga_pokok[]" class="harga-pokok" value="' . @$val['harga_pokok'] . '"/>
										</td>
										<td>
											<div class="input-group">
												<button type="button" class="input-group-text qty-min">-</button>
												<input type="text" class="form-control text-end qty" style="width:52px" name="qty[]" value="' . format_number(@$val['qty']) . '"/>
												<button type="button" class="input-group-text qty-plus">+</button>
											</div>
											<input type="hidden" name="id_barang[]" class="id-barang" value="' . @$val['id_barang'] . '"/>
										</td>
										<td>
											<div class="input-group">'
											. options(['name' => 'diskon_barang_jenis[]', 'class' => 'diskon-barang-jenis', 'style' => 'width:auto'],['%' => '%', 'rp' => 'Rp'], @$val['diskon_jenis']) 
											. '
											<input type="text" size="4" class="form-control text-end diskon-barang" style="width:100px" name="diskon_barang_nilai[]" value="'. format_number(@$val['diskon_nilai']) . '"/>
											</div>
										</td>
										<td>
											<input type="text" size="4" class="form-control text-end harga-total" name="harga_total[]" value="' . format_number((int) @$val['harga_neto']) . '" readonly/></td>
										<td class="text-center">
											<button type="button" class="btn text-danger del-row"><i class="fas fa-times"></i></button>
										</td>
										</tr>';
									
									$sub_total += @$val['harga_neto'];
									$no++;
								}
								
								$penyesuaian_operator = '-';
								$penyesuaian_nilai = 0;
								if (@$penjualan['penyesuaian']) {
									$penyesuaian_operator = $penjualan['penyesuaian'] > 0 ? '+' : '-';
									$penyesuaian_nilai = format_number( (int) $penjualan['penyesuaian'] );
									
								}
								echo '</tbody>
										
											<tr>
												<th colspan="5" class="text-start">Sub Total</th>
												<th><input name="sub_total" class="form-control text-end" id="subtotal" type="text" value="' . format_number( set_value('sub_total', $sub_total) ) . '" readonly/></th>
												<th></th>
											</tr>
											<tr>
												<td colspan="5" class="text-start">Diskon</td>
												<td>
													<div class="input-group">'
														. options(['name' => 'diskon_total_jenis', 'id' => 'diskon-total-jenis', 'style' => 'flex: 0 0 auto;width: 70px'],['%' => '%', 'rp' => 'Rp'], set_value('diskon_total_jenis', @$penjualan['diskon_jenis']) ) 
														. '<input name="diskon_total_nilai" id="diskon-total" class="form-control text-end" value="' . set_value('diskon_total_nilai', @$penjualan['diskon_nilai']) . '" type="text"/>
													</div>
												</td>
												<td></td>
											</tr>
											<tr>
												<td colspan="5" class="text-start">Penyesuaian</td>
												<td>
													<div class="input-group">'
														. options(['name' => 'penyesuaian_operator', 'id' => 'operator-penyesuaian', 'style' => 'flex: 0 0 auto;width: 70px'],['-' => '-', '+' => '+'], set_value('penyesuaian_operator', $penyesuaian_operator)) 
														. '<input name="penyesuaian_nilai" class="form-control text-end" id="penyesuaian" value="'  . set_value('penyesuaian_nilai', $penyesuaian_nilai) . '" type="text"/>
													</div>
												</td>
												<td></td>
											</tr>';
											
										
											if ($pajak['status'] == 'aktif') {
												$pajak_text = empty($_POST['id']) ? $pajak['display_text'] : @$penjualan['pajak_display_text'];
												echo '
												<tr>
													<td colspan="5" class="text-start">' . $pajak_text . '</td>
													<td>
														<div class="input-group">
															<button type="button" class="input-group-text" id="pajak-min">-</button>
															<input inputmode="numeric" id="pajak-nilai" type="text" class="form-control number text-end number" style="width:80px" name="pajak_nilai" value="' . @$penjualan['pajak_persen'] . '"/>
															<span class="input-group-text">%</span>
															<button type="button" class="input-group-text" id="pajak-plus">+</button>
														</div>
													</td>
													<td></td>
												</tr>';
											}
											
											echo '<tr>
												<th colspan="5" class="text-start">Total</th>
												<th><input name="neto" class="form-control text-end" id="total" type="text" value="' . format_number(set_value('neto', @$penjualan['neto'])) . '" readonly/></th>
												<th></th>
											</tr>
										</tbody>
										<tfoot>
											<tr>
												<th colspan="7" class="py-3 text-start bg-light">Bayar</th>
											</tr>
											<tr>
												<td colspan="5">Jenis Bayar</td>
												<td>' . options(['name' => 'jenis_bayar'], ['tunai' => 'Tunai', 'tempo' => 'Tempo' ], set_value('jenis_bayar', @$penjualan['jenis_bayar'])) . '</td>
												<td></td>
											</tr>
										
										';
										
										$using_pembayaran = 1;
										if (empty($pembayaran)) {
											$pembayaran[] = ['jml_bayar' => 0, 'tgl_bayar' => date('Y-m-d'), 'id_user_bayar' => ''];
											$using_pembayaran = 0;
										}
										$no = 1;
						
										$total_bayar = 0;
										
										// print_r( $pembayaran );
										// die;
										foreach ($pembayaran as $index => $val) {
											$total_bayar += $val['jml_bayar'];
											if ($index == 0) {
												$button = '<button type="button" class="btn text-success add-pembayaran"><i class="fas fa-plus"></i></button>';
											} else {
												$button = '<button type="button" class="btn text-danger del-pembayaran"><i class="fas fa-times"></i></button>';
											}
											// echo '<pre>' . $val['tgl_bayar']; die;
										echo '<tr class="row-bayar">
												<td>' . $no . '</td>
												<td colspan="4">
													<div class="input-group" style="width:250px; float:right">
														<span class="input-group-text">Tanggal</span>
														<input type="text" size="1" name="tgl_bayar[]" class="form-control flatpickr text-end format-ribuan" value="'. format_tanggal(@$val['tgl_bayar'], 'dd-mm-yyyy') .'"/>
													</div>
												<td><input type="text" size="1" name="jml_bayar[]" class="form-control text-end format-ribuan item-bayar" value="'. format_number(@$val['jml_bayar']) .'"/></td>
												<td class="text-center">
													' . $button . '
												</td>
												</tr>';
												
											$no++;
										}
										
										$text = 'Kurang';
										$class = '';
										if (@$penjualan['kurang_bayar']) {
											$text = $penjualan['kurang_bayar'] > 0 ? 'Kurang' : 'Kembali';
											$class = ' text-danger';
										}
										
										echo '
											<tr>
												<th colspan="5" class="text-start"><span class="sisa">' . $text . '</span></th>
												<td><input class="form-control text-end format-ribuan kurang-bayar' . $class . '" type="text" name="kurang_bayar" value="' . format_number( (int) set_value('kurang_bayar', @$penjualan['kurang_bayar'])) . '" required="required" readonly/>
												</th>
												<th></th>
											</tr>
										</tfoot>
							</table>';
						?>
						
					</div>
				</div>
				<div class="form-group row mb-0">
					<div class="col-sm-6">
						<button type="submit" name="submit" value="submit" id="submit" class="btn btn-primary">Simpan</button>
						<input type="hidden" name="id" id="id-penjualan" value="<?=@$_GET['id']?>"/>
					</div>
				</div>
				<span style="display:none" id="list-barang-terpilih"><?=json_encode($barang)?></span>
			</div>
		</form>
	</div>
</div>