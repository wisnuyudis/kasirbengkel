$(document).ready(function() {
	
	$(document).undelegate('.setting-barang', 'click').delegate('.setting-barang', 'click', function()  {
		$gudang = $('#id-gudang');
		$gudang_clone = $gudang.clone().val($gudang.val())
		$harga = $('#id-jenis-harga');
		$harga_clone = $harga.clone().val($harga.val())
		
		content = '<div class="row mb-3">' + 
						'<label class="col-sm-4">Gudang</label>' +
						'<div class="col-sm-8 modal-gudang-option">' + 
							 
						'</div>' +
					'</div>' + 
					'<div class="row mb-3">' + 
						'<label class="col-sm-4">Harga</label>' +
						'<div class="col-sm-8 modal-harga-option">' + 
							
						'</div>' +
					'</div>';
		$bootbox =  bootbox.dialog({
			title: 'Setting',
			message: content,
			buttons: {
				cancel: {
					label: 'Cancel'
				},
				success: {
					label: 'OK',
					className: 'btn-success submit',
					callback: function() 
					{
						$('#id-gudang').val($gudang_clone.val());
						$('#id-jenis-harga').val($harga_clone.val());
						
						dataTables.destroy();
						$('#tabel-data').find('tbody').remove();
						$('#tabel-data').find('th').eq(0).css('width', '64px');

						loadDataTables(base_url + 'pos-kasir/getDataDTBarang?id_gudang=' + $('#id-gudang').val() + '&id_jenis_harga=' + $('#id-jenis-harga').val(), true);
						changeHargaStok();
						$bootbox.hide();
					}
					
				}
			}
		});
		
		$bootbox.find('.modal-gudang-option').html('').append($gudang_clone.show());
		$bootbox.find('.modal-harga-option').html('').append($harga_clone.show());
	})
	
	$('div.dataTables_filter input').on('keypress keyup', function() {
		dataTables.search(this.value).draw();
	});
		
	$(document).undelegate('#id-gudang, #id-jenis-harga', 'change').delegate('#id-gudang, #id-jenis-harga', 'change', function() {
		changeHargaStok();
	})
	
	function changeHargaStok() {
		$barang_pilih_item = $('tbody.barang-pilih-detail');
		id_gudang = $('#id-gudang').val();
		id_jenis_harga = $('#id-jenis-harga').val();
		
		if (!$('#barang-pilih-tabel').is(':hidden')) {
			$barang_pilih_item.each(function(i, elm) {
				$elm = $(elm);
				$item = $elm.find('tr').eq(0);
				
				detail = JSON.parse( $item.find('.barang-pilih-item-detail').text() );
				
				harga = detail.list_harga[id_jenis_harga];
				
				stok = detail.list_stok[id_gudang];
				$item.find('.stok').val(stok);
				$item.find('.stok-text').text(format_ribuan(stok));
				$item.find('.harga-satuan').val(harga);
				$item.find('.harga-satuan-text').text(format_ribuan(harga));
				
				$item.find('.qty').trigger('keyup');
			})
		}
	}
	
	// Customer
	let $modal_customer = '';
	$(document).undelegate('.cari-customer', 'click').delegate('.cari-customer', 'click', function() 
	{
		$this = $(this);
		$modal_customer = jwdmodal({
			title: 'Pilih Customer',
			url: base_url + '/penjualan/getListCustomer',
			width: '950px',
			action :function () 
			{
				$panel = $modal_customer.find('.header-panel');
				$panel.append('<div class="px-4 py-3 d-grid"><button class="btn btn-outline-warning add-customer py-2"><i class="fas fa-plus me-2"></i>Add Customer</button></div><hr class="p-0 m-0"/>');
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
			$('#nama-customer').text(customer.nama_customer);
			$('#nama-customer').val(customer.nama_customer);
			$('#del-customer').show();
			$modal_customer.remove();
		});
	});
	
	$(document).undelegate('#del-customer', 'click').delegate('#del-customer', 'click', function() {
		$('#id-customer').val('');
		$('#nama-customer').text('Umum');
		$('#nama-customer').val('Umum');
		$(this).hide();
	});
	
	$(document).undelegate('.add-customer', 'click')
		.delegate('.add-customer', 'click', function() {
			$modal_customer.remove();
			$bootbox =  bootbox.dialog({
				title: 'Tambah Customer',
				message: '<div class="text-center text-secondary spinner"><div class="spinner-border"></div></div>',
				buttons: {
					cancel: {
						label: 'Cancel'
					},
					success: {
						label: 'Submit',
						className: 'btn-success submit',
						callback: function() 
						{
							$bootbox.find('.alert').remove();
							let $spinner = $('<i class="fas fa-circle-notch fa-spin me-2 fa-lg"></i>');
							$button_submit.prepend($spinner);
							$button.prop('disabled', true);
							
							form = $bootbox.find('form')[0];
							data = new FormData(form);
							data.append('submit', 'submit');
							
							$.ajax({
								type: 'POST',
								url: base_url + 'customer/add?ajax=1',
								data: data,
								processData: false,
								contentType: false,
								dataType: 'json',
								success: function (data) {
									$button.prop('disabled', false);
									$spinner.remove();

									if (data.status == 'ok') 
									{
										$('#id-customer').val(data.customer.id_customer);
										$('#nama-customer').text(data.customer.nama_customer);
										$bootbox.modal('hide');
										$('#del-customer').show();
									} else {
										show_alert('Error !!!', data.message, 'error');
									}
								},
								error: function (xhr) {
									$button.prop('disabled', false);
									$spinner.remove();
									show_alert('Error !!!', xhr.responseText, 'error');
									console.log(xhr.responseText);
								}
							})
							return false;
						}
					}
				}
			});
			$bootbox.find('.modal-dialog').css('max-width', '700px');
			var $button = $bootbox.find('button').prop('disabled', true);
			var $button_submit = $bootbox.find('button.submit');
			
			$.get(base_url + '/customer/add?ajax=1', function(html){
				$button.prop('disabled', false);
				$bootbox.find('.modal-body').empty().append(html);
				$('.select2').select2({theme: 'bootstrap-5', dropdownParent: $(".bootbox")});
			});
		});
	//-- Customer

	$('.diskon-nilai').keypress(function() {
		// $(this).css('width', this.value.length + 'ch'); 
	})
	
	// Right Panel
	$(document).undelegate('.min-jml-barang', 'click').delegate('.min-jml-barang', 'click', function(e) {
		
		e.stopPropagation();
		console.log('ee')
		jml = setInt($(this).next().val());
		$this = $(this)
		if (jml == 1) {
			$this.prop('disabled', true);
		} else {
			$this.next().val(jml - 1).trigger('keyup');
		}
		
	})
	
	$(document).undelegate('.plus-jml-barang', 'click').delegate('.plus-jml-barang', 'click', function(e) {
		e.stopPropagation();
		
		$this = $(this);
		$td = $this.parents('td').eq(0);
		$stok = $this.parents('tr').eq(0).find('.stok');
		$qty = $td.find('.qty');
		
		jml = setInt($qty.val());
		stok = setInt($stok.val());
		
		if (jml >= stok) {
			$this.prop('disabled', true);
			return false;
		}
		$qty.val(jml + 1).trigger('keyup');
		$td.find('.min-jml-barang').prop('disabled', false);
		
	})
	
	$(document).undelegate('.del-barang-pilih', 'click').delegate('.del-barang-pilih', 'click', function() {
		$list_barang = $('.tabel-barang-pilih');
		if (!$list_barang.is(':hidden')) {
			$list_barang.find('.del-item').trigger('click');
		}
		$('.total-text').text('0');
	})
	
	// Edit Harga
	//--- Pembayaran
	$(document).undelegate('.edit-item', 'click').delegate('.edit-item', 'click', function(e) {
		e.stopPropagation();
		$tbody = $(this).parents('tbody').eq(0);
		harga_satuan = $tbody.find('.harga-satuan').val();
		$bootbox =  bootbox.dialog({
			title: 'Edit Item',
			message: 
					'<div class="row">'+
						'<label class="col-sm-3 col-form-label">Harga</label>'+
						'<div class="col-sm-9">'+
							'<input inputmode="numeric" class="form-control harga-satuan number" type="text" name="harga_satuan" value="' + format_ribuan(harga_satuan) + '" required="required">'+
						'</div>'+
					'</div>',
			buttons: {
				cancel: {
					label: 'Cancel'
				},
				success: {
					label: 'Ubah',
					className: 'btn-success submit',
					callback: function() 
					{
						harga_satuan_new = $bootbox.find('.harga-satuan').val();
						$tbody.find('.harga-satuan').val(harga_satuan_new);
						$tbody.find('.harga-satuan-text').text(format_ribuan(harga_satuan_new));
						$tbody.find('.qty').trigger('keyup');
						
						return true;
					}
				}
			}
		});
		// $bootbox.find('.modal-body').html($form);
	});
	
	// Diskon Barang
	$(document).undelegate('.diskon-barang-jenis', 'change').delegate('.diskon-barang-jenis', 'change', function(e) 
	{
		$tbody = $(this).parents('tbody').eq(0);
		if ($(this).val() == '%') {
			width = '110px';
		} else {
			width = '135px';
		}
		$tbody.find('.diskon-nilai-container').css('width', width);
		$tbody.find('.diskon-barang-nilai').trigger('keyup');
	})
	$(document).undelegate('.plus-diskon-barang', 'click').delegate('.plus-diskon-barang', 'click', function(e) 
	{
		$input = $(this).prev();
		diskon_value = setInt($input.val()) + 1;
		$input.val( format_ribuan(diskon_value) );
		diskon_barang($input);
	});
	
	$(document).undelegate('.minus-diskon-barang', 'click').delegate('.minus-diskon-barang', 'click', function(e) 
	{
		$input = $(this).next();
		diskon_value = setInt($input.val()) - 1;
		$input.val( format_ribuan(diskon_value) );
		diskon_barang($input);
	});
	
	$(document).undelegate('.diskon-barang-nilai', 'keyup').delegate('.diskon-barang-nilai', 'keyup', function() 
	{
		diskon_barang($(this));
	});
	
	function diskon_barang($input) {
		
		$tbody = $input.parents('tbody').eq(0);
		harga_barang = setInt($tbody.find('.harga-barang-input').val());
		diskon_value = setInt($input.val());
		diskon_jenis = $tbody.find('.diskon-barang-jenis').val();

		simbol_persen = '';
		simbol_rp = '';
		prefix = '';

		$plus = $input.next();
		$minus = $input.prev();
		$plus.prop('disabled', false);
		$minus.prop('disabled', false);
		if (diskon_value) 
		{
			if (diskon_jenis == '%') {
				simbol_persen = '%';
				if (diskon_value > 100) {
					diskon_value = 100;
					$plus.prop('disabled', true);
				}
			} else {
				simbol_rp = 'Rp';
				if (diskon_value > harga_barang) {
					diskon_value = harga_barang;
					$plus.prop('disabled', true);
				}
				prefix = '-';
			}
			
			if (diskon_value <= 0) {
				prefix = '';
				simbol_persen = '';
				simbol_rp = '';
				diskon_value = 0;
				$minus.prop('disabled', true);
			}
		}
		$input.val(format_ribuan(diskon_value));
		$('#minus-diskon-barang').prop('disabled', false);
		$tbody.find('.diskon-barang-text').text(prefix + format_ribuan(diskon_value) + simbol_persen);
		$tbody.find('.diskon-barang-simbol-rp').text(simbol_rp);
		calculate_total();
	}
	
	// Delete Barang
	$(document).undelegate('.del-item', 'click').delegate('.del-item', 'click', function() {
		
		$tbody_all = $('.barang-pilih-detail');
		$tbody_current = $(this).parents('tbody').eq(0);
		$tbody_current.find('.del-diskon').click();

		if ($tbody_all.length > 1) {
			$tbody_current.remove();
		} else {
			$tbody_current.find('.qty').val(0).trigger('keyup');
			$('.tabel-barang-pilih').hide();
			$('.barang-pilih-empty').show();
			$('.btn-bayar').prop('disabled', true);
			$('.barang-pilih-form').hide();
		}
	});
	
	$(document).undelegate('.diskon-barang-jenis', 'keyup').delegate('.diskon-barang-jenis', 'change', function() 
	{
		$(this).parents('tr').eq(0).find('.diskon-barang').trigger('keyup');
	})
	
	$(document).undelegate('.diskon-barang', 'keyup').delegate('.diskon-barang', 'keyup', function() 
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
		
		$this = $(this);
		$tbody = $(this).parents('tbody').eq(0);
		harga = setInt($tbody.find('.harga-input').val());
		diskon = harga * diskon_value / 100;

		$tbody.find('.diskon-text').html(format_ribuan(diskon * -1));
		$tbody.find('.diskon-input').val(diskon * -1);
	});
	
	$(document).undelegate('.diskon-barang-container', 'click').delegate('.diskon-barang-container', 'click', function(e) {
		e.stopPropagation();
	})

	//-- Diskon Barang
	
	//Pembayaran
	$(document).undelegate('.jml-bayar', 'keyup').delegate('.jml-bayar', 'keyup', function() {
		$parent = $(this).parents('.form-bayar').eq(0);
		
		jml_tagihan = setInt($parent.find('.jml-tagihan').val());
		jml_bayar = setInt($(this).val());
		kembali = jml_bayar - jml_tagihan;
		if (kembali < 0) {
			kembali = 0;
		}
		$parent.find('.kembali').text(format_ribuan(kembali));
	});
	
	$(document).undelegate('.del-pembayaran', 'click').delegate('.del-pembayaran', 'click', function(e) 
	{
		$tr = $(this).parents('tr').eq(0);
		$tfoot = $tr.parent();
		$tr.remove();
		$list_tr = $tfoot.find('tr.row-bayar');
		$('.item-bayar').eq(0).trigger('keyup');
	});
	
	$(document).undelegate('.add-pembayaran', 'click').delegate('.add-pembayaran', 'click', function(e) 
	{
		$tr = $(this).parents('tr').eq(0);
		$tfoot = $tr.parent();
		$new_tr = $tr.clone();
		$new_tr.find('input').val('');
		$new_tr.find('td').last().html( '<button type="button" class="btn text-danger del-pembayaran"><i class="fas fa-times"></i></button>' );
		$new_tr.insertAfter($tfoot.find('tr.row-bayar').last());
		$('.flatpickr').flatpickr({
			enableTime: false,
			dateFormat: "d-m-Y",
			time_24hr: true
		});
	});
	
	$(document).undelegate('.item-bayar', 'keyup').delegate('.item-bayar', 'keyup', function(e) 
	{
		calculate_total();
	});
	
	//--- Pembayaran
	$(document).undelegate('.del-diskon', 'click').delegate('.del-diskon', 'click', function() {
		$this = $(this);
		$this.parents('tr').eq(0).hide();
		$tbody = $this.parents('tbody').eq(0);
		$tbody.find('.add-discount').prop('disabled', false);
		$tbody.find('.diskon-barang-nilai').val(0).trigger('keyup');
	});
	
	$(document).undelegate('.add-discount', 'click').delegate('.add-discount', 'click', function() {
		$this = $(this);
		$this.prop('disabled', true);
		$this.parents('tbody').eq(0).find('.diskon-row').show();
	});
	
	// Qty Keyup
	$(document).undelegate('.qty', 'keyup').delegate('.qty', 'keyup', function() {
		$this = $(this);
		$tr = $this.parents('tr').eq(0);
		$btn_min = $tr.find('.min-jml-barang');
		$btn_plus = $tr.find('.plus-jml-barang');
		stok = setInt( $tr.find('.stok').val() );
		qty = setInt($this.val());
		$btn_plus.prop('disabled', false);
		
		if (qty > stok) {
			this.value = stok;
			qty = stok;
		}
		
		if (qty == stok) {
			$btn_plus.prop('disabled', true);
		}
		
		if (qty > 0) {
			$btn_min.prop('disabled', false);
		} else {
			$btn_min.prop('disabled', true);
		}
		
		$tbody = $(this).parents('tbody').eq(0);
		harga_satuan = setInt($tbody.find('.harga-satuan').val());
		harga = qty * harga_satuan;
		$tbody.find('.harga-barang-text').text( format_ribuan(harga) );
		$tbody.find('.harga-barang-input').val(harga);
		calculate_total();
		$tbody.find('.diskon-barang').trigger('keyup');
		this.value = format_ribuan(this.value);
	});
	
	// Penyesuaian

	$(document).undelegate('#penyesuaian-text-container', 'click').delegate('#penyesuaian-text-container', 'click', function(e) {
		e.stopPropagation();
		$('#penyesuaian-input-container').show();
		$('#penyesuaian-text-container').hide();
		hideDiskon();
		hidePajak();
	});
		
	$(document).undelegate('#penyesuaian-nilai', 'keyup').delegate('#penyesuaian-nilai', 'keyup', function(e) {
		if (this.value == '-') {
			this.value = 0;
		}

		nilai_penyesuaian = setInt(this.value);
		if ($('#penyesuaian-operator').val() == 'minus') {
			if (nilai_penyesuaian > 0) {
				nilai_penyesuaian = nilai_penyesuaian * -1;
				this.value = format_ribuan(nilai_penyesuaian);
			}
		} else {
			if (nilai_penyesuaian <= 0) {
				nilai_penyesuaian = nilai_penyesuaian * -1;
				this.value = format_ribuan(nilai_penyesuaian);
			}
		}
		
		sub_total = setInt($('#subtotal-input').val());
		
		operator_diskon = $('#diskon-total-jenis').val();
		diskon_nilai = $('#diskon-total-nilai').val();
		if (operator_diskon == '%') {
			diskon_total = Math.round(sub_total * diskon_nilai / 100);
		} else {
			diskon_total = diskon_nilai;
		}

		if ( nilai_penyesuaian + ( sub_total - diskon_total ) < 0 ) {
			this.value = format_ribuan( (sub_total - diskon_total) * -1); 
		}
	});
	
	$(document).undelegate('#penyesuaian-nilai, #diskon-total-nilai', 'keyup').delegate('#penyesuaian-nilai, #diskon-total-nilai', 'keyup', function(e) {
		calculate_total();
		if (e.key) {
			if (e.key.toLowerCase() == 'enter') {
				e.preventDefault();
				$(document).trigger('click');
			}
		}
	})
	
	$(document).click(function() {
		hideDiskon();
		hidePenyesuaian();
		hidePajak();
	});
	
	function hideDiskon() {
		$diskon_input_container = $('#diskon-total-input-container');
		$diskon_text_container = $('#diskon-total-text-container');
		$diskon_text = $('#diskon-total-text');
		
		if ( $diskon_text_container.is(':hidden') && !$diskon_input_container.is(':hidden') ) {
			$diskon_input_container.hide();
			$diskon_text_container.show();
			
			persen = '';
			prefix = '';
			jenis = $('#diskon-total-jenis').val();
			if (jenis == '%') {
				if ( $('#diskon-total-nilai').val() > 0) {
					persen = '%';
				}
				$('#diskon-total-simbol-rp').html('');
			} else {
				if ( $('#diskon-total-nilai').val() > 0) {
					prefix = '-';
					$('#diskon-total-simbol-rp').html('Rp');
				} else {
					$('#diskon-total-simbol-rp').html('');
				}
			}
			$diskon_text.html( prefix + format_ribuan($('#diskon-total-nilai').val()) + persen);
		}
	}
	
	function hidePenyesuaian() {
		$penyesuaian_input_container = $('#penyesuaian-input-container');
		$penyesuaian_text_container = $('#penyesuaian-text-container');
		$penyesuaian_text = $('#penyesuaian-text');
		
		if ( $penyesuaian_text_container.is(':hidden') && !$penyesuaian_input_container.is(':hidden') ) {
			$penyesuaian_input_container.hide();
			$penyesuaian_text_container.show();
			
			prefix = '';
			jenis = $('#penyesuaian-operator').val();
			if (jenis == 'minus') {
				if ($('#penyesuaian-nilai').val() > 0) {
					prefix = '-';
				}
			}
			if ($('#penyesuaian-nilai').val() == 0) {
				$('#penyesuaian-simbol-rp').html('');
			} else {
				$('#penyesuaian-simbol-rp').html('Rp');
			}
			$penyesuaian_text.html( prefix + format_ribuan($('#penyesuaian-nilai').val()));
		}
	}
	
	function hidePajak() {
		$pajak_input_container = $('#pajak-input-container');
		$pajak_text_container = $('#pajak-text-container');
		$pajak_text = $('#pajak-text');
		
		if ( $pajak_text_container.is(':hidden') && !$pajak_input_container.is(':hidden') ) {
			$pajak_input_container.hide();
			$pajak_text_container.show();
			$pajak_text.html( format_ribuan($('#pajak-nilai').val()) + '%');
		}
	}
	
	// Document Click
	$(document).undelegate('#diskon-total-container, #penyesuaian-container, #pajak-container', 'click').delegate('#diskon-total-container, #penyesuaian-container, #pajak-container', 'click', function(e) {
		e.stopPropagation();
	})
	
	// Diskon
	$(document).undelegate('#diskon-total-text-container', 'click').delegate('#diskon-total-text-container', 'click', function(e) {
		e.stopPropagation();
		$('#diskon-total-input-container').show();
		$('#diskon-total-text-container').hide();
		hidePenyesuaian();
		hidePajak();
	});
	
	$(document).undelegate('#diskon-total-jenis', 'change').delegate('#diskon-total-jenis', 'change', function() {
		$('#diskon-total-nilai').trigger('keyup');
	})
	
	$(document).undelegate('#diskon-total-plus', 'click').delegate('#diskon-total-plus', 'click', function(e) {
		$diskon_nilai = $(this).prev();
		diskon = setInt($diskon_nilai.val());
		
		if ($('#diskon-total-jenis').val() == '%') {
			if (diskon == 100) {
				return false;
			}
		}
		$diskon_nilai.val(format_ribuan(diskon + 1));
		$('#diskon-total-min').prop('disabled', false);
		calculate_total();
	});
	
	$(document).undelegate('#diskon-total-min', 'click').delegate('#diskon-total-min', 'click', function(e) {
		$next = $(this).next();
		diskon = setInt($next.val());
		if (diskon == 0) {
			$(this).prop('disabled', true);
			return false;
		}
		$next.val(format_ribuan(diskon - 1));
		calculate_total();
	});
	
	$(document).undelegate('#diskon-total-nilai', 'keyup').delegate('#diskon-total-nilai', 'keyup', function(e) {
		
		let value = parseInt(this.value.replace(/\D/g, ''));
		this.value = format_ribuan(value);
			
		if ($('#diskon-total-jenis').val() == '%') {
			$this = $(this);
			
			if (value >= 100) {
				$this.val(100);
				return false;
			}
		}
		
		calculate_total();
	});
	
	// -- Diskon
	
	// Pajak
	$(document).undelegate('#pajak-text-container', 'click').delegate('#pajak-text-container', 'click', function(e) {
		e.stopPropagation();
		$('#pajak-input-container').show();
		$('#pajak-text-container').hide();
		hideDiskon();
		hidePenyesuaian();
	});
	
	$(document).undelegate('#pajak-min', 'click').delegate('#pajak-min', 'click', function(e) {
		$next = $(this).next();
		let pajak = setInt($next.val());
		if (pajak == 0) {
			$(this).prop('disabled', true);
			return false;
		}
		$next.val(format_ribuan(pajak - 1));
		calculate_total();
	});
	
	$(document).undelegate('#pajak-plus', 'click').delegate('#pajak-plus', 'click', function(e) {
		$pajak_nilai = $(this).prev().prev();
		let pajak = setInt($pajak_nilai.val());
		
		if (pajak >= 100) {
			return false;
		}
	
		$pajak_nilai.val(format_ribuan(pajak + 1));
		$('#pajak-min').prop('disabled', false);
		calculate_total();
	});
	
	$(document).undelegate('#pajak-nilai', 'keyup').delegate('#pajak-nilai', 'keyup', function(e) {
		$this = $(this);
		let value = parseInt($this.val().replace(/\D/g, ''));
		this.value = value;

		if (value >= 100) {
			$this.val(100);
		}
		
		calculate_total();
	});
	// -- Pajak
	
	$(document).undelegate('#penyesuaian-operator', 'change').delegate('#penyesuaian-operator', 'change', function() 
	{
		$('#penyesuaian-nilai').trigger('keyup');
		calculate_total();
	});
	//-- Diskon total
		
	$(document).undelegate('.btn-bayar', 'click').delegate('.btn-bayar', 'click', function() 
	{
		$this = $(this);
		total_nilai = $('#total-input').val();
		$form = $('.form-bayar').clone().show();
		$form.find('.jml-tagihan, .jml-bayar').val(format_ribuan(total_nilai));
		$bootbox =  bootbox.dialog({
			title: 'Bayar',
			message: 'bayar',
			buttons: {
				cancel: {
					label: 'Close',
					callback: function()
					{
						location.reload(); 	
					}
				},
				success: {
					label: 'Bayar',
					className: 'btn-success submit',
					callback: function() 
					{
					
						$form_list_barang = $('#barang-pilih-form');
						// $form_list_barang.find('.form-bayar-inserted').remove();
						
						$form_bayar = $bootbox.find('.form-bayar').clone().hide();
						// $form_bayar.removeAttr('class');
						// $form_bayar.addClass('form-bayar-inserted');
						// $form_list_barang.append($form_bayar);
						
						data = $form_list_barang.serialize();
						data += '&' + $bootbox.find('.form-bayar').serialize();
					
						$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
						$btn_all = $bootbox.find('button');
						$btn_submit = $bootbox.find('.submit');
						
						$btn_all.prop('disabled', true);
						$btn_submit.prepend($spinner);
						$.ajax({
							url: base_url + 'pos-kasir/ajaxSaveData',
							data: data,
							method: 'post',
							success: function(data) {
								$spinner.remove();
								$btn_all.prop('disabled', false);
								data = JSON.parse(data);
								if (data.status == 'ok') {
									$btn_submit.prop('disabled', false);
								
									let html_content = '<div class="d-flex flex-column align-items-start">' + 
											'<button class="btn btn-success mb-2 print-nota" data-id="' + data.id_penjualan + '"><i class="fas fa-print me-2"></i>Print Nota</button>' + 
											'<button class="btn btn-danger mb-2 download-invoice-pdf" data-id="' + data.id_penjualan + '" data-filename="Invoice-' + data.penjualan.no_invoice + '"><i class="fas fa-file-pdf me-2"></i>Download Invoice</button>' + 
											'<div class="d-flex input-group" style="width:390px">' + 
												'<i class="fas fa-paper-plane me-2"></i>Nomor Invoice&nbsp;&nbsp;&nbsp;<input type="text" class="form-control" name="txtinvoice" value="' + data.no_invoice + '"/>' +
											/* '<div class="d-flex input-group" style="width:390px">' + 
												'<input type="email" class="form-control" name="email" value="' + data.customer.email + '"/>' + 
												'<button class="btn btn-primary text-nowrap kirim-email-invoice" data-id="' + data.id_penjualan + '"><i class="fas fa-paper-plane me-2"></i>Email Invoice</button>' + */ 
											'</div>' +
										'</div>';
									$bootbox.find('.modal-body').html(html_content);
								}								
								$bootbox.find('.submit').hide();
							}, error: function(xhr) {
								$spinner.remove();
								$btn_all.prop('disabled', false);
								console.log(xhr);
							}
						})
						return false;
					}
					
				}
			}
		});
		$bootbox.find('.modal-body').html($form);
	});
	
	$(document).undelegate('.print-nota', 'click').delegate('.print-nota', 'click', function(e) {
		e.preventDefault();
		url = base_url + 'penjualan/printNota?id=' + $(this).attr('data-id');
		is_mobile = /android|mobile/ig.test(navigator.userAgent);
		if (is_mobile) {
			let html_container = "print://escpos.org/escpos/bt/print?srcTp=uri&srcObj=html&src='data:text/html,";			
			$.ajax({
				url: url,
				success: function(html) {
					html_container += html
					window.location.href = html_container;
					console.log(html);				
				}, error: function() {
					bootbox.alert('Ajax Error, cek console browser');
				}
				
			}
			)			
		} else {
			window.open(url, top = 500, left = 500, width = 600, height = 600, menubar = 'no', status = 'no', titlebar = 'no'); 
		}
		return false;
	});
	
	$(document).undelegate('.download-invoice-pdf', 'click').delegate('.download-invoice-pdf', 'click', function(e) 
	{
		e.preventDefault();
		$this = $(this);
		url_pdf = base_url + 'penjualan/invoicePdf?id=' + $this.attr('data-id');
		filename = $this.attr('data-filename').replace('/','_').replace('\\', '_');
		$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
		$this.prepend($spinner);
		$this.prop('disabled', true);
		
		fetch(url_pdf)
		  .then(resp => resp.blob())
		  .then(blob => {
				saveAs(blob, filename + '.pdf');
				$spinner.remove();
				$this.prop('disabled', false);
		  })
		.catch(() => alert('Ajax Error'));
	})
	
	$(document).undelegate('.kirim-email-invoice', 'click').delegate('.kirim-email-invoice', 'click', function(e){
		e.preventDefault();
		$this = $(this);
		email = $this.prev().val();
		id = $this.attr('data-id');		
		$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
		
		$this.prop('disabled', true);
		$this.prepend($spinner);

		$.ajax({
			url: base_url + 'penjualan/invoicePdf?email=' + email + '&id=' + id,
			method: 'get',
			success: function(data) {
				$spinner.remove();
				$this.prop('disabled', false);
				data = JSON.parse(data);

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
						html: '<div class="toast-content d-flex"><i class="far fa-check-circle me-2 mt-1"></i>' + parse_message(data.message) + '</div>'
					})
				} else {
					bootbox.alert('<div class="d-flex my-2"><span class="text-danger"><i class="fas fa-times-circle me-3" style="font-size:20px"></i></span>' + parse_message(data.message) + '</div>');
				}
				
			}, error: function(xhr) {
				$spinner.remove();
				$this.prop('disabled', false);
				Swal.fire({
					text: 'Ajax Error, cek console browser',
					title: 'AJAX Error',
					icon: 'error',
					showCloseButton: true,
					confirmButtonText: 'OK'
				})
				console.log(xhr);
			}
		})		
	});
	
	function calculate_total() 
	{
		$harga_barang = $('.right-panel-body').find('.harga-barang-input');

		subtotal = 0;
		$harga_barang.each(function(i, elm) 
		{
			value = $(elm).val();
			subtotal += setInt( value );
		});
		
		// Diskon barang
		$('.diskon-barang-nilai').each(function(i, elm) {
			$elm = $(elm);
			diskon_nilai = setInt($elm.val());
			if (diskon_nilai) {
				$tbody = $elm.parents('tbody').eq(0);
				jenis = $tbody.find('.diskon-barang-jenis').val();
				nilai = setInt($tbody.find('.harga-barang-input').val());
				if (jenis == '%') {
					diskon_nilai = diskon_nilai / 100 * nilai;
				}
				subtotal -= diskon_nilai;
			}
		});
		
		$('#subtotal-text').text(format_ribuan(subtotal));
		$('#subtotal-input').val(subtotal);
		
		// Diskon
		let diskon_total_jenis = $('#diskon-total-jenis').val();
		let diskon_total = setInt( $('#diskon-total-nilai').val(), 10 );
		
		if (diskon_total) {
			if (diskon_total_jenis == '%') {
				if (diskon_total >= 100) {
					diskon_total = 100;
				}
				jumlah_diskon = Math.round(subtotal * diskon_total / 100);
			} else {
				jumlah_diskon = diskon_total;
			}
			subtotal = subtotal - jumlah_diskon;
		}

		// Penyesuaian
		let penyesuaian = setInt( $('#penyesuaian-nilai').val());
		let neto = subtotal + penyesuaian;

		// Pajak
		tarif_pajak = $('#pajak-nilai').val();
		if (tarif_pajak) {
			pajak = Math.round( neto * parseInt(tarif_pajak) / 100 );
			neto = neto + pajak;
		}

		$('.total-text').text(format_ribuan(neto));
		$('#total-input').val(neto);
		$('.jml-tagihan').val(neto);
		
		$('.jml-bayar').trigger('keyup');
		
		// Bayar
		let $item_bayar = $('.item-bayar');
		let total_bayar = 0;
		$item_bayar.each(function(i, elm) {
			bayar = setInt( $(elm).val() );
			total_bayar += bayar;
		})
		
		$('#total-bayar').text(format_ribuan(total_bayar));
		
		if (total_bayar < neto) {
			$('#kembali-row').hide();
			$('#kurang-bayar-row').show();
			$('#kurang-bayar-nilai').text( format_ribuan(neto - total_bayar) );
		} else {
			$('#kurang-bayar-row').hide();
			$('#kembali-row').show();
			$('#kembali-nilai').text( format_ribuan(total_bayar - neto) );
		}
		// $('.item-bayar').eq(0).trigger('keyup');
		// $('.kurang-bayar').val(format_ribuan(neto));
		
	}
	
	// Pilih Barang - Left Panel
	$(document).undelegate('tr', 'click').delegate('tr', 'click', function() {
				
		jenis = $(this).parents('table').eq(0).attr('data-tabel-jenis');
		if (jenis != 'tabel-barang')
			return;
		
		if ($(this).parents('table').eq(0).attr('id') != 'tabel-data')
			return;
	
		detail_barang = $(this).find('.detail-barang').text();
		$detail_barang = JSON.parse(detail_barang);
		
		if ( parseInt($detail_barang.stok) == 0 ) {
			bootbox.alert('Stok barang kosong');
			return;
		}
		
		suara = new Audio(base_url + 'public/files/audio/beep.wav');
		suara.play();
		
		$('.barang-pilih-empty').hide();
		$('.btn-bayar').prop('disabled', false);
		$('#barang-pilih-form').show();
		$list_barang = $('.tabel-barang-pilih');
			
		$id_barang = $list_barang.find('.id-barang[value="' + $detail_barang.id_barang + '"]');
		if ($id_barang.length) 
		{
			$row = $id_barang.parents('tbody').eq(0);
			$qty = $row.find('.qty');
			
			if ($list_barang.is(':hidden')) {
				$qty.val(1);
			} else {
				jml = setInt($qty.val());
				$qty.val(jml + 1);
			}
			$qty.trigger('keyup');
			
		} else {
			if ($list_barang.is(':hidden')) {
				$row = $list_barang.find('tbody.barang-pilih-detail').eq(0);
				$row.find('.diskon-row').hide();
			} else {
				$row = $list_barang.find('tbody.barang-pilih-detail').eq(0).clone();
				$row.hide();
				$row.insertBefore($('#subtotal-tbody'));
			}
			
			$row.find('.id-barang').val($detail_barang.id_barang);
			$row.find('.nama-barang').html($detail_barang.nama_barang);
			$row.find('.stok').val($detail_barang.stok);
			$row.find('.stok-text').text($detail_barang.stok);
			$row.find('.barang-pilih-item-detail').html(detail_barang);
			$row.find('.harga-satuan').val($detail_barang.harga);
			$row.find('.harga-pokok').val($detail_barang.harga_pokok);
			$row.find('.harga-satuan-text').html(format_ribuan($detail_barang.harga));
			$row.find('.satuan').val($detail_barang.satuan);
			$row.find('.qty').val(1).trigger('keyup');
			$row.find('.diskon-row').hide();
			$row.find('.diskon-barang-nilai').val(0);
			$row.find('.add-discount').prop('disabled', false);
		}
			
		$row.find('.diskon-barang-nilai').trigger('keyup')
		$row.show();
		$list_barang.show();
		osRightPanel = OverlayScrollbars( $('.right-panel-body'), {scrollbars : {autoHide: 'leave', autoHideDelay: 100}} );
		
		if ($('#toast-alert').length == 0) {
			html_toast = '<div class="toast-container position-fixed bottom-0 end-0 p-3">' + 
					'<div id="toast-alert" class="toast align-items-center text-bg-success border-0 top-0 end-0 px-2 py-1" data-bs-delay="1000" role="alert" aria-live="assertive" aria-atomic="true">' +
						'<div class="d-flex">' +
							'<div class="toast-body"><i class="far fa-check-circle me-2"></i>1 Item ditambahkan</div>'+
						'</div>' +
					'</div>' +
					'</div>';
					
					
			$('body').append(html_toast);
		}
		
		if (toastTimer) {
			clearTimeout(toastTimer);
		}
		$toast = $('#toast-alert');
		$toast.stop(true, true).fadeOut('fast', function() {
			
			$toast.stop(true, true).fadeIn('fast', function() {
				toastTimer = setTimeout(() => {
					
					$toast.stop(true, true).fadeOut('fast');
				}, 1000)
			});
		});
	})
})