<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?= $title ?></h5>
	</div>

	<div class="card-body">
		<?php
		helper('html');
		echo btn_link([
			'attr' => ['class' => 'btn btn-success btn-xs'],
			'url' => $config->baseURL . $current_module['nama_module'] . '/add',
			'icon' => 'fa fa-plus',
			'label' => 'Tambah Data'
		]);

		echo btn_link([
			'attr' => ['class' => 'btn btn-light btn-xs'],
			'url' => $config->baseURL . $current_module['nama_module'],
			'icon' => 'fa fa-arrow-circle-left',
			'label' => $current_module['judul_module']
		]);
		?>
		<hr />
		<?php
		if (!empty($message)) {
			show_message($message);
		}
		?>
		<form method="post" action="" class="form-horizontal" enctype="multipart/form-data">
			<div class="tab-content" id="myTabContent">
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nomor Invoice</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="no_invoice" value="<?= set_value('no_invoice', @$pembelian['no_invoice']) ?>" required="required" />
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Supplier</label>
					<div class="col-sm-5">
						<div class="input-group">
							<?= options(['name' => 'id_supplier', 'class' => 'select2'], $supplier, set_value('id_supplier', @$pembelian['id_supplier'])) ?>
							<a class="text-white input-group-text bg-success" target="_blank" href="<?= base_url() . '/supplier/add' ?>"><i class="fas fa-plus"></i></a>
						</div>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tanggal Invoice</label>
					<div class="col-sm-5">
						<input class="form-control flatpickr tanggal-invoice" type="text" name="tgl_invoice" value="<?= set_value('tgl_invoice', format_tanggal(@$pembelian['tgl_invoice'], 'dd-mm-yyyy')) ?>" required="required" />
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tanggal Jatuh Tempo</label>
					<div class="col-sm-5">
						<input class="form-control flatpickr tanggal-jatuh-tempo" type="text" name="tgl_jatuh_tempo" value="<?= set_value('tgl_jatuh_tempo', format_tanggal(@$pembelian['tgl_jatuh_tempo'], 'dd-mm-yyyy')) ?>" required="required" />
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Gudang</label>
					<div class="col-sm-5">
						<?= options(['name' => 'id_gudang', 'id' => 'id-gudang'], $gudang, set_value('id_gudang', @$pembelian['id_gudang'])) ?>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">File</label>
					<div class="col-sm-5">
						<div class="gallery-container" style="margin-top:0">
							<?php

							$initial_item = false;
							if (empty($pembelian['images'][0]['id_file_pocker'])) {
								$initial_item = true;
								$pembelian['images'][] = ['id_pembelian' => '', 'id_file_picker' => '', 'nama_file' => ''];
							}

							$display = $initial_item ? ' style="display:none"' : '';
							echo '<ul id="list-image-container" class="list-image-container">';
							foreach ($pembelian['images'] as $val) {
								$data_initial_item = $initial_item ? ' data-initial-item="true"' : '';
							?>
								<li class="thumbnail-item" <?= $data_initial_item ?> id="barang-<?= $val['id_pembelian'] ?>" <?= $display ?> data-id-file="<?= $val['id_file_picker'] ?>">
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
										$src = '';
										if ($val['nama_file']) {
											$src = base_url() . '/public/files/uploads/' . $val['nama_file'];
										}
										?>
										<img class="jwd-img-thumbnail" src="<?= $src ?>" />
									</div>
									<input type="hidden" name="id_file_picker[]" value="<?= $val['id_file_picker'] ?>" />
								</li>
							<?php
							}
							echo '</ul>';
							?>
							<a class="btn btn-secondary btn-xs" id="add-image" href="javascript:void(0)">Tambah File</a>
						</div>
					</div>
				</div>
				<hr />
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Produk</label>
					<div class="col-sm-5" style="position:relative">
						<div class="input-group">
							<input type="text" name="barcode" class="form-control barcode" value="" placeholder="13 Digit Barcode" />
							<a class="btn btn-outline-secondary add-barang" href="javascript:void(0)"><i class="fas fa-search"></i> Cari Barang</a>
							<a class="btn btn-outline-success" target="_blank" href="<?= base_url() ?>/barang/add"><i class="fas fa-plus"></i> Tambah Barang</a>
						</div>
					</div>
				</div>
				<div class="form-group row mb-3">
					<div class="col">
						<?php
						$display = '';
						$using_detail_barang = 1;
						if (empty($pembelian_detail)) {
							$display = ' ;display:none';
							$using_detail_barang = 0;

							$pembelian_detail[] = [
								'nama_barang' => '', 'keterangan' => '', 'id_barang' => '', 'qty' => 0, 'harga_neto' => 0, 'expired_date' => '', 'harga_satuan' => ''
							];
						}

						echo '
						<table style="width:auto' . $display . '" id="list-barang" class="table table-stiped table-bordered">
							<thead>
								<tr>
									<th rowspan="2">No</th>
									<th rowspan="2">Nama Barang</th>
									<th>Keterangan</th>
									<th>Expired Date</th>
									<th>Harga Satuan</th>
									<th>Kuantitas</th>
									<th>Diskon</th>
									<th>Total Harga</th>
									<th rowspan="2">Aksi</th>
								</tr>
							</thead>
							<tbody>';
						$no = 1;
						// echo '<pre>'; print_r($pembelian_detail); die;
						foreach ($pembelian_detail as $val) {
							echo '<tr>
								<td>' . $no . '</td>
								<td><span>' . $val['nama_barang'] . '</span>
									<input type="hidden" name="id_barang[]" value="' . @$val['id_barang'] . '"/>
								</td>
								<td><input type="text" size="15" name="keterangan[]" class="form-control" value="' . $val['keterangan'] . '"/></td>
								<td><input type="text" size="10" name="expired_date[]" class="form-control text-end flatpickr" value="' . format_tanggal($val['expired_date'], 'dd-mm-yyyy') . '"/></td>
								<td><input type="text" size="2" name="harga_satuan[]" class="form-control text-end format-ribuan harga-satuan" value="' . format_number($val['harga_satuan']) . '"/></td>
								<td>
									<div class="input-group">
										<input type="text" size="1" name="qty[]" class="form-control text-end format-ribuan kuantitas qty" value="' . @$val['qty'] . '"/>
										<span class="input-group-text satuan">pcs</span>
									</div>
								</td>
								<td>
								<div class="input-group">'
								. options(['name' => 'diskon_jenis[]', 'class' => 'dbj', 'style' => 'width:auto'], ['%' => '%', 'rp' => 'Rp'], @$val['diskon_jenis'])
								. '
								<input type="text" size="4" class="form-control text-end db" style="width:100px" name="diskon_nilai[]" value="' . format_number(@$val['diskon_nilai']) . '"/>
								</div>
								</td>
								<td><input type="text" size="4" class="form-control text-end harga-total" name="harga_neto[]" value="' . format_number((int) @$val['harga_neto']) . '" readonly/></td>
								<td class="text-center"><a href="javascript:void(0)" class="text-danger del-row"><i class="fas fa-times"></i></a></td>
								</tr>';
							$no++;
						}
						echo '</tbody>
								<tbody class="total">
									<tr>
										<td></td>
										<td>Sub Total</td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td>
										<td><input size="6" class="form-control text-end format-ribuan sub-total" type="text" name="sub_total" value=" ' . set_value('sub_total', format_number(@$pembelian['sub_total'])) . '" required="required" readonly/>
										</td>
										<td></td>
									</tr>
									<tr>
										<td></td>
										<td>Diskon RP</td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td><input size="6" class="form-control text-end format-ribuan diskon" type="text" name="diskon" value="' . set_value('diskon', format_number(@$pembelian['diskon'])) . '" required="required"/>
										</td>
										<td></td>
									</tr>
									<tr>
										<td></td>
										<td>Total</td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td><input size="6" class="form-control text-end format-ribuan total" type="text" name="total" value="' . set_value('total', format_number(@$pembelian['total'])) . '" required="required", readonly/>
										</td>
										<td></td>
									</tr>
								</tbody>
							</table>';
						?>
					</div>
				</div>
				<hr />
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Terima Barang</label>
					<div class="col-sm-5">
						<?php
						if (empty($pembelian)) {
							$pembelian['terima_barang'] = 'N';
						}
						?>
						<?= options(['name' => 'terima_barang', 'class' => 'terima-barang-option'], ['Y' => 'Ya', 'N' => 'Belum'], set_value('terima_barang', @$pembelian['terima_barang'])) ?>
					</div>
				</div>
				<?php
				$display = '';
				if (empty($pembelian) || @$pembelian['terima_barang'] == 'N') {
					$display = 'style="display:none"';
				}
				?>
				<div class="terima-barang-container" <?= $display ?>>
					<div class="form-group row mb-3">
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tanggal Terima</label>
						<div class="col-sm-5">
							<input class="form-control flatpickr" type="text" name="tgl_terima_barang" value="<?= set_value('tgl_terima_barang', format_tanggal(@$pembelian['tgl_terima_barang'], 'dd-mm-yyyy')) ?>" required="required" />
						</div>
					</div>
					<div class="form-group row mb-3">
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Penerima</label>
						<div class="col-sm-5">
							<?= options(['name' => 'id_user_terima', 'class' => 'select2'], $user, set_value('id_user_terima', @$pembelian['id_user_terima'])) ?>
						</div>
					</div>
				</div>
				<hr />
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Pembayaran</label>
					<div class="col-sm-9">
						<a class="btn btn-outline-secondary btn-xs mb-3 add-pembayaran" href="javascript:void(0)"><i class="fas fa-plus"></i> Tambah Pembayaran</a>

					</div>
				</div>
				<div class="form-group row mb-3">
					<div class="col">
						<?php
						$display = '';
						$using_pembayaran = 1;
						if (empty($pembayaran)) {
							$display = ' ;display:none';
							$pembayaran[] = ['jml_bayar' => 0, 'tgl_bayar' => '', 'id_user_bayar' => ''];
							$using_pembayaran = 0;
						}
						echo '
						<table style="width:auto' . $display . '" id="list-pembayaran" class="table table-stiped table-bordered">
							<thead>
								<tr>
									<th>No</th>
									<th>Tanggal Bayar</th>
									<th>Jumlah Bayar</th>
									<th>User</th>
									<th>Aksi</th>
								</tr>
							</thead>
							<tbody>';
						$no = 1;

						$total_bayar = 0;
						foreach ($pembayaran as $val) {
							$total_bayar += $val['jml_bayar'];
							echo '<tr>
								<td>' . $no . '</td>
								<td><input type="text" size="1" name="tgl_bayar[]" class="form-control flatpickr text-end format-ribuan" value="' . format_tanggal(@$val['tgl_bayar'], 'dd-mm-yyyy') . '"/></td>
								<td><input type="text" size="1" name="jml_bayar[]" class="form-control text-end format-ribuan item-bayar" value="' . format_number(@$val['jml_bayar']) . '"/></td>
								<td>' . options(['name' => 'id_user_bayar[]'], $user, @$val['id_user_bayar']) . '</td>
								<td><a href="javascript:void(0)" class="text-danger del-row"><i class="fas fa-times"></i></a></td>
								</tr>';

							$no++;
						}
						echo '</tbody>
								<tbody class="total">
									<tr>
										<td></td>
										<td>Total Bayar</td>
										<td><input class="form-control text-end format-ribuan total-bayar" type="text" name="total_bayar" value=" ' . set_value('total_bayar', format_number(@$pembelian['total_bayar'])) . '" required="required" readonly/>
										</td>
										<td></td>
										<td colspan="5"></td>
									</tr>
									<tr>
										<td></td>
										<td>Total Tagihan</td>
										<td><input class="form-control text-end format-ribuan total-tagihan" type="text" value="' . set_value('total', format_number(@$pembelian['total'])) . '" required="required" readonly/>
										</td>
										<td></td>
										<td colspan="5"></td>
									</tr>
									<tr>
										<td></td>
										<td>Kurang</td>
										<td><input class="form-control text-end format-ribuan kurang-bayar" type="text" name="kurang_bayar" value="' . set_value('kurang_bayar', format_number(@$pembelian['kurang_bayar'])) . '" required="required" readonly/>
										</td>
										<td></td>
										<td colspan="5"></td>
									</tr>
								</tbody>
							</table>';
						?>
					</div>
				</div>
				<div class="form-group row mb-0">
					<div class="col-sm-5">
						<button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
						<input type="hidden" name="id" value="<?= @$_GET['id'] ?>" />
						<input type="hidden" id="using-pembayaran" name="using_pembayaran" value="<?= $using_pembayaran ?>" />
						<input type="hidden" id="using-list-barang" name="using_detail_barang" value="<?= $using_detail_barang ?>" />
					</div>
				</div>

			</div>
		</form>
	</div>
</div>