/**
* Written by: Agus Prawoto Hadi
* Year		: 2021
* Website	: jagowebdev.com
*/

jQuery(document).ready(function () {
	
	$('.flatpickr').flatpickr({
		enableTime: false,
		dateFormat: "d-m-Y",
		time_24hr: true
	});
	
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
			"columns": column,
			"initComplete": function( settings, json ) {
				table.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
					$row = $(this.node());
					/* this
						.child(
							$(
								'<tr>'+
									'<td>'+rowIdx+'.1</td>'+
									'<td>'+rowIdx+'.2</td>'+
									'<td>'+rowIdx+'.3</td>'+
									'<td>'+rowIdx+'.4</td>'+
								'</tr>'
							)
						)
						.show(); */
				} );
			 }
		}
		
		let $add_setting = $('#dataTables-setting');
		if ($add_setting.length > 0) {
			add_setting = $.parseJSON($('#dataTables-setting').html());
			for (k in add_setting) {
				settings[k] = add_setting[k];
			}
		}
		
		const table =  $('#table-result').DataTable( settings );
	}
	
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
	
	$('.select2').select2({theme: 'bootstrap-5'});
	
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
	
	function addBarang(item) 
	{
		$table = $('#list-barang');

		// List barang
		$tbody = $table.find('tbody').eq(0);
		
		// exists
		$id_barang = $tbody.find('input[value="' + item.id_barang + '"');
		if ($id_barang.length) {
			return;
		}
		
		// Add New
		
		$trs = $tbody.find('tr');
		$tr = $trs.eq(0).clone();
		num = $trs.length;
		if ($table.is(':hidden')) {
			$trs.remove();
			num = 0;
		}

		$td = $tr.find('td');
		$td.eq(0).html(num + 1);
		$td.eq(1).html(item.nama_barang);
		
		$td.eq(1).find('input[name="id_barang[]"]').remove();
		$td.eq(1).find('input').val("");
		$td.eq(1).append('<input type="hidden"class name="id_barang[]" value="'+ item.id_barang +'"/>');
		
		$td.eq(2).find('input').val('');

		$td.eq(3).find('input').val("");
		$td.eq(4).find('input').val("");
		$td.eq(5).find('input').val('');
		$td.eq(5).find('.satuan').text(item.satuan);
		
		$table.show();
		$tbody.append($tr);
		
		$tr.find('.flatpickr').flatpickr({
			enableTime: false,
			dateFormat: "d-m-Y",
			time_24hr: true
		});
	}
	
	// console.log (format_ribuan(10000));
	$('.add-barang').click(function() {
		$this = $(this);
		if ($this.hasClass('disabled')) {
			return false;
		}
		var $modal = jwdmodal({
			title: 'Pilih Barang',
			url: base_url + '/pembelian/getDataDTListBarang?id_gudang=' + $('#id-gudang').val(),
			width: '850px',
			action :function () 
			{
				$table = $('#list-barang');
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
			
			$('#using-list-barang').val(1);
			$table = $('#list-barang');

			// Barang Popup
			$tr = $(this).parents('tr').eq(0);
			$td = $tr.find('td');
			nama_barang = $td.eq(1).find('.nama-barang').text();
			item = $td.eq(1).find('.detail-barang').text();
			stok = $td.eq(2).html();
			satuan = $td.eq(3).html();
			harga_modal = $td.eq(5).html();
			harga = $td.eq(6).html();
			$this.attr('disabled', 'disabled');
			
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
			$td.eq(0).html(num + 1);
			$td.eq(1).html(nama_barang);
			
			$td.eq(1).find('input[name="id_barang[]"]').remove();
			$td.eq(1).find('input').val("");
			$td.eq(1).append('<input type="hidden"class name="id_barang[]" value="'+ $(this).attr('data-id-barang') +'"/>');
			
			$td.eq(2).find('input').val('');

			$td.eq(3).find('input').val("");
			$td.eq(4).find('input').val("");
			$td.eq(5).find('input').val('');
			$td.eq(5).find('.satuan').text(satuan);
			
			$table.show();
			$tbody.append($tr);
			
			$tr.find('.flatpickr').flatpickr({
				enableTime: false,
				dateFormat: "d-m-Y",
				time_24hr: true
			});
			
			$('.list-barang-terpilih').find('.belum-ada').remove();
			$('.list-barang-terpilih').append('<small  class="px-3 py-2 me-2 mb-2 text-light bg-success bg-opacity-10 border border-success border-opacity-10 rounded-2">' + nama_barang + '</small>');
			
			// $(document);
		});
	});
	
	$('table').delegate('.format-ribuan', 'keyup', function() {
		this.value = this.value.replace(/\D/g,'');
		if (this.value == '')
			this.value = 0;
		
		this.value = parseInt(this.value, 10);
		this.value = format_ribuan(this.value);
	});
	
	$('table').delegate('.item-bayar', 'keyup', function() {
		$this = $(this);
		$table = $this.parents('table');
		$tbody = $table.find('tbody').eq(0);
		$item_bayar = $tbody.find('.item-bayar');
		
		total_bayar = 0;
		$item_bayar.each(function (i, elm) {
			total_bayar += parseInt($(elm).val().replace(/\D/g, ''));
		});
		
		total_tagihan = parseInt($('.total-tagihan').val().replace(/\D/g, ''), 10);
		kurang_bayar = total_tagihan - total_bayar;
		if (kurang_bayar < 0) {
			kurang_bayar = 0;
		}
		$table.find('.total-bayar').val(total_bayar).trigger('keyup');
		$table.find('.kurang-bayar').val(kurang_bayar).trigger('keyup');
	});
	
	$('.add-pembayaran').click(function() {
		
		$('#using-pembayaran').val(1);
		$table = $('#list-pembayaran');
			
		// List barang
		$tbody = $table.find('tbody').eq(0);
		
		$trs = $tbody.find('tr');
		$tr = $trs.eq(0).clone();
		num = $trs.length;
		if ($table.is(':hidden')) {
			$table.show();
			$trs.remove();
			num = 0;
		}
		
		$td = $tr.find('td');
			
		$td.eq(0).html(num + 1);
		$tr.find('input').val('');
		
		//
		$select = $tr.find('select');
		value = $select.find('option').eq(0).attr('value');
		$select.val(value);
		
		$table.append($tr);
	
		$tr.find('.flatpickr').flatpickr({
			enableTime: false,
			dateFormat: "d-m-Y",
			time_24hr: true
		});
	})

	$('table').delegate('.harga-satuan', 'keyup', function() 
	{
		$tr = $(this).parents('tr').eq(0);
		harga_satuan = setInt( this.value );
		qty = setInt( $tr.find('.qty').val() );
		harga_neto = qty * harga_satuan;
		diskon_jenis = $tr.find('.diskon-barang-jenis').val();
		diskon = setInt($tr.find('.diskon-barang').val());
		if (diskon) {

			if (diskon_jenis == '%') {
				jumlah_diskon = Math.round(harga_neto * diskon / 100);
			} else {
				jumlah_diskon = diskon;
			}
			harga_neto = harga_neto - jumlah_diskon;
		}
		$tr.find('.harga-total').val( format_ribuan(harga_neto) );
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

	$('table').delegate('.dbj', 'change', function() 
	{
		$(this).parents('tr').eq(0).find('.db').trigger('keyup');
	});
	
	$('#list-barang').delegate('.harga-satuan, .kuantitas, .db', 'keyup', function() 
	{
		$this = $(this);
		$table = $this.parents('table');
		$tr = $this.parents('tr').eq(0);
		$tbody = $table.find('tbody').eq(0);
		
		harga_satuan = setInt($tr.find('.harga-satuan').val());
		kuantitas =  setInt($tr.find('.kuantitas').val());
		db =  setInt($tr.find('.db').val());

		dbj = $tr.find('.dbj').val();

		if (dbj == '%') {
			jumlah_diskon = Math.round(harga_satuan * kuantitas * db / 100);
		} else {
			jumlah_diskon = db;
		}

		$tr.find('.harga-total').val(format_ribuan(harga_satuan * kuantitas - jumlah_diskon));
		
		sub_total = 0;
		$list_harga_satuan = $tbody.find('.harga-total');
		$list_harga_satuan.each(function (i, elm) {
			elm_val = $(elm).val();
			if (elm_val == '') {
				elm_val = '0';
			}
			sub_total += parseInt(elm_val.replace(/\D/g, ''), 10);
			// console.log(sub_total);
		});
		
		diskon = $table.find('.diskon').val().replace(/\D/g, '');
		total = sub_total - diskon;
		if (total < 0) {
			total = 0;
		}
		$table.find('.sub-total').val(sub_total).trigger('keyup');
		$table.find('.total').val(total).trigger('keyup');
		
		$('#list-pembayaran').find('.total-tagihan').val(total).trigger('keyup');
		$('#list-pembayaran').find('.item-bayar').eq(0).trigger('keyup');
		
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
	});
	
	$('.terima-barang-option').change(function() {
		if (this.value == 'Y') {
			$('.terima-barang-container').show();
		} else {
			$('.terima-barang-container').hide();
		}
		
	});
	
	$('.diskon').keyup(function() {
		total = setInt( $('#list-barang').find('.sub-total').val() ) - setInt(this.value);
		$('.total, .total-tagihan').val(format_ribuan(total));
		$('.item-bayar').eq(0).trigger('keyup');
	
	
		/* sub_total = $('.sub-total').val().replace(/\D/g, '');
		diskon = $('.diskon').val().replace(/\D/g, '');
		neto = sub_total - diskon;
		if (neto < 0) {
			neto = 0;
		}
		$('.total').val(neto);
		$('.total').trigger('keyup'); */
	})
});