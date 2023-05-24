/**
* Written by: Agus Prawoto Hadi
* Year		: 2021
* Website	: jagowebdev.com
*/

jQuery(document).ready(function () {
	
	list_barang_terpilih = {}
	$table = $('#list-produk');
	
	// Edit
	if (!$('#list-produk').is(':hidden')) {
		list = $('#list-barang-terpilih').text();
		if (list) {
			list_barang_terpilih = JSON.parse(list);
		}
	}
	
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
						url: base_url + 'transfer-barang/ajaxDeleteData',
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
					return false;
				}
			},
			centerVertical: true
		});
	});
	
	$('#submit').click(function(e) {
		
		e.preventDefault();
		if ( $('#gudang-asal').val() == $('#gudang-tujuan').val()) {
			bootbox.alert('<div class="d-flex my-2"><span class="text-danger"><i class="fas fa-exclamation-triangle me-3" style="font-size:20px"></i></span>Gudang asal dan gudang tujuan tidak boleh sama</div>');
			return false;
		}
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
			url: base_url + 'transfer-barang/ajaxSaveData',
			data: data,
			method: 'post',
			success: function (data) {
				data = JSON.parse(data);
				console.log(data);
				bootbox.alert('<div class="d-flex my-2"><span class="text-success"><i class="fas fa-check-circle me-3" style="font-size:20px"></i></span>Data berhasil disimpan</div>');
				$('#id-transfer-barang').val(data.id_transfer_barang);
				$('#no-nota-transfer').val(data.no_nota_transfer);
				$form.find('input, select, button').removeAttr('disabled');
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
	
	$('.tanggal-nota-transfer').change(function() {
		// alert();
		split = this.value.split('-');
		let date = new Date(split[2] + '-' + split[1] + '-' + split[0] + ' 00:00:00');
		date.setDate(date.getDate() + 21);
		d = "0" + date.getDate();
		m = "0" + (date.getMonth() + 1);
		y = date.getFullYear()
		$('.tanggal-jatuh-tempo').val(d.substr(-2) + '-' + m.substr(-2) + '-' + y);
	});
	
	$('#gudang-asal').change(function() {
		id_gudang = this.value;
		$('.id-barang').each(function(i, el) {
			id_barang = $(el).val();
			$tr = $(el).parents('tr').eq(0);
			new_stok = list_barang_terpilih[id_barang]['list_stok'][id_gudang];
			$tr.find('.jml-stok').text(new_stok);
			$tr.find('.qty').trigger('keyup');
		})
	})
	
	$('#jenis-harga').change(function() {
		if (Object.keys(list_barang_terpilih).length == 0)
			return;
		console.log(list_barang_terpilih);
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
	
	$('table').delegate('.qty', 'keyup', function() 
	{
		let value = setInt(this.value);
		$tr = $(this).parents('tr').eq(0);
		let jml_stok = setInt($tr.find('.jml-stok').text());
		if (value > jml_stok) {
			this.value = format_ribuan(jml_stok);
		} else {
			this.value = format_ribuan(value);
		}
		
		$(this).parents('tr').eq(0).find('.harga-satuan').trigger('keyup');
	});
	
	$('table').delegate('.harga-satuan', 'keyup', function() 
	{
		$tr = $(this).parents('tr').eq(0);
		harga_satuan = setInt( this.value );
		qty = setInt( $tr.find('.qty').val() );
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
		$tr.find('.harga-total').val( format_ribuan(harga_total) );
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
			if (id == 'list-pembayaran') {
				$('#using-pembayaran').val(0);
			} else if (id == 'list-barang') {
				$('#using-list-barang').val(0);
			}
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
	
	$('form').delegate('#penyesuaian', 'keyup', function() 
	{
		this.value = format_ribuan(this.value);
		calculate_total();
	});
	
	$('form').delegate('#operator-penyesuaian', 'change', function() 
	{
		calculate_total();
	});
	
	function calculate_total() 
	{
		$input_harga = $('#list-produk').find('.harga-total');

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
		
		operator = $('#operator-penyesuaian').val();
		penyesuaian = setInt( $('#penyesuaian').val());
		if (operator == '-') {
			neto = subtotal - penyesuaian;
		} else {
			neto = subtotal + penyesuaian;
		}

		$('#total').val(format_ribuan(neto));
		$('.item-bayar').eq(0).trigger('keyup');
		// $('.kurang-bayar').val(format_ribuan(neto));
		
	}
	//-- Total
	
	$('.barcode').keypress(function(e) {
		if (e.which == 13) {
			return false;
		}
	})
	
	$('.barcode').keyup(function(e) {
		
		$this = $(this);
		value = $this.val().replace(/\D/g,'');
		this.value = value.substr(0,13);
		// console.log(value.length);
		if (value.length >= 13) 
		{
			value = value.substr(0,13);
			$spinner = $('<div class="spinner-border text-secondary spinner" style="height: 18px; width:18px; position:absolute; left:-15px; top:7px" role="status"><span class="visually-hidden">Loading...</span></div>');
			$parent = $this.parent().parent();
			$parent.find('.spinner').remove();
			$spinner.appendTo($parent);
			$this.attr('disabled', 'disabled');
			$('.add-barang').attr('disabled', 'disabled').addClass('disabled');
			$.ajax({
				url : base_url + 'pembelian/ajaxGetBarangByBarcode?code=' + value
				, success : function (data) {
					console.log(data);
					
					$parent.find('.spinner').remove();
					$this.removeAttr('disabled');
					$('.add-barang').removeAttr('disabled').removeClass('disabled');
					
					data = JSON.parse(data);
					if (data.status == 'ok') {
						addBarang(data.data);
						$this.val('').focus();
					} else {
						const Toast = Swal.mixin({
							toast: true,
							position: 'bottom-end',
							showConfirmButton: false,
							timer: 2500,
							timerProgressBar: true,
							iconColor: 'white',
							customClass: {
								popup: 'bg-danger text-light toast p-2 mb-3'
							},
							didOpen: (toast) => {
								toast.addEventListener('mouseenter', Swal.stopTimer)
								toast.addEventListener('mouseleave', Swal.resumeTimer)
							}
						})
						Toast.fire({
							html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Data tidak ditemukan</div>'
						})
					}
				}, error: function() {
					
				}
			})
		}
		
	})
	
	function addBarang(barang) {
		list_barang_terpilih[barang.id_barang] = barang;

		// List barang
		$tbody = $table.find('tbody').eq(0);
		
		$trs = $tbody.find('tr');
		$tr = $trs.eq(0).clone();
		num = $trs.length;
		if ($table.is(':hidden')) {
			$trs.remove();
			num = 0;
		}

		$td = $tr.find('td');
		$td.eq(0).text(num + 1);
		$td.eq(1).text(barang.nama_barang);
		$td.eq(2).html('<span class="jml-stok">' + barang.stok + '</span>');
		$td.eq(3).html(barang.satuan);
		
		$tr.find('.qty').val(1);
		$tr.find('.diskon-barang').val('');
		harga_jual = barang.harga_pokok || 0;
		$harga_satuan = $tr.find('.harga-satuan').val(harga_jual);
		
		$id_barang = $tr.find('.id-barang');
		$parent = $id_barang.parent();
		$id_barang.remove();
		$parent.append('<input type="hidden" class="id-barang" name="id_barang[]" value="'+ barang.id_barang +'"/>');
			
		$table.show();
		$tbody.append($tr);
		
		$harga_satuan.trigger('keyup');
		
		$tr.find('.flatpickr').flatpickr({
			enableTime: false,
			dateFormat: "d-m-Y",
			time_24hr: true
		});
		
		$('.list-barang-terpilih').find('.belum-ada').remove();
		$('.list-barang-terpilih').append('<small  class="px-3 py-2 me-2 mb-2 text-light bg-success bg-opacity-10 border border-success border-opacity-10 rounded-2">' + barang.nama_barang + '</small>');
	}
	
	// Modal Barang
	$('.add-barang').click(function() 
	{
		$this = $(this);
		if ($this.hasClass('disabled')) {
			return false;
		}
		
		let gudang = $('#gudang-asal').val();
		let harga = $('#jenis-harga').val();
		var $modal = jwdmodal({
			title: 'Pilih Barang',
			url: base_url + '/transfer-barang/getDataDTListBarang?id_gudang=' + gudang + '&id_jenis_harga=' + harga,
			width: '850px',
			action :function () 
			{
				$trs = $table.find('tbody').eq(0).find('tr');
				var list_barang = '<span class="belum-ada mb-2">Silakan pilih barang</span>';
				if ($table.is(':visible')) {
					var list_barang = '';
					$trs.each (function (i, elm) {
						$td = $(elm).find('td');
						list_barang += '<small  class="px-3 py-2 me-2 mb-2 text-light bg-success bg-opacity-10 border border-success border-opacity-10 rounded-2">' + $td.eq(1).html() + '</small>';
					});
				}
				$('.jwd-modal-header-panel').prepend('<div class="list-barang-terpilih">' + list_barang + '</div>');
			}
			
		});
		
		$(document)
		.undelegate('.pilih-barang', 'click')
		.delegate('.pilih-barang', 'click', function() {
						
			// Barang Popup
			$tr = $(this).parents('tr').eq(0);
			barang = JSON.parse($tr.find('.detail-barang').text());
			
			addBarang(barang);
		});
	});
	
	$('table').delegate('.item-bayar', 'keyup', function() 
	{
		let bayar = 0;
		$('table').find('.item-bayar').each(function(i, el) {
			number = setInt($(el).val());
			if (!number)
				number = 0;
			bayar += number;
		})
		
		
		total = setInt($('#total').val());
		sisa = total - bayar;
		
		this.value = format_ribuan( this.value );
		if (sisa == 0) {
			$('.sisa').text('Lunas');
			$('.kurang-bayar').removeClass('text-danger');
			
		} else {
			if (sisa < 0) {
				sisa = sisa * -1;
				$('.sisa').text('Kembali');
				$('.kurang-bayar').addClass('text-danger');
			} else {
				$('.sisa').text('Kurang');
				$('.kurang-bayar').addClass('text-danger');
			}
		}
		$('.kurang-bayar').val( format_ribuan(sisa) )
	})
	
	$('.add-pembayaran').click(function() {
		$tr = $(this).parents('tr').eq(0);
		$tfoot = $tr.parent();
		$new_tr = $tr.clone();
		$new_tr.find('input').val('');
		$new_tr.find('td').eq(0).text( $tfoot.children('tr').length - 1 );
		$new_tr.find('td').last().html( '<button type="button" class="btn text-danger del-pembayaran"><i class="fas fa-times"></i></button>' );
		$new_tr.insertBefore($tfoot.find('tr').last());
		$('.flatpickr').flatpickr({
			enableTime: false,
			dateFormat: "d-m-Y",
			time_24hr: true
		});
	});
	
	$('table').delegate('.del-pembayaran', 'click', function() {
		$tr = $(this).parents('tr').eq(0);
		$tfoot = $tr.parent();
		$tr.remove();
		$list_tr = $tfoot.find('tr');
		$list_tr.each(function(i, el) {
			if (i > 0 && i < ($list_tr.length - 1)) {
				$(el).find('td').eq(0).text( i );
			}
		});
		$table.find('.item-bayar').eq(0).trigger('keyup');
	});
	
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
		filename = $this.attr('data-filename').replace('/','_').replace('\\', '_')
		
		$swal =  Swal.fire({
			title: 'Memproses Nota Transfer',
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
				saveAs(blob, filename + '.pdf');
				$swal.close();
		  })
		.catch(() => alert('Ajax Error'));

	})
	
});