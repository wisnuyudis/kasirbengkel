/**
* Written by: Agus Prawoto Hadi
* Year		: 2021-2022
* Website	: jagowebdev.com
*/

jQuery(document).ready(function () {
	
	let dataTables = '';
	
	if ($('#table-data').length) {
		const column = $.parseJSON($('#dataTables-column').html());
		const url = $('#dataTables-url').text();
		
		const settings = {
			"processing": true,
			"serverSide": true,
			"scrollX": true,
			"ajax": {
				"url": url,
				"type": "POST",
				"dataSrc": function ( json ) {
					if (json.recordsTotal > 0) {
						$('.btn-export').removeAttr('disabled');
					} else {
						$('.btn-export').attr('disabled', 'disabled');
					}
			
					return json.data;
				}
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
		
		dataTables =  $('#table-data').DataTable( settings );
	}
	
	if ( $('.jml-digit').length > 0 ) {
		$('.jml-digit').text($('.barcode').val().length)
	}
	
	$('.barcode').keyup(function() {
		value = this.value.replace(/\D/g, '');
		this.value = value;
		if (value.length > 13) {
			this.value = value.substr(0, 13);
		}
		$('.jml-digit').text(this.value.length)
		
	});
	
	$('.generate-barcode').click(function() 
	{
		$this = $(this).prop('disabled', true);
		$input = $this.prev().prop('disabled', true);
		$parent = $this.parent().parent();
		$spinner = $parent.find('.spinner').show();
		
		$.ajax({
			url: base_url + 'barang/ajaxGenerateBarcodeNumber',
			success: function(data) {
				console.log(data);
				$this.prop('disabled', false);
				$input.prop('disabled', false).val(data).trigger('keyup');
				$spinner.hide();
			}, error: function() {
				
			}
		})
	})
	
	$('.increment').click(function() {
		value = setInt($(this).prev().val());
		$(this).prev().val(value + 1).trigger('keyup');
	})
	
	$('.decrement').click(function() {
		value = setInt($(this).next().val());
		if (value > 0) {
			$(this).next().val(value - 1).trigger('keyup');
		}
	})
	
	$('.stok').keyup(function() {
		adjusment = this.value;
		if (!adjusment) {
			adjusment = '0';
		}
		adjusment = setInt(adjusment);
		
		$parent = $(this).parents('.stok-number').eq(0);
		operator = $parent.find('.operator').val();
		stok_awal = setInt($parent.find('.stok-awal').text());
		stok_akhir = operator == 'plus' ? stok_awal + adjusment : stok_awal - adjusment;
		
		sign = operator == 'plus' ? '+' : '-';
		$parent.find('.stok-adjusment').text(sign + adjusment.toString())
		$parent.find('.stok-akhir').text(stok_akhir)
		this.value = format_ribuan(adjusment);
		$parent.find('input[name="adjusment[]"]').val(sign + adjusment.toString());
	});
	
	$('.operator').change(function() {
		$parent = $(this).parents('.stok-number').eq(0);
		$parent.find('.stok').trigger('keyup');
	})
	
	$('.number').keyup(function() {
		number = this.value;
		if (!number) {
			number = '0';
		}
		number = setInt(number);
		this.value = format_ribuan(number);
	})
	
	$('.harga-pokok').keyup(function() {
		$parent = $(this).parent();
		harga_pokok_awal = setInt($parent.find('.harga-pokok-awal').text());
		harga_pokok_akhir = setInt(this.value);
		adjusment = harga_pokok_akhir - harga_pokok_awal;
		$parent.find('.adjusment-harga-pokok').text(format_ribuan(adjusment));
		$parent.find('input[name="adjusment_harga_pokok"]').val(adjusment);
	})
	
	$('.harga-jual').keyup(function() {

		$parent = $(this).parent().parent();
		harga_jual_awal = setInt($parent.find('.harga-jual-awal').text());
		harga_jual_akhir = setInt(this.value);
		adjusment = harga_jual_akhir - harga_jual_awal;
		console.log(adjusment);
		$parent.find('.adjusment-harga-jual').text(format_ribuan(adjusment));
	})
	
	$('#table-data').delegate('.btn-delete', 'click', function(e) {
		e.preventDefault();
		id = $(this).attr('data-id');
		$bootbox = bootbox.confirm({
			message: $(this).attr('data-delete-title'),
			callback: function(confirmed) {
				if (confirmed) {
					$button = $bootbox.find('button');
					$button.attr('disabled', 'disabled');
					$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
					$spinner.prependTo($bootbox.find('.bootbox-accept'));
					$.ajax({
						type: 'POST',
						url: current_url + '/ajaxDeleteData',
						data: 'id=' + id,
						dataType: 'json',
						success: function (data) {
							$bootbox.modal('hide');
							$spinner.remove();
							$button.removeAttr('disabled');
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
								show_alert('Error !!!', data.message, 'error');
							}
						},
						error: function (xhr) {
							$spinner.remove();
							$button.removeAttr('disabled');
							show_alert('Error !!!', xhr.responseText, 'error');
							console.log(xhr.responseText);
						}
					})
					return false;
				}
			},
			centerVertical: true
		});
	})
		
	function showForm(type='add', id = '') {
		$bootbox =  bootbox.dialog({
			title: 'Edit Data',
			message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
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
						$button_submit.prepend('<i class="fas fa-circle-notch fa-spin me-2 fa-lg"></i>');
						$button.prop('disabled', true);
						
						form = $bootbox.find('form')[0];
						$.ajax({
							type: 'POST',
							url: current_url + '/ajaxUpdateData',
							data: new FormData(form),
							processData: false,
							contentType: false,
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
										html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Data berhasil disimpan</div>'
									})
									if (type == 'edit') {
										dataTables.draw(false);
									} else {
										dataTables.draw();
									}
								} else {
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
				}
			}
		});
		
		var $button = $bootbox.find('button').prop('disabled', true);
		var $button_submit = $bootbox.find('button.submit');
		
		$.get(current_url + '/ajaxGetFormData?id=' + id, function(html){
			$button.prop('disabled', false);
			$bootbox.find('.modal-body').empty().append(html);
		});
	};
	
	$('#btn-excel').click(function() {
		$this = $(this);
		$this.prop('disabled', true);
		$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
		$spinner.prependTo($this);
		
		filename = 'Daftar Barang - ' + format_date('dd-mm-yyyy') + '.xlsx';
		url = base_url + 'barang/ajaxExportExcel';
		fetch(url)
		  .then(resp => resp.blob())
		  .then(blob => {
				$this.prop('disabled', false);
				$spinner.remove();
				saveAs(blob, filename);
		  })
		.catch((xhr) => {
			$this.prop('disabled', false);
			$spinner.remove();
			console.log(xhr);
			alert('Ajax Error')
			
		});
	})
	
	$('#btn-pdf').click(function() {
		$this = $(this);
		$this.prop('disabled', true);
		$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
		$spinner.prependTo($this);
				
		filename = 'Daftar Barang - ' + format_date('dd-mm-yyyy') + '.pdf';
		url = base_url + 'barang/ajaxExportPdf?ajax=true';
		fetch(url)
		  .then(resp => resp.blob())
		  .then(blob => {
				$this.prop('disabled', false);
				$spinner.remove();
				saveAs(blob, filename);
		  })
		.catch((xhr) => {
			$this.prop('disabled', false);
			$spinner.remove();
			console.log(xhr);
			alert('Ajax Error')
			
		});
	})
	
	$('#btn-send-email').click(function() {
		$bootbox =  bootbox.dialog({
			title: 'Kirim Email',
			message: '<form method="post" class="px-2">' +
				'<div class="row mb-3">' +
					'<label class="col-sm-3 col-form-label">Email</label>' +
					'<div class="col-sm-8">' +
						'<input class="form-control" name="email" id="email-address" value="daftar_barang@yopmail.com"/>' +
					'</div>' +
				'</div>'+
				'<div class="row mb-3">' +
					'<label class="col-sm-3 col-form-label">Format File</label>' +
					'<div class="col-sm-8">' +
						'<select class="form-select" name="fromat_file" id="format-file"><option value="excel">Excel</option><option value="pdf">PDF</option></select>' +
					'</div>' +
				'</div>'+
			'</form>',
			buttons: {
				cancel: {
					label: 'Cancel'
				},
				success: {
					label: 'Submit',
					className: 'btn-success submit',
					callback: function() 
					{
						var $button = $bootbox.find('button').prop('disabled', true);
						var $button_submit = $bootbox.find('button.submit');
						
						$bootbox.find('.alert').remove();
						$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
						$button_submit.prepend($spinner);
						$button.prop('disabled', true);
						
						$.ajax({
							type: 'GET',
							url: current_url + '/ajaxSendEmail?email=' + $('#email-address').val() + '&ajax=true&file=true&file_format=' + $('#format-file').val(),
							dataType: 'text',
							success: function (data) {
								data = $.parseJSON(data);
								console.log(data);
								$spinner.remove();
								$button.prop('disabled', false);
								
								if (data.status == 'ok') 
								{
									$bootbox.modal('hide');
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
										html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Email berhasil dikirim</div>'
									})
								} else {
									Swal.fire({
										title: 'Error !!!',
										html: data.message,
										icon: 'error',
										showCloseButton: true,
										confirmButtonText: 'OK'
									})
								}
							},
							error: function (xhr) {
								console.log(xhr.responseText);
								$spinner.remove();
								$button.prop('disabled', false);
								Swal.fire({
									title: 'Error !!!',
									html: xhr.responseText,
									icon: 'error',
									showCloseButton: true,
									confirmButtonText: 'OK'
								})
							}
						})
						return false;
					}
				}
			}
		});
	})
});