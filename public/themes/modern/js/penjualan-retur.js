/**
* Written by: Agus Prawoto Hadi
* Year		: 2021
* Website	: jagowebdev.com
*/

jQuery(document).ready(function () {
	
	list_barang_terpilih = {}
	$table = $('#list-produk');
	
	let dataTables = '';
	if ($('#table-result').length) {
		const column = $.parseJSON($('#dataTables-column').html());
		const url = $('#dataTables-url').text();
		
		const settings = {
			"processing": true,
			"serverSide": true,
			"scrollX": true,
			"ajax": {
				"url": url,
				"type": "POST"
			},
			"columns": column
		}
		
		let $add_setting = $('#dataTables-setting');
		if ($add_setting.length > 0) {
			add_setting = $.parseJSON($('#dataTables-setting').html());
			for (k in add_setting) {
				settings[k] = add_setting[k];
			}
		}
		dataTables =  $('#table-result').DataTable( settings );
	}
	
	$('.flatpickr').flatpickr({
		enableTime: false,
		dateFormat: "d-m-Y",
		time_24hr: true
	});
	
	
	// Modal Barang
	$('.cari-invoice').click(function() 
	{
		$this = $(this);
		if ($this.hasClass('disabled')) {
			return false;
		}
		
		let gudang = $('#gudang').val();
		let harga = $('#jenis-harga').val();
		var $modal = jwdmodal({
			title: 'Pilih Invoice',
			url: base_url + '/penjualan-retur/getDataDTListInvoice?id_gudang=' + gudang + '&id_jenis_harga=' + harga,
			width: '850px',
			action :function () 
			{
				
			}
			
		});
		
		$(document)
		.undelegate('.pilih-invoice', 'click')
		.delegate('.pilih-invoice', 'click', function() {
			// Invoice Popup
			$tr = $(this).parents('tr').eq(0);
			penjualan = JSON.parse($tr.find('.penjualan').eq(0).text());
			barang = penjualan['detail'];
		
			// List barang
			$tbody = $table.find('tbody').eq(0);
			$trs = $tbody.find('tr');
			$tr = $trs.eq(0).clone();
			$trs = $tbody.find('tr');
			
			$trs.remove();
			num = 0;
			
			$('#no-invoice').val(penjualan.no_invoice);
			$('#nama-customer').val(penjualan.nama_customer);
			$('#id-gudang').val(penjualan.id_gudang);
			$('#id-penjualan').val(penjualan.id_penjualan);

			Object.keys(barang).map(function(i, v) {
				item = barang[v];
				$new_tr = $tr.clone();
				$new_tr.find('.id-penjualan-detail').val(item.id_penjualan_detail);
				$td = $new_tr.find('td');
				$td.eq(0).html( parseInt(i) + 1);
				$new_tr.find('.nama-barang').html(item.nama_barang);
				$td.eq(2).html(item.satuan);
				$td.eq(3).find('input').val(format_ribuan(item.harga_satuan));
				$td.eq(4).find('input').val(format_ribuan(item.qty));
				$td.eq(7).find('input').val(format_ribuan(item.harga_neto));
				$tbody.append($new_tr);
				$new_tr.find('.diskon-barang, .qty-retur').val(0).trigger('keyup');
				
			})
			
			$table.find('#diskon-total, #penyesuaian').val(0).trigger('keyup');
			$table.show();
			$modal.remove();
		});
	});
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	$('#table-result').delegate('.del-data', 'click', function(e) {
		id = $(this).attr('data-id');
		$this = $(this);
		$bootbox = bootbox.confirm({
			message: $(this).attr('data-delete-message'),
			callback: function(confirmed) {
				if (confirmed) {
					$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
					$bootbox.find('button').attr('disabled', 'disabled');
					$bootbox.find('button.bootbox-accept').prepend($spinner);
					$.ajax({
						type: 'POST',
						url: base_url + 'penjualan-retur/ajaxDeleteData',
						data: 'id=' + id,
						dataType: 'json',
						success: function (data) {
							$bootbox.modal('hide');
							if (data.status == 'ok') {
								const Toast = Swal.mixin({
									toast: true,
									position: 'top-end',
									showConfirmButton: false,
									timer: 2500,
									timerProgressBar: true,
									iconColor: 'white',
									customClass: {
										popup: 'bg-success text-light toast p-2'
									},
									didOpen: (toast) => {
										toast.addEventListener('mouseenter', Swal.stopTimer)
										toast.addEventListener('mouseleave', Swal.resumeTimer)
									}
								})
								Toast.fire({
									html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Data berhasil dihapus</div>'
								})
								dataTables.draw();
							} else {
								$bootbox.find('button').removeAttr('disabled');
								$spinner.remove();
								show_alert('Error !!!', data.message, 'error');
							}
						},
						error: function (xhr) {
							show_alert('Error !!!', xhr.responseText, 'error');
							console.log(xhr.responseText);
						}
					})
				}
				return false;
			},
			centerVertical: true
		});
	});
	
	$('#submit').click(function(e) {
		
		e.preventDefault();
		if ($table.is(':hidden')) {
			bootbox.alert('<div class="d-flex my-2"><span class="text-danger"><i class="fas fa-exclamation-triangle me-3" style="font-size:20px"></i></span>Barang belum dipilih</div>');
			return false;
		}
		
		$this = $(this);
		$form = $('form');
		$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
		const data = $form.serialize();
		$form.find('input, select, button').attr('disabled', 'disabled');
		$this.prepend($spinner);
		$.ajax({
			url: base_url + 'penjualan-retur/ajaxSaveData',
			data: data,
			method: 'post',
			success: function (data) {
				data = JSON.parse(data);
				console.log(data);
				bootbox.alert('<div class="d-flex my-2"><span class="text-success"><i class="fas fa-check-circle me-3" style="font-size:20px"></i></span>Data berhasil disimpan</div>');
				$('#id-penjualan-retur').val(data.id_penjualan_retur);
				$form.find('input, select:not(#id-gudang), button').removeAttr('disabled');
				$spinner.remove();
			},
			error: function(xhr) {
				bootbox.alert('<div class="d-flex my-2"><span class="text-danger"><i class="fas fa-times-circle me-3" style="font-size:20px"></i></span>Ajax Error: cek console browser</div>');
				$form.find('input, select, button').removeAttr('disabled');
				$spinner.remove();
				console.log(xhr);
			}
		})
		
	});
	
	$('.tanggal-invoice').change(function() {
		// alert();
		split = this.value.split('-');
		let date = new Date(split[2] + '-' + split[1] + '-' + split[0] + ' 00:00:00');
		date.setDate(date.getDate() + 21);
		d = "0" + date.getDate();
		m = "0" + (date.getMonth() + 1);
		y = date.getFullYear()
		$('.tanggal-jatuh-tempo').val(d.substr(-2) + '-' + m.substr(-2) + '-' + y);
	});
	
	$('#jenis-harga').change(function() {
		if (Object.keys(list_barang_terpilih).length == 0)
			return;
		
		id_jenis_harga = this.value;
		$('.id-barang').each(function(i, el) {
			id_barang = $(el).val();
			$tr = $(el).parents('tr').eq(0);
			new_harga = list_barang_terpilih[id_barang]['list_harga'][id_jenis_harga];
			$tr.find('.harga-satuan').val(new_harga).trigger('keyup');
		})
	})
		
	$('form').delegate('.harga', 'keyup', function() 
	{
		calculate_total();
	});
	
	$('table').delegate('.qty-retur', 'keyup', function() 
	{
		let value = setInt(this.value);
		$tr = $(this).parents('tr').eq(0);
		let qty_jual = setInt($tr.find('.qty-jual').val());
		if (value > qty_jual) {
			console.log('ccc');
			this.value = format_ribuan(qty_jual);
		} else {
			console.log('ccc33');
			this.value = format_ribuan(value);
		}
		
		$(this).parents('tr').eq(0).find('.harga-satuan').trigger('keyup');
	});
	
	$('table').delegate('.harga-satuan', 'keyup', function() 
	{
		$tr = $(this).parents('tr').eq(0);
		harga_satuan = setInt( this.value );
		qty = setInt( $tr.find('.qty-retur').val() );
		harga_total = qty * harga_satuan;
		diskon_jenis = $tr.find('.diskon-barang-jenis').val();
		diskon = setInt($tr.find('.diskon-barang').val());
		if (diskon) {

			if (diskon_jenis == '%') {
				jumlah_diskon = Math.round(harga_total * diskon / 100);
			} else {
				jumlah_diskon = diskon;
			}
			harga_total = harga_total - jumlah_diskon;
		}
		$tr.find('.harga-total-retur').val( format_ribuan(harga_total) );
		this.value = format_ribuan( harga_satuan );
		calculate_total();
	});
	
	$('#diskon-total').keyup(function() 
	{
		let diskon_value = setInt(this.value);
		let diskon_total_jenis = $('#diskon-total-jenis').val();
		if (diskon_value) {
			if (diskon_total_jenis == '%') {
				if (diskon_value > 100) {
					diskon_value = 100
				}
			} 
		}
		this.value = format_ribuan(diskon_value);
		calculate_total();
	});
	
	$('#diskon-total-jenis').change(function() 
	{
		calculate_total();
	});
	
	$('table').delegate('.diskon-barang', 'keyup', function() 
	{
		let diskon_value = setInt(this.value);
		$tr = $(this).parents('tr').eq(0);
		diskon_jenis = $tr.find('.diskon-barang-jenis').val();
		diskon = setInt($tr.find('.diskon-barang').val());
		if (diskon) {
			if (diskon_jenis == '%') {
				if (diskon_value > 100) {
					diskon_value = 100
				}
			} 
		}
		this.value = format_ribuan(diskon_value);
		$(this).parents('tr').eq(0).find('.harga-satuan').trigger('keyup');
	});
	
	$('table').delegate('.diskon-barang-jenis', 'change', function() 
	{
		$(this).parents('tr').eq(0).find('.diskon-barang').trigger('keyup');
	});
		
	$('table').delegate('.del-row', 'click', function() 
	{
		$this = $(this);
		$table = $this.parents('table');
		$tbody = $table.find('tbody').eq(0);
		$trs = $tbody.find('tr');
		id = $table.attr('id');

		if ($trs.length == 1) {
			$trs.find('input').val('');
			$tbody.parent().hide();
			$('#nama-customer').val('');
			$('#no-invoice').val('');
		} else {
			$this.parents('tr').eq(0).remove();
			$new_trs = $tbody.find('tr');
			$new_trs.each(function(i, elm) {
				$(elm).find('td').eq(0).html(i + 1);
			});
		}
		
		if (id == 'list-pembayaran') {
			$tbody.find('.item-bayar').eq(0).trigger('keyup');
		} else if (id == 'list-barang') {
			$tbody.find('.harga-satuan').eq(0).trigger('keyup');
		}
		
		calculate_total()
	});
	
	// Total
	
	function update_penyesuaian() {
		operator = $('#operator-penyesuaian').val();
		penyesuaian = setInt( $('#penyesuaian').val());
		if (operator == '-') {
			if ( penyesuaian > 0 ) {
				penyesuaian = penyesuaian * -1;
			}
		} else {
			if ( penyesuaian < 0 ) {
				penyesuaian = penyesuaian * -1;
			}
		}
		$('#penyesuaian').val(format_ribuan(penyesuaian));
		calculate_total();
	}
	
	$('form').delegate('#penyesuaian', 'keyup', function() 
	{
		update_penyesuaian();
	});
	
	$('form').delegate('#operator-penyesuaian', 'change', function() 
	{
		update_penyesuaian();
	});
	
	function calculate_total() 
	{
		$input_harga = $('#list-produk').find('.harga-total-retur');

		subtotal = 0;
		$input_harga.each(function(i, elm) 
		{
			value = $(elm).val();
			subtotal += setInt( value );
		});
		$('#subtotal').val(format_ribuan(subtotal));
		
		// Diskon
		let diskon_total_jenis = $('#diskon-total-jenis').val();
		let diskon_total = setInt( $('#diskon-total').val() );
		if (diskon_total) {
			if (diskon_total_jenis == '%') {
				jumlah_diskon = Math.round(subtotal * diskon_total / 100);
			} else {
				jumlah_diskon = diskon_total;
			}
			subtotal = subtotal - jumlah_diskon;
		}
		
		/* operator = $('#operator-penyesuaian').val();
		penyesuaian = setInt( $('#penyesuaian').val());
		if (operator == '-') {
			neto = subtotal - penyesuaian;
		} else {
			neto = subtotal + penyesuaian;
		} */
		
		penyesuaian = setInt( $('#penyesuaian').val());
		neto = subtotal + penyesuaian;

		$('#total').val(format_ribuan(neto));
		$('.item-bayar').eq(0).trigger('keyup');
		// $('.kurang-bayar').val(format_ribuan(neto));
		
	}
	//-- Total
	
	
	
	// Customer
	$('.cari-customer').click(function() {
		$this = $(this);
		var $modal = jwdmodal({
			title: 'Pilih Customer',
			url: base_url + '/penjualan/getListCustomer',
			width: '950px',
			action :function () 
			{
				
			}
		});
		
		$(document)
		.undelegate('.pilih-customer', 'click')
		.delegate('.pilih-customer', 'click', function() {
			
			// Customer popup
			$this = $(this);
			$this.attr('disabled', 'disabled');
			customer = JSON.parse($(this).next().text())
			$('#id-customer').val(customer.id_customer);
			$('#nama-customer').val(customer.nama_customer);
			$('#del-customer').show();
			$modal.remove();
		});
	});
	
	$('#del-customer').click(function() {
		$('#id-customer').val('');
		$('#nama-customer').val('Tidak diketahui');
		$(this).hide();
	})
	
	// Invoice - PDF
	$('body').delegate('.save-pdf', 'click', function(e){
		e.preventDefault();
		$this = $(this);
		url = $this.attr('href');
		filename = $this.attr('data-no-invoice').replace('/','_').replace('\\', '_')
		
		$swal =  Swal.fire({
			title: 'Memproses Invoice',
			text: 'Mohon sabar menunggu...',
			showConfirmButton: false,
			allowOutsideClick: false,
			didOpen: function () {
			  	Swal.showLoading();
			},
			didClose () {
				Swal.hideLoading()
			},
		});
		
		fetch(url)
		  .then(resp => resp.blob())
		  .then(blob => {
				saveAs(blob, 'Invoice-' + filename + '.pdf');
				$swal.close();
		  })
		.catch(() => alert('Ajax Error'));

	})
	
	$('body').delegate('.kirim-email', 'click', function(e){
		e.preventDefault();
		$this = $(this)
		email = $this.attr('data-email');
		id = $this.attr('data-id');

		$swal =  Swal.fire({
			title: 'Memproses invoice',
			text: 'Mohon sabar menunggu...',
			showConfirmButton: false,
			allowOutsideClick: false,
			didOpen: function () {
			  	Swal.showLoading();
			},
			didClose () {
				Swal.hideLoading()
			},
		});

		url = base_url + "penjualan/invoice?id=" + id + '&email=Y';

		$.ajax({
			type: "GET",
			url: url,
			dataType: "JSON",
			success: function(data) {
				className = data.status == 'ok' ? 'success' : 'error';
				title = data.status == 'ok' ? 'Sukses !!!' : 'Error !!!';
				$swal.close();
				console.log(className);
				Swal.fire({
					text: data.message,
					title: title,
					icon: className,
					showCloseButton: true,
					confirmButtonText: 'OK'
				})
			}, error: function (xhr) {
				console.log(xhr);
			}
			
		});


	})
});