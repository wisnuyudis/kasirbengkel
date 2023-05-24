 $(document).ready(function() {
	 	 
	 $('.gallery-container').delegate('.thumbnail-item', 'click', function() {

		 id_image = $(this).attr('data-id-file');
		 jwdfilepicker.init({
			title : 'Edit Image',
			id_file : id_image,
			onSelect: function ($elm) 
			{
				$this.find('.text').hide();
				$this.find('img').remove();
				
				$clone = $ul.find('li').eq(0).clone();
				$clone.find('img').replaceWith($elm.find('img'));
				$ul.append($clone);
			}
		});
	 });
	 
	 $('#add-image').click(function() 
	 {
		var $this = $(this);
		var $ul = $('.gallery-container').find('ul').eq(0);
		var $gallery_container = $('.gallery-container');
		jwdfilepicker.init({
			title : 'Gallery Image',
			filter_file : 'image',
			onSelect: function ($elm) {
				$this.find('.text').hide();
				$this.find('img').remove();
				meta_file = JSON.parse($elm.find('.meta-file').html());
				// console.log(meta_file);
				
				var $ul = $('.list-image-container');
				var $li_first = $ul.find('li').eq(0);
				var $li = $li_first.clone().hide();
					$li.removeAttr('data-initial-item');
				
				if ($li_first.attr('data-initial-item') == 'true') {
					$li_first.remove();
				}
				
				$gallery_container.find('.alert-danger').remove();
				$li.attr('id', 'barang--' + meta_file.id_file_picker);
				$li.attr('data-id-file', meta_file.id_file_picker);
				$li.find('[name="id_file_picker[]"]').val(meta_file.id_file_picker);
				
				$new_img = $elm.find('img');
				$li.find('img').replaceWith($new_img);

				$ul.prepend($li);
				$li.fadeIn('fast');
			}
		});
	});
	
	function show_message(type, content) {
		return '<div class="alert alert-danger">' + content + '</div>'; 
	}
	
		
	$('.gallery-container').delegate('.delete-image', 'click', function(e) {
		e.stopPropagation();
		$this = $(this);
		$this.parents('.thumbnail-item').eq(0).fadeOut('fast', function(){
			if ($(this).parent().children().length == 1) {
				$(this).attr('data-initial-item', 'true');
				$('.gallery-container').prepend(show_message('error', 'Gambar belum dipilih'));
			} else {
				$(this).remove();
			}
		});		
	})
	
	drag_image_gallery = dragula([document.getElementById('list-image-container')], {
		moves: function (el, container, handle) {
			return handle.classList.contains('grip') || handle.parentNode.classList.contains('grip');
		}
	});
	
	drag_image_gallery.on('dragend', function(el)
	{	
		$li = $('.gallery-container').find('li.thumbnail-item');
		
		list_id = [];
		$li.each(function(i, elm){
			list_id.push( $(elm).attr('id').split('-')[1] );
		});
	});
 });