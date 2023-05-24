$(document).ready(function() {
	const query_string = new URLSearchParams(window.location.search);
	id = query_string.get('id');
	
	show_form_penjualan(id)
	
	$(document).delegate('.jml-bayar', 'keyup', function() {
		$this = $(this);
		len = this.value.length;
		if (len < 8) len = 8;
		$this.css('width', (len* 1.1) + 'ch');
	});
})