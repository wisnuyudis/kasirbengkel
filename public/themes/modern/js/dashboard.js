$(document).ready(function() {
	
	// Chart Penjualan Perbulan
	let randomBackground = [];
		
	for (i = 0; i < 12; i++){
		randomBackground.push(dynamicColors());
	}
	
	dataset_penjualan = [];
	colors = ['rgb(99 174 206)', 'rgb(251 179 66)', 'rgb(62 185 110)'];
	// colors = ['rgb(76 162 199)', 'rgb(250 168 38)', 'rgb(37 176 91)'];
	
	num = 0;
	Object.keys(data_penjualan).map( tahun => {
		color = colors[num];
		dataset_penjualan.push(
			{
				label: tahun,
				backgroundColor: color,
				data: data_penjualan[tahun],
				fill: false,
				borderColor: color,
				tension: 0.1
			}
		);
		num++;
	});
	
	let dataChartPenjualan = {
		labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
		datasets: dataset_penjualan
	};
	
	configChartPenjualan = {
		type: 'line',
		data: dataChartPenjualan,
		options: {
			responsive: false,
			maintainAspectRatio: false,
			plugins: {
			  legend: {
				display: true,
				position: 'top',
				fullWidth: false,
				labels: {
					padding: 10,
					boxWidth: 30
				}
			  }
			},
			
			tooltips: {
				callbacks: {
					label: function(tooltipItems, data) {
						// return data.labels[tooltipItems.index] + ": " + data.datasets[0].data[tooltipItems.index].toLocaleString();
						// return "Total : " + data.datasets[0].data[tooltipItems.index].toLocaleString();
						return "Total : " + data.datasets[0].data[tooltipItems.index].toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
					}
				}
			},
			scales: {
				y: {
					beginAtZero: false,
					ticks: {
						callback: function(value, index, values) {
							// return value.toLocaleString();
							return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
						}
					}
				}
			}
		}
	}
	
	// Chart Total Penjualan
	label_total_penjualan = [];
	// colors = ['rgb(99 174 206)', 'rgb(251 179 66)', 'rgb(62 185 110)'];
	colors = [dynamicColors(), dynamicColors(), dynamicColors()];
	// colors = ['rgb(76 162 199)', 'rgb(250 168 38)', 'rgb(37 176 91)'];
	
	num = 0;
	Object.keys(total_penjualan).map( tahun => {
		label_total_penjualan.push(tahun);
	});
	
	let dataChartTotalPenjualan = {
		labels: label_total_penjualan,
		datasets: [{
			data: total_penjualan,
			backgroundColor: colors,
			borderWidth: 1
		}]
	};
	
	configChartTotalPenjualan = {
		type: 'bar',
		data: dataChartTotalPenjualan,
		options: {
			responsive: false,
			maintainAspectRatio: false,
			aspectRatio: 1,
			plugins: {
			  legend: {
				display: false,
				position: 'top',
				fullWidth: false,
				labels: {
					padding: 10,
					boxWidth: 30
				}
			  },
			  display: false,
				text: '',
				fontSize: 14,
				lineHeight:3
			},
			tooltips: {
				callbacks: {
					label: function(tooltipItems, data) {
						// return data.labels[tooltipItems.index] + ": " + data.datasets[0].data[tooltipItems.index].toLocaleString();
						// return "Total : " + data.datasets[0].data[tooltipItems.index].toLocaleString();
						return "Total : " + data.datasets[0].data[tooltipItems.index].toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
					}
				}
			},
			scales: {
				y: {
					beginAtZero: false,
					ticks: {
						// stepSize: 500000000,
						callback: function(value, index, values) {
							// return value.toLocaleString();
							return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
						}
					}
				}
			}
		}
	}
	
	// Chart Item Terjual
	let item_terjual_bg = [];
	item_terjual.map( () => {
		item_terjual_bg.push(dynamicColors());
	})
	
	var configChartItemTerjual = {
		type: 'pie',
		data: {
			datasets: [{
				data: item_terjual,
				backgroundColor: item_terjual_bg,
			}],
			labels: item_terjual_label
		},
		options: {
			responsive: false,
			// maintainAspectRatio: false,
			title: {
				display: true,
				text: '',
				fontSize: 14,
				lineHeight:3
			},
			plugins: {
			  legend: {
				display: true,
				position: 'bottom',
				fullWidth: false,
				labels: {
					padding: 10,
					boxWidth: 30
				}
			  },
			  title: {
				display: false,
				text: 'Item Terjual'
			  }
			}
		}
	};
	
	data_kategori = JSON.parse(jumlah_item_kategori)
	
	let background_kategori = [];
	item_terjual.map( () => {
		background_kategori.push(dynamicColors());
	})
	
	const dataChartKategori = {
		labels: JSON.parse(label_kategori),
		datasets: [{
			label: 'Top Kategori',
			data: data_kategori,
			backgroundColor: background_kategori,
			hoverOffset: 4
		}]
	};

	const configChartKategori = {
		type: 'doughnut',
		data: dataChartKategori,
		options: {
			responsive: false,
			// maintainAspectRatio: false,
			title: {
				display: false,
				text: '',
				fontSize: 14,
				lineHeight:3
			},
			plugins: {
			  legend: {
				display: true,
				position: 'bottom',
				fullWidth: false,
				labels: {
					padding: 10,
					boxWidth: 30
				}
			  },
			  title: {
				display: false,
				text: 'Kategori'
			  }
			}
		}
	};

	/* Penjualan perbulan */
	var ctx = document.getElementById('bar-container').getContext('2d');
	window.chartPenjualan = new Chart(ctx, configChartPenjualan);
	
	/* Penjualan total */
	var ctx = document.getElementById('chart-total-penjualan').getContext('2d');
	window.chartPenjualan = new Chart(ctx, configChartTotalPenjualan);
	
	/* Item Terjual */
	var ctx = document.getElementById('pie-container').getContext('2d');
	window.chartItemTerjual = new Chart(ctx, configChartItemTerjual);

	/* Kategori */
	var ctx = document.getElementById('chart-kategori').getContext('2d');
	window.chartKategori = new Chart(ctx, configChartKategori);
	
	// Penjualan Tempo
	let dataTablesPenjualanTempo = '';
	let column = $.parseJSON($('#penjualan-tempo-column').html());
	let url = $('#penjualan-tempo-url').text();
	
	let settingPenjualanTempo = {
		"processing": true,
		"serverSide": true,
		"scrollX": true,
		pageLength : 5,
		lengthChange: false,
		"ajax": {
			"url": url,
			"type": "POST"
		},
		"columns": column
	}
	
	let $add_setting = $('#penjualan-tempo-setting');
	if ($add_setting.length > 0) {
		add_setting = $.parseJSON($('#penjualan-tempo-setting').html());
		for (k in add_setting) {
			settingPenjualanTempo[k] = add_setting[k];
		}
	}
	dataTablesPenjualanTempo =  $('#tabel-penjualan-tempo').DataTable( settingPenjualanTempo );
	
	// Penjualan Terbesar - Data Tables Ajax
	let dataTablesPenjualanTerbesar = '';
	column = $.parseJSON($('#penjualan-terbesar-column').html());
	url = $('#penjualan-terbesar-url').text();
	
	settings = {
		"processing": true,
		"serverSide": true,
		"scrollX": true,
		pageLength : 5,
		lengthChange: false,
		"ajax": {
			"url": url,
			"type": "POST"
		},
		"columns": column
	}
	
	$add_setting = $('#penjualan-terbesar-setting');
	if ($add_setting.length > 0) {
		add_setting = $.parseJSON($('#penjualan-terbesar-setting').html());
		for (k in add_setting) {
			settings[k] = add_setting[k];
		}
	}
	
	dataTablesPenjualanTerbesar =  $('#tabel-penjualan-terbesar').DataTable( settings );
	
	// Update Chart Penjualan
	$('#tahun-penjualan-perbulan').change(function() {
		$this = $(this);
		$spinner = $('<div class="spinner-container me-2" style="margin:auto">' + 
								'<div class="spinner-border spinner-border-sm"></div>' +
							'</div>').prependTo($this.parent());
							
		$.get(base_url + 'dashboard/ajaxGetPenjualan?tahun=' + $(this).val(), function(data) {
			$spinner.remove();
			if (data) {
				data_penjualan = JSON.parse(data);
	
				randomBackground = [];
		
				for (i = 0; i < 12; i++){
					randomBackground.push(dynamicColors());
				}
				
				dataChartPenjualan.datasets = [{
					backgroundColor: randomBackground, 
					borderWidth: 1,
					data: data_penjualan
				}];
				chartPenjualan.update();				
			}
		});
	})
	
	// Update IKontribusi Penjualan
	$('#tahun-barang-terlaris').change(function() {
		$this = $(this);
		settings.ajax.url = base_url + 'dashboard/getDataDTPenjualanTerbesar?tahun=' + $this.val();
		dataTablesPenjualanTerbesar.destroy();
		len = $('#tabel-penjualan-terbesar').find('thead').find('th').length;
		$('#tabel-penjualan-terbesar').find('tbody').html('<tr>' +
								'<td colspan="' + len + '" class="text-center">Loading data...</td>' +
							'</tr>');
		dataTablesPenjualanTerbesar =  $('#tabel-penjualan-terbesar').DataTable( settings );
	})
	
	// Update Chart Item Terjual
	$('#tahun-item-terjual').change(function() {
		$this = $(this);
		$spinner = $('<div class="spinner-container me-2" style="margin:auto">' + 
								'<div class="spinner-border spinner-border-sm"></div>' +
							'</div>').prependTo($this.parent());
							
		$.get(base_url + 'dashboard/ajaxGetItemTerjual?tahun=' + $(this).val(), function(data) {
			$spinner.remove();
			if (data) {
				data = JSON.parse(data);
				data_item_terjual = data.total;
				item_terjual_label = data.nama_item;
		
				randomBackground = [];
				data_item_terjual.map( () => {
					randomBackground.push(dynamicColors());
				})
								
				configChartItemTerjual.data = {
					datasets: [{
						data: item_terjual,
						backgroundColor: randomBackground,
					}],
					labels: item_terjual_label
				}
				chartItemTerjual.update();
			}
		});
	})
	
	// Update Kategori Terjual
	$('#tahun-kategori-terjual').change(function() {
		$this = $(this);
		$spinner = $('<div class="spinner-container me-2" style="margin:auto">' + 
								'<div class="spinner-border spinner-border-sm"></div>' +
							'</div>').prependTo($this.parent());
	
		$.get(base_url + 'dashboard/ajaxGetKategoriTerjual?tahun=' + $(this).val(), function(data) {
			$spinner.remove();
			if (data) {
				data = JSON.parse(data);
				data_kategori = data.total;
				data_kategori_label = data.nama_kategori;
		
				randomBackground = [];
				data_kategori.map( () => {
					randomBackground.push(dynamicColors());
				})
								
				configChartKategori.data = {
					labels: data_kategori_label,
					datasets: [{
						label: 'Top Kategori',
						data: data_kategori,
						backgroundColor: randomBackground,
						hoverOffset: 4
					}]
				}
				
				chartKategori.update();
			}
		});
	})
	
	// Update Kategori Terjual Detail
	$('#tahun-kategori-terjual-detail').change(function() {
		$this = $(this);
		$spinner = $('<div class="spinner-container me-2" style="margin:auto">' + 
								'<div class="spinner-border spinner-border-sm"></div>' +
							'</div>').prependTo($this.parent());
	
		$.get(base_url + 'dashboard/ajaxGetKategoriTerjual?tahun=' + $(this).val(), function(data) {
			$spinner.remove();
			if (data) {
				data = JSON.parse(data);
				html = '';
				data.item_terjual.map( item => {
					html += '<tr>' + 
						'<td><span class="text-warning h5"><i class="fas fa-folder"></i></span></td>' +
						'<td>' + item.nama_kategori + '</td>' +
						'<td class="text-end">' + item.nilai + '</td>' +

					'</tr>';
				})
				$this.parents('.card').eq(0).find('tbody').html(html);
			}
		});
	})
	
	// Update Penjualan Terbaru
	$('#tahun-penjualan-terbaru').change(function() {

		$this = $(this);
		$spinner = $('<div class="spinner-container me-2" style="margin:auto">' + 
								'<div class="spinner-border spinner-border-sm"></div>' +
							'</div>').prependTo($this.parent());
							
		if (dataTablesPenjualanTerbaru) {
			dataTablesPenjualanTerbaru.destroy();
		}
		
		$tbody = $this.parents('.card').eq(0).find('tbody');
		len = $this.parents('.card').eq(0).find('th').length;
		html = '<tr><td colspan="' + len + '">Loading data...</td></tr>';
		$tbody.html(html);
	
		$.get(base_url + 'dashboard/ajaxGetPenjualanTerbaru?tahun=' + $(this).val(), function(data) {
			$spinner.remove();
			if (data) {
				data = JSON.parse(data);
				html = '';
				data.map( (item, index) => {
					html += '<tr>' +
								'<td>' + (index + 1) + '</td>' +
								'<td>' + (item.nama_customer || 'Umum') + '</td>' +
								'<td class="text-end">' + item.jml_barang + '</td>' +
								'<td class="text-end">' + item.total_harga + '</td>' +
								'<td class="text-end">' + item.tgl_invoice + '</td>' +
								'<td>' + item.status + '</td>' +
							'</tr>';
				})
				
				$tbody.html(html);
				initDataTablesPenjualanTerbaru();
			}
		});
	})
	
	// Update Penjualan Tempo
	$('#jenis-penjualan-jatuh-tempo').change(function() {
		
		$this = $(this);
		settingPenjualanTempo.ajax.url = base_url + 'dashboard/getDataDTPenjualanTempo?start_date=' + $('#piutang-jatuh-tempo-start-date').val() + '&end_date=' + $('#piutang-jatuh-tempo-end-date').val() + '&jatuh_tempo='  + $this.val();
		dataTablesPenjualanTempo.destroy();
		len = $('#tabel-penjualan-tempo').find('thead').find('th').length;
		$('#tabel-penjualan-tempo').find('tbody').html('<tr>' +
								'<td colspan="' + len + '" class="text-center">Loading data...</td>' +
							'</tr>');
		dataTablesPenjualanTempo =  $('#tabel-penjualan-tempo').DataTable( settingPenjualanTempo );
	})
	
	$('#tahun-pelanggan-terbesar').change(function() {

		$this = $(this);
		$spinner = $('<div class="spinner-container me-2" style="margin:auto">' + 
								'<div class="spinner-border spinner-border-sm"></div>' +
							'</div>').prependTo($this.parent());
								
		$.get(base_url + 'dashboard/ajaxGetPelangganTerbesar?tahun=' + $(this).val(), function(data) {
			$spinner.remove();
			if (data) {
				data = JSON.parse(data);
				html = '';
				data.map( item => {
					html += '<tr>' +
								'<td>' + item.foto + '</td>' +
								'<td>' + item.nama_pelanggan + '</td>' +
								'<td class="text-end">' + item.total_harga + '</td>' +
							'</tr>';
				})
				
				$this.parents('.card').eq(0).find('tbody').html(html);
			}
		});
	})
		
	let dataTablesPenjualanTerbaru = '';
	function initDataTablesPenjualanTerbaru() {
		
		let export_title = $('#penjualan-terbaru').attr('data-export-title');
		let num_column = $('#penjualan-terbaru').find('th').length;
		let export_column = [];
		for (i = 0; i < num_column; i++) {
			export_column.push(i);
		}

		let settings = {
				"order":[4,"desc"]
				,"columnDefs":[{"targets":[0],"orderable":false}]
				, pageLength : 5
				, lengthChange: false
			};
		
		const addSettings = 
		{
			// "dom":"Bfrtip",
			"buttons":[
				{"extend":"copy"
					,"text":"<i class='far fa-copy'></i> Copy"
					,"className":"btn-light me-1"
				},
				{"extend":"excel"
					, "title": export_title
					, "text":"<i class='far fa-file-excel'></i> Excel"
					, "exportOptions": {
					  // columns: [2, 3, 4, 5, 6],
					  columns: export_column,
					  modifier: {selected: null}
					}
					, "className":"btn-light me-1"
				},
				{"extend":"pdf"
					,"title": export_title
					,"text":"<i class='far fa-file-pdf'></i> PDF"
					, "exportOptions": {
					  // columns: [2, 3, 4, 5, 6, 7],
					  columns: export_column,
					  modifier: {selected: null}
					}
					,"className":"btn-light me-1"
				}
			]
		}
		
		// Merge settings
		// settings['lengthChange'] = false;
		settings = {...settings, ...addSettings};
		
		// settings['buttons'] = [ 'copy', 'excel', 'pdf', 'colvis' ];
		dataTablesPenjualanTerbaru = $('#penjualan-terbaru').DataTable(settings);
		dataTablesPenjualanTerbaru.buttons().container()
			.appendTo( '#penjualan-terbaru_wrapper .col-md-6:eq(0)' );
			
		$('#penjualan-terbaru_wrapper').find('.row').eq(1).css('overflow', 'auto');
		
		// No urut
		dataTablesPenjualanTerbaru.on( 'order.dt search.dt', function () {
			dataTablesPenjualanTerbaru.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
				cell.innerHTML = i+1;
			} );
		} ).draw();
	}

	$('#tahun-penjualan-terbaru').trigger('change');
});