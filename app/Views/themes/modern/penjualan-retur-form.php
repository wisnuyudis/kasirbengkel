<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php 
			helper ('html');
			echo btn_link(['attr' => ['class' => 'btn btn-success btn-xs'],
				'url' => $config->baseURL . 'penjualan-retur/add',
				'icon' => 'fa fa-plus',
				'label' => 'Add Retur penjualan'
			]);
			
			echo btn_link(['attr' => ['class' => 'btn btn-light btn-xs'],
				'url' => $config->baseURL . 'penjualan-retur',
				'icon' => 'fa fa-arrow-circle-left',
				'label' => 'Retur penjualan'
			]);
		?>
		<hr/>
		<?php
		if (!@$penjualan_retur['nama_customer']) {
			$penjualan_retur['nama_customer'] = 'Tamu';
		}
		
		if (!@$penjualan_retur['tgl_nota_retur']) {
			$penjualan_retur['tgl_nota_retur'] = date('d-m-Y');
		} else {
			$penjualan_retur['tgl_nota_retur'] = format_tanggal(@$penjualan_retur['tgl_nota_retur'], 'dd-mm-yyyy');
		}
		?>
		<form method="post" action="" class="form-horizontal" enctype="multipart/form-data">
			<div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">No. Nota Retur</label>
					<div class="col-sm-6">
						<input class="form-control" type="text" name="no_nota_retur" value="<?=set_value('no_nota_retur', @$penjualan_retur['no_nota_retur'])?>"/>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tgl. Nota Retur</label>
					<div class="col-sm-6">
						<input class="form-control flatpickr tanggal-nota-retur flatpickr" type="text" name="tgl_nota_retur" value="<?=set_value('tgl_nota_retur', $penjualan_retur['tgl_nota_retur'])?>" required="required"/>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">No. Invoice Penjualan</label>
					<div class="col-sm-6">
						<div class="input-group">
							<input type="text" name="no_invoice" id="no-invoice" readonly="readonly" class="form-control barcode" value="<?=set_value('no_invoice', @$penjualan_retur['no_invoice'])?>"/>
							<button type="button" class="btn btn-outline-secondary cari-invoice"><i class="fas fa-search"></i> Cari Invoice</button>
							<input type="hidden" name="id_penjualan" value="<?=set_value('id_penjualan', @$penjualan_retur['id_penjualan'])?>" id="id-penjualan"/>
						</div>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Pembeli</label>
					<div class="col-sm-6">
						<input class="form-control" type="text" name="nama_customer" readonly="readonly" id="nama-customer" value="<?=set_value('nama_customer', @$penjualan_retur['nama_customer'])?>"/>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Gudang</label>
					<div class="col-sm-6">
						<?=options(['name' => 'id_gudang', 'id' => 'id-gudang', 'disabled' => 'disabled'], $gudang, set_value('id_gudang', @$penjualan_retur['id_gudang']))?>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Dokumen</label>
					<div class="col-sm-6">
						<div class="gallery-container" style="margin-top:0">
							<?php
							// print_r($dokumen); die;
							$initial_item = false;
							if (!@$dokumen[0]['id_file_picker']) {
								$initial_item = true;
								$dokumen[] = ['id_penjualan_retur' => '', 'id_file_picker' => '', 'nama_file' => ''];
							}

							$display = $initial_item ? ' style="display:none"' : '';
							echo '<ul id="list-image-container" class="list-image-container">';
							foreach ($dokumen as $val) 
							{
								$data_initial_item = $initial_item ? ' data-initial-item="true"' : '';
								?>
								<li class="thumbnail-item"<?=$data_initial_item?> id="dokumen-<?=$val['id_penjualan_retur']?>"<?=$display?> data-id-file="<?=$val['id_file_picker']?>">
									<div class="toolbox">
										<?php if (@$id_kategori != '') { ?>
											<div class="grip"><i class="fas fa-grip-horizontal"></i></div>
										<?php } ?>
										<ul class="right-menu">
											<li><a class="grip" data-bs-toggle="tooltip" data-bs-placement="top" title="Move" href="javascript:void(0)"><i class="fas fa-grip-horizontal"></i></a>
											<li><a class="text-danger delete-image" href="javascript:void(0)"><i class="fas fa-times"></i></a>
										</ul>
									</div>
									<div class="img-container">
										<?php
										if ($val['nama_file']) {
											?>
											<img class="jwd-img-thumbnail" src="<?=base_url() . '/public/files/uploads/' . $val['nama_file']?>" />
										<?php } else { ?>
											<img class="jwd-img-thumbnail" />
										<?php } ?>
									</div>
									<input type="hidden" name="id_file_picker[]" value="<?=$val['id_file_picker']?>"/>
								</li>	
							<?php 
							} 
							echo '</ul>';
							?>
							<a class="btn btn-secondary btn-xs" id="add-image" href="javascript:void(0)">Add File</a>
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
									<th>Satuan</th>
									<th>Harga Satuan</th>
									<th>Qty Jual</th>
									<th>Qty Kembali</th>
									<th>Diskon</th>
									<th style="width: 175px">Total Jual</th>
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
										<td><span class="nama-barang">' . @$val['nama_barang'] . '</span><input type="hidden" value="' . @$val['id_penjualan_detail'] . '" name="id_penjualan_detail[]" class="id-penjualan-detail"/></td>
										<td>' . @$val['satuan'] . '</td>
										<td>
											<input type="text" size="4" class="form-control text-end harga-satuan" readonly="readonly" name="harga_satuan[]" value="' . format_number((int) @$val['harga_satuan']) . '"/>
										</td>
										<td>
											<input type="text" class="form-control text-end qty-jual" size="1" name="qty_barang[]" readonly="readonly" value="' . format_number(@$val['qty']) . '"/>
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
											<input type="text" size="4" class="form-control text-end harga-total-jual" name="harga_total_jual[]" value="' . format_number((int) @$val['harga_neto']) . '" readonly/>
										</td>
										<td>
											<input type="text" size="4" class="form-control text-end harga-total-retur" name="harga_total_retur[]" value="' . format_number((int) @$val['harga_neto_retur']) . '" readonly/></td>
										<td class="text-center">
											<button type="button" class="btn text-danger del-row"><i class="fas fa-times"></i></button>
										</td>
										</tr>';
									
									$sub_total += @$val['harga_neto_retur'];
									$no++;
								}

								$penyesuaian_operator = '-';
								$penyesuaian_nilai = 0;
								if (@$penjualan_retur['penyesuaian_retur']) {
									$penyesuaian_operator = $penjualan_retur['penyesuaian_retur'] > 0 ? '+' : '-';
									$penyesuaian_nilai = format_number( (int) $penjualan_retur['penyesuaian_retur'] );
								}
								
								echo '</tbody>
										
											<tr>
												<th colspan="8" class="text-start">Sub Total</th>
												<th><input name="sub_total" class="form-control text-end" id="subtotal" type="text" value="' . format_number( set_value('sub_total', $sub_total) ) . '" readonly/></th>
												<th></th>
											</tr>
											<tr>
												<td colspan="8" class="text-start">Diskon</td>
												<td>
													<div class="input-group">'
														. options(['name' => 'diskon_total_jenis', 'id' => 'diskon-total-jenis', 'style' => 'flex: 0 0 auto;width: 70px'],['%' => '%', 'rp' => 'Rp'], set_value('diskon_total_jenis', @$penjualan_retur['diskon_jenis_retur']) ) 
														. '<input name="diskon_total_nilai" id="diskon-total" class="form-control text-end" value="' . set_value('diskon_total_nilai', @$penjualan_retur['diskon_nilai_retur']) . '" type="text"/>
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
												<th><input name="neto" class="form-control text-end" id="total" type="text" value="' . format_number(set_value('neto', @$penjualan_retur['neto_retur'])) . '" readonly/></th>
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
						<input type="hidden" name="id" id="id-penjualan-retur" value="<?=@$_GET['id']?>"/>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>