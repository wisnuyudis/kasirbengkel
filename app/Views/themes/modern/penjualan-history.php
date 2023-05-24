<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$current_module['judul_module']?></h5>
	</div>
	
	<div class="card-body">
		<div class="row">
			<div class="col-4">
				<form class="form-inline">
					<div class="row">
						<div class="form-group col-4">
							<input type="date" class="form-control ml-2" id="startDate" name="startDate">
						</div>
						<div class="col-1">to</div>
						<div class="form-group col-4">
							<input type="date" class="form-control ml-2" id="endDate" name="endDate">
						</div>
						<button type="submit" class="btn btn-primary col-2">Filter</button>
					</div>
				</form>
			</div>
			<div class="col-8"></div>
		</div>

		<hr/>
		<?php 
		if (!empty($msg)) {
			show_alert($msg);
		}
			
		$column =[
					'ignore_urut' => 'No'
					, 'nama_customer' => 'Nama Customer'
					, 'ignore_action' => 'Action'
				];
		
		$settings['order'] = [2,'desc'];
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
		
		<table id="table-result" class="table display table-striped table-bordered table-hover" style="width:100%">
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
		?>
		<span id="dataTables-column" style="display:none"><?=json_encode($column_dt)?></span>
		<span id="dataTables-setting" style="display:none"><?=json_encode($settings)?></span>
		<?php
			if (isset($_GET['startDate']) && isset($_GET['endDate'])) {
				$startDate = $_GET['startDate'];
				$endDate = $_GET['endDate'];
				// Lanjutkan dengan logika Anda di sini
			}
			if (empty($startDate) && empty($endDate)) {
				?>
			<span id="dataTables-url" style="display:none"><?=current_url() . '/getDataDTPenjualan'?></span>
				<?php
			} else {
				?>
			<span id="dataTables-url" style="display:none"><?=current_url() . '/getDataDTPenjualan?startDate='. $startDate. '&endDate='. $endDate?></span>

				<?php
			}
		?>
	</div>
</div>

<script>
    document.getElementById('dateFilterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var startDate = document.getElementById('startDate').value;
        var endDate = document.getElementById('endDate').value;
        var url = '<?=current_url() . '/getDataDTPenjualan'?>?startDate=' + startDate + '&endDate=' + endDate;
        document.getElementById('dataTables-url').innerHTML = url;
        // Lakukan request data ke controller atau tindakan lainnya dengan URL yang sudah diubah
    });
</script>