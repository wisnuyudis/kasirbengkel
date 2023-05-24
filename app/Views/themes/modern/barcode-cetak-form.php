<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php
		if (!empty($message)) {
			show_message($message);
		}
		helper('html');
		?>
		<form method="post" action="" class="form-horizontal" enctype="multipart/form-data">
			<div class="tab-content" id="myTabContent">
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Ukuran Kertas</label>
					<div class="col-sm-5">
						<div class="d-flex">
							<?=options(['name' => 'ukuran_kertas', 'style' => 'width:auto', 'id' =>'paper-size', 'class' => 'me-2'], ['a4' => 'A4', 'f4' => 'F4', 'custom' => 'Custom'])?>
							<div class="input-group">
								<span class="input-group-text">W</span>
								<input type="text" class="form-control text-end" name="paper_width" id="paper-size-width" value="210" disabled/>
								<span class="input-group-text bg-light">mm</span>
								<span class="input-group-text">X</span>
								<span class="input-group-text">H</span>
								<input type="text" class="form-control text-end" name="paper_height" id="paper-size-height" value="297" disabled/>
								<span class="input-group-text bg-light">mm</span>
							</div>
						</div>
					</div>
					
				</div>
				<div class="form-group row mb-4">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tampilkan Angka</label>
					<div class="col-sm-5">
						<?=options(['name' => 'tampilkan_angka', 'id' => 'display-value'], ['Y' => 'Ya', 'N' => 'Tidak'])?>
					</div>
				</div>
				<div class="form-group row mb-4">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tinggi Barcode</label>
					<div class="col-sm-5">
						<div class="d-flex">
							<input type="range" value="100" class="form-range me-3" min="30" id="barcode-height" oninput="this.nextElementSibling.value = this.value"><output>100</output>
						</div>
					</div>
				</div>
				<div class="form-group row mb-4">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Lebar Barcode</label>
					<div class="col-sm-5">
						<div class="d-flex">
							<input type="range" value="2" class="form-range me-3" min="1" max="4" id="barcode-width" oninput="this.nextElementSibling.value = this.value"><output>2</output>
						</div>
					</div>
				</div>
				<div class="form-group row mb-4">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Produk</label>
					<div class="col-sm-5">
						<div class="input-group">
							<input type="text" name="barcode" class="form-control barcode" value="" placeholder="13 Digit Barcode"/>
							<button type="button" class="btn btn-outline-secondary add-barang"><i class="fas fa-search"></i> Cari Barang</button>
						</div>
					</div>
				</div>
				<hr/>
				<div class="form-group row mb-2">
					<div class="col">
						<?php
						$display = ';display:none';
						echo '
						<table style="width:auto' . $display . '" id="list-barang" class="table table-stiped table-bordered">
							<thead>
								<tr>
									<th>No</th>
									<th>Nama Barang</th>
									<th>Barcode</th>
									<th>Jumlah Barcode</th>
									<th>Aksi</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>1</td>
									<td>
										<span></span>
										<input type="hidden" name="id_barang[]" value=""/>
									</td>
									<td class="barcode-barang" value=""/></td>
									<td><input type="text" size="2" name="jml_cetak[]" class="form-control text-end format-ribuan jml-cetak" value="0"/></td>
									<td class="text-center"><a href="javascript:void(0)" class="text-danger del-row"><i class="fas fa-times"></i></a></td>
								</tr>
							</tbody>
						</table>';
						?>
					</div>
				</div>			
				<div class="form-group row mb-0">
					<div class="col-sm-5">
						<button type="button" name="print" id="print" value="print" class="btn btn-success me-2" disabled="disabled"><i class="fas fa-print me-2"></i>Print</button>
						<button type="button" name="pdf" id="export-pdf" value="PDF" class="btn btn-danger me-2" disabled="disabled"><i class="far fa-file-pdf me-2"></i>PDF</button>
						<button type="button" name="word" id="export-word" value="PDF" class="btn btn-primary" disabled="disabled"><i class="far fa-file-word me-2"></i>Word</button>
					</div>
				</div>
			</div>
		</form>
		<hr/>
		<div id="barcode-print-container" style="border: 1px solid #CCCCCC;text-align: center;padding:15px;width: 793.7007874px;min-width:377.953px;margin-top:10px;">
			PREVIEW
		</div>
		
	</div>
</div>