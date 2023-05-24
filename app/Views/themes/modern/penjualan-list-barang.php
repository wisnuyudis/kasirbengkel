<?php 

$column =[
			'ignore_urut' => 'No'
			, 'nama_barang' => 'Nama'
			, 'stok' => 'Stok'
			, 'satuan' => 'Satuan'
			, 'ignore_harga_pokok' => 'Harga Pokok'
			, 'ignore_harga_jual' => 'Harga Jual'
			, 'ignore_pilih' => 'Pilih'
		];

$settings['order'] = [1,'asc'];
$index = 0;
$th = '';
foreach ($column as $key => $val) {
	$th .= '<th>' . $val . '</th>'; 
	if (strpos($key, 'ignore') !== false) {
		$settings['columnDefs'][] = ["targets" => $index, "orderable" => false];
	}
	$index++;
}

?>

<table id="jwdmodal-table-result" class="table display table-striped table-bordered table-hover" style="width:100%">
<thead>
	<tr>
		<?=$th?>
	</tr>
</thead>
<tfoot>
	<tr>
		<?=$th?>
	</tr>
</tfoot>
</table>
<?php
	foreach ($column as $key => $val) {
		$column_dt[] = ['data' => $key];
	}
$id_lokasi_usaha = '';
if (!empty($_GET['id_lokasi_usaha'])) {
	$id_lokasi_usaha = '?id_lokasi_usaha=' . $_GET['id_lokasi_usaha'];
}
?>
<span id="jwdmodal-dataTables-column" style="display:none"><?=json_encode($column_dt)?></span>
<span id="jwdmodal-dataTables-setting" style="display:none"><?=json_encode($settings)?></span>
<span id="jwdmodal-dataTables-url" style="display:none"><?=base_url() . '/penjualan/getDataDTBarang?id_gudang=' . $_GET['id_gudang'] . '&id_jenis_harga=' . $_GET['id_jenis_harga']?></span>