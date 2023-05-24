$(document).ready(function() {
	const query_string = new URLSearchParams(window.location.search);
	id = query_string.get('id');
	
	detail = JSON.parse($('.detail-barang').text());
	show_form_barang(detail);
})