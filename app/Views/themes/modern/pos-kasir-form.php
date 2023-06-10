<?= $this->extend('themes/modern/layout-mobile') ?>
<?= $this->section('content') ?>
<div class="row">
	<div class="col-sm-12 col-xl-6 left-panel">
		<div class="tabel-barang-container">
			<?php
			$column = [
				// 'ignore_urut' => 'No'
				// 'ignore_foto' => 'foto', 
				'barcode' => 'Barcode',
				'nama_barang' => 'Nama Barang'
				// , 'stok' => 'Stok'
				, 'ignore_harga' => 'Harga',
				'ignore_hargasatu' => 'Harga Satu',
				'ignore_hargadua' => 'Harga Dua',
				'ignore_hargatiga' => 'Harga Tiga',
				'ignore_hargaempat' => 'Harga Empat'
			];

			$settings['order'] = [1, 'asc'];
			$index = 0;
			$th = '';
			helper('html');

			foreach ($column as $key => $val) {
				$th .= '<th>' . $val . '</th>';
				if (strpos($key, 'ignore') !== false) {
					$settings['columnDefs'][] = ["targets" => $index, "orderable" => false];
				}
				$index++;
			}

			?>
			<table id="tabel-data" data-tabel-jenis="tabel-barang" class="tabel-data table table-hover" style="width:100%;opacity:0">
				<thead>
					<tr>
						<!-- <th style="width:64px">Foto</th> -->
						<th scope="col">Barcode </th>
						<th scope="col">Barang</th>
						<th scope="col" style="color:blue;text-align:right;
					">HET</th>
						<th scope="col" style="text-align:right;
					">Umum</th>
						<th scope="col" style="text-align:right;
					">Grosir</th>
						<th scope="col" style="text-align:right;
					">VIP</th>
						<th scope="col" style="text-align:right;
					" class="text-end" style="width:100px">PriceList</th>
					</tr>
				</thead>
			</table>
			<?php
			foreach ($column as $key => $val) {
				$column_dt[] = ['data' => $key];
			}
			?>
			<span id="dataTables-column" style="display:none"><?= json_encode($column_dt) ?></span>
			<span id="dataTables-setting" style="display:none"><?= json_encode($settings) ?></span>
			<span id="dataTables-url" style="display:none"><?= current_url() . '/getDataDTBarang' ?></span>
		</div>
	</div>
	<div class="col-sm-12 col-xl-6 right-panel">

		<div class="row">
			<div class="col-sm-12">
				<div class="right-panel-header ps-4 pe-3 rounded-top shadow">
					<i class="fas fa-user-edit me-3"></i>
					<input type="hidden" class="cekrole" value="<?php echo $cekrole['nama_role'] ?>" />
					<div class="title cari-customer"><span class="cari-customer" id="nama-customer">Umum</span></div>
					<button class="btn btn-clear-warning del-customer me-2 rounded-circle border border-0" id="del-customer" style="display:none"><i class="fas fa-times"></i></button>
					<button class="show-mobile-d-flex btn-clear-success show-left-panel rounded-circle me-2 border-0"><i class="fas fa-search"></i></button>
					<button class="btn-clear-primary setting-barang rounded-circle me-2 border-0"><i class="fas fa-cog"></i></button>
					<button class="btn btn-clear-danger del-barang-pilih rounded-circle border border-0"><i class="fas fa-eraser"></i></button>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">
				<div class="right-panel-body shadow-sm">
					<div class="barang-pilih-empty">
						<div class="alert alert-success">
							<p><i class="fas fa-info-circle me-2"></i> Petunjuk</p>
							<ul>
								<li>Untuk memulai silakan <span class="hide-mobile">pilih barang disamping</span> atau <span>klik icon <i class="fas fa-search"></i></span> (tampilan mobile)</li>
								<li>Klik icon <i class="fas fa-cog"></i> untuk mengatur gudang dan harga
								<li>Klik nama disebelah icon <i class="fas fa-user"></i> untuk mengganti nama customer</li>
								<li>Klik icon <i class="fas fa-eraser"></i> untuk menghapus semua barang yang sudah dipilih</li>
							</ul>
						</div>
					</div>
					<form id="barang-pilih-form" style="display:none">
						<input type="hidden" id="id-customer" name="id_customer" value="0" />
						<div class="row">
							<div class="col-sm-12">
								<?= options(['name' => 'id_gudang', 'id' => 'id-gudang', 'style' => 'display:none'], $gudang, $id_gudang_selected) ?>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-12">
								<?= options(['name' => 'id_jenis_harga', 'id' => 'id-jenis-harga', 'style' => 'display:none'], $jenis_harga, $jenis_harga_selected) ?>
							</div>
						</div>
						<table id="barang-pilih-tabel" class="tabel-barang-pilih" style="display:none">
							<tbody class="barang-pilih-detail">
								<tr>
									<td>
										<div class="nama-barang-container">
											<div class="barang-pilih-nama-container">
												<span class="nama-barang"></span>
												<div>
													<span style="font-weight:bold;font-size:105%"><span>Rp. </span><span class="harga-satuan-text"></span></span>
													<small>Stok: <span class="stok-text"></span></small>
												</div>
												<span class="barang-pilih-item-detail" style="display:none"></span>
												<input type="hidden" class="id-barang" name="id_barang[]" value="0" />
												<input type="hidden" class="harga-satuan" name="harga_satuan[]" value="0" />
												<input type="hidden" class="harga-pokok" name="harga_pokok[]" value="0" />
												<input type="hidden" class="stok" name="stok[]" value="0" />
												<input type="hidden" class="satuan" name="satuan[]" value="" />
											</div>
											<div class="input-group" style="width:128px">
												<button type="button" style="width:32px;height:32px;font-size:70%" class="btn btn-clear-info rounded min-jml-barang" disabled="disabled"><i class="fas fa-minus"></i></button>
												<input type="text" size="4" class="rounded form-control text-end qty me-2 ms-2" style="width:42px" name="qty[]" value="" />
												<button type="button" style="width:32px;height:32px;font-size:70%" class="btn btn-clear-info rounded plus-jml-barang"><i class="fas fa-plus"></i></button>
											</div>
										</div>
									</td>
									<td class="fw-bold">Rp</td>
									<td class="text-end fw-bold">
										<span class="harga-barang-text number-display">0</span>
										<input type="hidden" class="form-control harga-barang-input" value="0" />
									</td>
									<td>
										<div class="item-menu">
											<button class="btn shadow-none text-secondary btn-item-option" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></button>
											<ul class="dropdown-menu">
												<li><button type="button" class="dropdown-item del-item py-2"><i class="far fa-trash-alt me-2"></i>Hapus Item</button></li>
												<li><button type="button" class="dropdown-item add-discount py-2"><i class="fas fa-plus me-2"></i>Tambah Diskon</button></li>
												<li><button type="button" class="dropdown-item edit-item py-2"><i class="fas fa-edit me-2"></i>Edit Harga</button></li>
											</ul>
										</div>
									</td>
								</tr>
								<tr class="diskon-row" style="display:none">
									<td>
										<div class="d-flex diskon-barang-row" style="justify-content: space-between;">
											<div>Diskon</div>
											<div class="diskon-barang-container">
												<div class="d-flex" style="align-items: center;">
													<select name="diskon_barang_jenis[]" class="form-select diskon-barang-jenis me-2" style="width:auto">
														<option value="%">%</option>
														<option value="rp">Rp</option>
													</select>
													<div class="input-group-counter-warning diskon-nilai-container" style="display: flex;flex-wrap: nowrap;align-items: center;">
														<button type="button" class="input-group-text minus-diskon-barang">-</button>
														<input type="text" size="4" class="form-control text-end diskon-barang-nilai" style="width:60px" name="diskon_barang_nilai[]" value="0">
														<button type="button" class="input-group-text plus-diskon-barang">+</button>
													</div>
												</div>
											</div>
										</div>
									</td>
									<td class="diskon-barang-simbol-rp"></td>
									<td class="text-end">
										<span class="diskon-barang-text number-display">0</span>
									</td>
									<td>
										<div class="item-menu"><button type="button" class="btn shadow-none btn-item-option text-secondary del-diskon"><i class="fas fa-times"></i></button></div>
									</td>
								</tr>
							</tbody>
							<tbody id="subtotal-tbody" style="font-size:110%">
								<tr>
									<td colspan="4">
										<hr />
									</td>
								</tr>
								<tr class="fw-bold">
									<td>Sub Total</td>
									<td>Rp</td>
									<td>
										<div id="subtotal-text" class="text-end number-display">0</div>
										<input id="subtotal-input" type="hidden" name="sub_total" class="form-control text-end" value="0" />
									</td>
									<td></td>
								</tr>
							</tbody>
							<tbody>
								<tr id="diskon-total-text-container">
									<td>Diskon</td>
									<td id="diskon-total-simbol-rp"></td>
									<td>
										<div id="diskon-total-text" class="text-end number-display">0</div>
									</td>
									<td></td>
								</tr>
								<tr id="diskon-total-input-container" style="display:none">
									<td colspan="4" style="padding-right:0">
										<div class="d-flex" style="justify-content: space-between; align-items: center;">
											<div>Diskon</div>
											<div id="diskon-total-container" class="d-flex">
												<select name="diskon_total_jenis" id="diskon-total-jenis" class="form-select diskon-total-jenis me-2" style="width:auto; display:inline-block">
													<option value="%">%</option>
													<option value="rp">Rp</option>
												</select>
												<div class="input-group d-flex" style="flex-wrap: nowrap;">
													<button type="button" class="input-group-text" id="diskon-total-min">-</button>
													<input inputmode="numeric" id="diskon-total-nilai" type="text" class="form-control number text-end number" style="width:80px" name="diskon_total_nilai" value="0" />
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
										<div id="penyesuaian-text" class="text-end number-display">0</div>
									</td>
									<td></td>
								</tr>
								<tr id="penyesuaian-input-container" style="display:none">
									<td colspan="4" style="padding-right:0">
										<div class="d-flex" style="justify-content: space-between; align-items: center;">
											<div>Penyesuaian</div>
											<div id="penyesuaian-container" class="d-flex">
												<select name="penyesuaian_operator" id="penyesuaian-operator" class="form-select me-2" style="width:auto; display:inline-block">
													<option value="minus">-</option>
													<option value="plus">+</option>
												</select>
												<div class="input-group d-flex" style="flex-wrap: nowrap;">
													<span class="input-group-text">Rp</span>
													<input type="text" inputmode="numeric" id="penyesuaian-nilai" name="penyesuaian_nilai" style="width:100px" class="form-control text-end number" value="0" />
												</div>
											</div>
										</div>
									</td>
									<td></td>
								</tr>
								<?php
								if ($pajak['status'] == 'aktif') {
								?>
									<tr id="pajak-text-container">
										<td><?= $pajak['display_text'] ?></td>
										<td id="pajak-simbol-rp"></td>
										<td>
											<div id="pajak-text" class="text-end number-display"><?= $pajak['tarif'] ?>%</div>
										</td>
										<td></td>
									</tr>
									<tr id="pajak-input-container" style="display:none">
										<td colspan="4" style="padding-right:0">
											<div class="d-flex" style="justify-content: space-between; align-items: center;">
												<div><?= $pajak['display_text'] ?></div>
												<div id="pajak-container" class="d-flex">
													<div class="input-group d-flex" style="flex-wrap: nowrap;">
														<button type="button" class="input-group-text" id="pajak-min">-</button>
														<input inputmode="numeric" id="pajak-nilai" type="text" class="form-control number text-end number" style="width:80px" name="pajak_nilai" value="<?= $pajak['tarif'] ?>" />
														<span class="input-group-text">%</span>
														<button type="button" class="input-group-text" id="pajak-plus">+</button>
													</div>
												</div>
											</div>
										</td>
										<td></td>
									</tr>
								<?php
								} ?>

								<tr class="fw-bold text-info" style="font-size:110%">
									<td>Total</td>
									<td>Rp</td>
									<td>
										<div id="total-text" class="total-text text-end number-display">0</div>
										<input id="total-input" type="hidden" name="total" class="form-control text-end" value="0" />
									</td>
									<td></td>
								</tr>
							</tbody>
						</table>
					</form>
					<form class="form-bayar" style="display: none">
						<div class="row mb-3">
							<label class="col-sm-3 col-form-label">Jml. Tagihan</label>
							<div class="col-sm-9">
								<input class="form-control jml-tagihan number" inputmode="numeric" type="text" name="total_tagihan" value="0" required="required">
							</div>
						</div>
						<div class="form-group row mb-3">
							<label class="col-sm-3 col-form-label">Pembayaran</label>
							<div class="col-sm-9">
								<?= options(['name' => 'jenis_bayar'], ['tunai' => 'Tunai', 'tempo' => 'Tempo']) ?>
							</div>
						</div>
						<div class="form-group row mb-3">
							<label class="col-sm-3 col-form-label">Jml. Bayar</label>
							<div class="col-sm-9">
								<input class="form-control jml-bayar number" inputmode="numeric" type="text" name="jml_bayar[]" value="0" required="required">
								<input type="hidden" name="tgl_bayar[]" value="<?= date('d-m-Y') ?>" />
							</div>
						</div>
						<div class="form-group row mb-3">
							<label class="col-sm-3 col-form-label">Kembali</label>
							<div class="col-sm-9">
								<div class="kembali">0</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">
				<div class="right-panel-footer shadow-sm rounded-bottom">
					<button class="btn btn-success rounded-0 btn-bayar btn-lg rounded-bottom" disabled>Bayar Rp <span class="total-text">0</span></button>
				</div>
			</div>
		</div>
		<input type="hidden" id="page-type" value="kasir" />
	</div>
</div>
<?= $this->endSection() ?>
