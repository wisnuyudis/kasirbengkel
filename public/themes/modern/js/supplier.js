/**
* Written by: Agus Prawoto Hadi
* Year		: 2021
* Website	: jagowebdev.com
*/

jQuery(document).ready(function () {
	
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
	
	if ($('.select2').length > 0 ) {
		$(".select2").select2({
			theme: "bootstrap-5"
		});
	}
	
	$spinner = $('<div class="spinner-border spinner-border-md" role="status" style="width: 1.5rem; height: 1.5rem; position:absolute; right: -15px; top:5px"></div>');
	
	function generate_options(json) {
		options = '';
		$.each(json, function(i, v) {
			options += '<option value="' + i + '">' + v + '</option>';
		})
		
		return options;
	}
	
	$('.tujuan').change(function(){
		if (this.value == 1) {
			$('.tujuan-detail').show();
		} else {
			$('.tujuan-detail').hide();
		}
	})

	function set_options($wilayah, url) 
	{
		$spinner.insertAfter($wilayah);
		$.getJSON(url, function(data) 
		{
			new_options = generate_options(data);
			$wilayah.each (function(i, elm) 
			{
				$elm = $(elm);
				teks = '-- Pilih Kelurahan --';
				if ($elm.hasClass('kabupaten')) {
					teks = '-- Pilih Kabupaten --';
				} else if ($elm.hasClass('kecamatan')) {
					teks = '-- Pilih Kecamatan --';
				}
				
				if (i == 0) {
					$elm.prop('disabled', false)
				}
				$elm
					.empty()
					.append(new_options)
					.prepend('<option value="">' + teks + '</option>')
					.val('');
				$('.spinner-border').remove();
			});
		});
	}
	
	$('.propinsi').change(function() {
		$wilayah = $('.kabupaten, .kecamatan, .kelurahan').prop('disabled', true);
		set_options($wilayah, base_url + 'wilayah/ajaxgetkabupatenbyidpropinsi?id=' + this.value);
	});
	
	$('.kabupaten').change(function() {
		$wilayah = $('.kecamatan, .kelurahan').prop('disabled', true);
		set_options($wilayah, base_url + 'wilayah/ajaxgetkecamatanbyidkabupaten?id=' + this.value);
	});
	
	$('.kecamatan').change(function() {
		$wilayah = $('.kelurahan').prop('disabled', true);
		$spinner.insertAfter($wilayah);
		set_options($wilayah, base_url + 'wilayah/ajaxgetkelurahanbyidkecamatan?id=' + this.value);
	});
	
	$('form').delegate('.del-row', 'click', function() {
		$this = $(this);
		$table = $this.parents('table').eq(0);
		$trs = $table.find('tbody').find('tr');
		if ($trs.length == 1) {
			$table.hide();
		} else {
			$tr = $(this).parents('tr').eq(0).remove();
			$trs = $table.find('tbody').find('tr');
			no = 1;
			$trs.each (function(i, elm) {
				$td = $(elm).find('td');
				$td.eq(0).html(no);
				no++;
			});
		}
		
		
	})
});