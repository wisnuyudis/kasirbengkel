function show_form_barang(detail) 
{
	$('.btn-submit').prop('disabled', true);
	$('.barang-pilih-empty').hide();
	$spinner = $('<div class="d-flex justify-content-center text-secondary"><div class="spinner-border" role="status"></div>');
	$container = $('.right-panel-body').empty();
	$container.append($spinner);
	
	$(this).parents('.left-panel').find('.btn-close-panel').trigger('click');
	
	let url_ajax = base_url + 'barang/edit-stok?mobile=true&id=' + detail['id_barang'];
	let url = base_url + 'barang-mobile/edit?id=' + detail['id_barang'];
	history.pushState( url,'',url);
	$.get(url_ajax, function (data) 
	{
		data = data.replaceAll('col-sm-5', 'col-sm-9');
		data = data.replaceAll('col-xl-2', 'col-xl-3');
		
		$spinner.remove();
		$html = $('<div>');
		$html.append(data);
		$html.find('.input-group').addClass('input-group-counter');
		$content = $html.find('.card-body').hide();
		$container.empty().append($content);
		
		$container.find('.card-body').addClass('p-0');
		$container.find('input[type="submit"]').hide();
		$content.fadeIn('fast');
		
		$buttons = $('.right-panel-footer').find('button');
		$buttons.prop('disabled', false);
		
		$html.find('[data-type="dynamic-resource-head"]').appendTo($('head'));
		
		$('.btn-submit').prop('disabled', false);
		if (osRightPanel) {
			osRightPanel.destroy();
		}
				
		osRightPanel = OverlayScrollbars( $('.right-panel-body'), {scrollbars : {autoHide: 'leave', autoHideDelay: 100}} );
	})
}
$(document).ready(function() {
	
	$(document).undelegate('.btn-submit', 'click').delegate('.btn-submit', 'click', function(e) 
	{
		e.preventDefault();
		$button = $(this);
		$spinner = $('<div class="spinner-border spinner-border-sm me-2"></div>');
		$button.prepend($spinner);
		$button.prop('disabled', true);
		
		$.ajax({
			url: base_url + 'barang/edit-stok?mobile=true',
			method: 'post',
			data: $('.right-panel-body').find('form').serialize() + '&submit=submit',
			success: function(data) {
				console.log(data);
				data = JSON.parse(data);
				$spinner.remove();
				$button.prop('disabled', false);
				
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
				$button.prop('disabled', false);
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
	})
	
	// Pilih Barang - Left Panel
	let toastTimer = '';
	let $toast = '';
	
	
	$(document).undelegate('tr', 'click').delegate('tr', 'click', function() {
		
		jenis = $(this).parents('table').eq(0).attr('data-tabel-jenis');
		if (jenis != 'tabel-barang-list')
			return;
		
		if ($(this).parents('table').eq(0).attr('id') != 'tabel-data')
			return;
	
		detail = JSON.parse($(this).find('.barang-detail').text());
		$btn_close_panel = $(this).parents('.left-panel').eq(0).find('.btn-close-panel');
		if (!$btn_close_panel.is(':hidden')) {
			$btn_close_panel.trigger('click');
		}
			
		show_form_barang(detail);	
	})
})