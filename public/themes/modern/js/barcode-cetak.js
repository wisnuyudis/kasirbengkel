/**
* Written by: Agus Prawoto Hadi
* Year		: 2021
* Website	: jagowebdev.com
*/

jQuery(document).ready(function () {
	
	$('.barcode').keypress(function(e) {
		if (e.which == 13) {
			return false;
		}
	})
	const button = $('#print,#export-pdf,#export-word');
	let $container = $('#barcode-print-container');
	$('.barcode').keyup(function(e) {
		
		$this = $(this);
		value = $this.val().replace(/\D/g,'');
		this.value = value.substr(0,13);
		// console.log(value.length);
		if (value.length >= 13) 
		{
			value = value.substr(0,13);
			$spinner = $('<div class="spinner-border text-secondary spinner" style="height: 18px; width:18px; position:absolute; right:15px; top:7px" role="status"><span class="visually-hidden">Loading...</span></div>');
			$parent = $this.parent();
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
	
	$('table').delegate('.jml-cetak', 'keyup', function() {
		
		jml_cetak = 0;
		$('table').find('.jml-cetak').each(function(i, el) {
			jml_cetak += setInt($(el).val());
		});

		if (jml_cetak == 0) {
			$('#preview').attr('disabled', 'disabled');
		} else {
			$('#preview').removeAttr('disabled');
		}
		this.value = format_ribuan(this.value);
	});
	
	// console.log (format_ribuan(10000));
	$('.add-barang').click(function() {
		$this = $(this);
		if ($this.hasClass('disabled')) {
			return false;
		}
		var $modal = jwdmodal({
			title: 'Pilih Barang',
			url: base_url + '/barcode-cetak/getDataDTListBarang',
			width: '650px',
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
			barang = JSON.parse($tr.find('.detail-barang').text());
			
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
			$td.eq(1).html(barang.nama_barang);
			$td.eq(2).html(barang.barcode);
			$tr.find('.jml-cetak').val(10);
						
			$table.show();
			$tbody.append($tr);
						
			$('.list-barang-terpilih').find('.belum-ada').remove();
			$('.list-barang-terpilih').append('<small  class="px-3 py-2 me-2 mb-2 text-light bg-success bg-opacity-10 border border-success border-opacity-10 rounded-2">' + barang.nama_barang + '</small>');
			
			$('#preview').removeAttr('disabled');
			
			generateBarcode();
			
			// $(document);
		});
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
		
		generateBarcode();
	});
	
	$('#paper-size-width, #paper-size-height').keyup(function() {
		this.value = setInt(this.value);
		if (this.value > 300) {
			this.value = 300;
		}
		
		w = parseInt($('#paper-size-width').val()) * pixel;
		h = parseInt($('#paper-size-height').val()) * pixel;
		$container.css('width', w);
		
		$container = $('#barcode-print-container');
		if ($container.find('canvas').eq(0).length) {
			$container.css({minHeight: h});
		}
	})
	
	$('#paper-size-width, #paper-size-height').blur(function() {
		if (this.value < 100) {
			this.value = 100;
		}
	})
	
	$('#paper-size').change(function() {
		
		let w = 0;
		let h = 0;
		const $paper_width = $('#paper-size-width').attr('disabled', 'disabled');
		const $paper_height = $('#paper-size-height').attr('disabled', 'disabled');
		
		
		if (this.value == 'a4') {
			w = 210;
			h = 297;
		} else if (this.value == 'f4') {
			w = 215;
			h = 330;
		} else {
			w = 210;
			h = 297;
			$paper_width.removeAttr('disabled');
			$paper_height.removeAttr('disabled');
			
		}
		
		paper_width = $paper_width.val(w);
		paper_height = $paper_height.val(h);
		
		w = w * pixel;
		h = h * pixel;
		$container.css('width', w);
		
		$container = $('#barcode-print-container');
		if ($container.find('canvas').eq(0).length) {
			$container.css({minHeight: h});
		}
		// $container.css('width', w);
		// generateBarcode();
	})
	
	$('#display-value').change(function() {
		generateBarcode();
	})
	
	$('#barcode-height').on('input', function() {
		generateBarcode();
	})
	
	$('#barcode-width').on('input', function() {
		generateBarcode();
	})
	
	$('table').delegate('.jml-cetak', 'keyup', function() {
		generateBarcode();
	})
	
	function setEmptyBarcode() {
		$container = $('#barcode-print-container').empty();
		$container.css('height', 'auto');
		$container.css('text-align', 'center');
		$container.html('PREVIEW');
		
		button.attr('disabled', 'disabled');
	}
	
	const pixel = 3.7795275591; // 1 mm => pixel
	const milimeter = 0.2645833333; //1 px  => mm
	function generateBarcode() 
	{
		if ($('#list-barang').is(':hidden')) {
			setEmptyBarcode();
			return false;
		} else {
			
			let jml_cetak = 0;
			$barcode_barang = $('.barcode-barang');
			$barcode_barang.each(function(i, elm) {
				$elm = $(elm);
				$tr = $elm.parents('tr').eq(0);
				jml_cetak += setInt( $tr.find('.jml-cetak').val() );
			});
			
			if (jml_cetak == 0) {
				setEmptyBarcode();
				return false;
			}
		}
		
		button.removeAttr('disabled');
		// ukuran_kertas = $('#paper-size').val();
		/* let w = 0;
		let h = 0;
		if (ukuran_kertas == 'a4') {
			w = 210;
			h = 297;
		} else if (ukuran_kertas == 'f4') {
			w = 215;
			h = 330;
		}
		
		w = w * pixel;
		h = h * pixel; */
		
		h = $('#paper-size-height').val() * pixel;
		$container.empty();
		// $container.css('width', w);
		$container.css({minHeight: h});
		$container.css('text-align', 'left');
		
		$('.barcode-barang').each(function(i, elm) {
			$elm = $(elm);
			$tr = $elm.parents('tr').eq(0);
			jml_cetak = setInt( $tr.find('.jml-cetak').val() );
			for ( let i = 1; i <= jml_cetak; i++) {
				
				id = 'barcode-' + i + '-' + $elm.text();
				$canvas = $('<canvas/>');
				$canvas.attr({'id': id});
				$canvas.css('padding-right', (5 * pixel) + 'px');
				$canvas.appendTo($container);
				
				JsBarcode('#' + id, $elm.text(), {
					format: "ean13",
					width: $('#barcode-width').val(),
					height: $('#barcode-height').val(),
					displayValue: $('#display-value').val() == 'Y' ? true : false
				});
			}
		})
		
		$container.show();
	}
	
	$('#export-pdf').click(function() 
	{

		paper_width = setInt($('#paper-size-width').val());
		paper_height = setInt($('#paper-size-height').val());
		
		const orientation = paper_width > paper_height ? 'lanscape' : 'portrait';
		
		window.jsPDF = window.jspdf.jsPDF;
		const pdf = new jsPDF({
		  orientation: orientation,
		  unit: "mm",
		  format: [paper_height, paper_width]
		});
		
		margin_left = 5;
		margin_top = 5;

		row_width = 0;
		index_col = 0;
		index_row = 0;
		
		barcode_margin_right = 5;
		barcode_margin_bottom = 5;
		
		x = 0;
		y = 10;
		
		$container = $('#barcode-print-container');
		$container.find('canvas').each(function(i, elm) 
		{
			const $elm = $(elm);
			const image_string = $(elm)[0].toDataURL();
			width = parseFloat($elm.width()) * milimeter;
			height = parseFloat($elm.height()) * milimeter;
			
			x = margin_left + (index_col * width) + ( index_col * barcode_margin_right );
			row_width = x + width + barcode_margin_right;

			if (row_width > paper_width) {
				index_col = 0;
				row_width = 0;
				index_row++;
				x = margin_left + (index_col * width) + ( index_col * barcode_margin_right );
			} 
			
			index_col++;
			y = margin_top + (index_row * height) + ( index_row * barcode_margin_bottom );			
			
			if (y + height + barcode_margin_bottom > paper_height) {

				pdf.addPage([paper_height, paper_width], orientation);
				index_col = 1;
				row_width = 0;
				index_row = 0;
				y = margin_top + (index_row * height) + ( index_row * barcode_margin_bottom );
			}
			pdf.addImage(image_string, 'PNG', x, y, width, height);
			
		});
		pdf.save("Barcode-cetak.pdf");
	})
	
	$('#print').click(function()
	{
		margin_left = 10; //mm
		margin_top = 10; //mm

		row_width = 0;
		index_col = 0;

		barcode_margin_right = 0;
		barcode_margin_bottom = 0;

		$container = $('#barcode-print-container');
		
		$table = $('<table id="table-print">');
		$tbody = $('<tbody>');
		$tr = $('<tr>');
		$container.find('canvas').each(function(i, elm) {
			const $elm = $(elm);
			const $elm_new = $elm.clone();
			const image_string = $(elm)[0].toDataURL();
			width = parseFloat($elm.width()) * milimeter;
			height = parseFloat($elm.height()) * milimeter;
			
			row_width = margin_left + (index_col * width) + ( index_col * barcode_margin_right );
			index_col++;
			
			cek_width = row_width + (width * milimeter);
			// console.log(cek_width);
			if ( cek_width > 210 ) 
			{
				index_col = 1;
				row_width = 0;
				$tbody.append($tr);
				$tr = $('<tr>');
			}
			
			$td = $('<td>');

			$td.html('<img src="' + image_string + '" style="width:' + $elm.width() + 'px; max-width:' +  $elm.width() + 'px; height: ' + $elm.height() + 'px"/>');
			$tr.append($td);
			
		});
		
		if ($tr.children('td').length) {
			$tbody.append($tr);
		}
		
		$('#print-container').remove();
		$print_container = $('<div id="print-container" style="padding:10px">');
		$table.append($tbody);
		$print_container.append($table);
		$print_container.appendTo($('.card-body'));
		
		printJS({printable: 'print-container', type: 'html', css: base_url + 'public/themes/modern/css/barcode-cetak-print.css'});
		$('#print-container').remove();
		
	})
	
	function mm(value) {
		point = value * 2.83465; // 1mm to point
		dxa = 20;
		return point * dxa; 
	}
	
	$('#export-word').click(function() {
		
		paper_width = setInt($('#paper-size-width').val());
		paper_height = setInt($('#paper-size-height').val());
		
		margin_left = 10; //mm
		margin_top = 10; //mm

		row_width = 0;
		index_col = 0;

		barcode_margin_right = 0;
		barcode_margin_bottom = 0;

		table_row = [];
		table_col = [];
		$container = $('#barcode-print-container');
		$container.find('canvas').each(function(i, elm) {
			
			const $elm = $(elm);
			const image_string = $(elm)[0].toDataURL();
			width = parseFloat($elm.width());
			height = parseFloat($elm.height());
		
			row_width = margin_left + (index_col * width * milimeter) + ( index_col * barcode_margin_right );
			index_col++;
			
			cek_width = row_width + (width * milimeter);
			if ( cek_width > paper_width) 
			{
				index_col = 1;
				row_width = 0;

				table_row.push(
					new docx.TableRow({
						children: table_col	
					})
				)
				
				table_col = [];				
			}
			
			table_col.push(
				new docx.TableCell({							
					children: [
						new docx.Paragraph({
							children : [
								new docx.ImageRun({
								data: image_string,
								transformation: {
										width: width,
										height: height,
									}
								})
							]
						})
						
					]
					
				})
			)
			
		});
		
		if (table_col) {
			table_row.push(
				new docx.TableRow({
					children: table_col	
				})
			)
		}
		
		const orientation = paper_width > paper_height ? docx.PageOrientation.LANSCAPE : docx.PageOrientation.PORTRAIT;
	 	const doc = new docx.Document({
			creator: "Jagowebdev",
			description: "Barcode",
			title: "Barcode",
			sections: [
				{
					properties: {
						page: {
							margin: {
								top: mm(margin_top),
								right: mm(10),
								bottom: mm(10),
								left: mm(margin_left)
							},
							size: {
								orientation: orientation,
								height: docx.convertMillimetersToTwip(paper_height),
								width: docx.convertMillimetersToTwip(paper_width),
							},
						},
					},
					children: [
						new docx.Table({
							rows: table_row
						})
					]
				}
			]
		});
		
		docx.Packer.toBlob(doc).then(blob => {
		saveAs(blob, "Barcode-cetak.docx");
		});
	})
});