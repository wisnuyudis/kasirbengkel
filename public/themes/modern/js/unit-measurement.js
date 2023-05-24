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
		
		dataTables =  $('#table-data').DataTable( settings );
	}
		
	$('body').delegate('.btn-edit', 'click', function(e) {
		e.preventDefault();
		showForm('edit', $(this).attr('data-id'))
	})
	
	$('body').delegate('.btn-add', 'click', function(e) {
		e.preventDefault();
		showForm();
	})
	
	$('#table-data').delegate('.btn-delete', 'click', function(e) {
		e.preventDefault();
		id = $(this).attr('data-id');
		$bootbox = bootbox.confirm({
			message: $(this).attr('data-delete-title'),
			callback: function(confirmed) {
				var $button = $bootbox.find('button').prop('disabled', true);
				var $button_submit = $bootbox.find('button.bootbox-accept');
				if (confirmed) {
					$spinner = $('<span class="spinner-border spinner-border-sm me-2"></span>');
					$spinner.prependTo($button_submit);
					$.ajax({
						type: 'POST',
						url: current_url + '/ajaxDeleteData',
						data: 'id=' + id,
						dataType: 'json',
						success: function (data) {
							$button.prop('disabled', false);
							$spinner.remove();
							
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
								show_alert('Error !!!', data.message, 'error');
							}
							return false;
						},
						error: function (xhr) {
							$bootbox.modal('hide');
							$button.prop('disabled', false);
							$spinner.remove();
							show_alert('Error !!!', xhr.responseText, 'error');
							console.log(xhr.responseText);
						}
					})
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
									
									$bootbox.modal('hide');
								} else {
									show_alert('Error !!!', data.message, 'error');
									$button_submit.find('i').remove();
									$button.prop('disabled', false);
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
});