$(document).ready(function() {
	const query_string = new URLSearchParams(window.location.search);
	id = query_string.get('id');
	
	detail = JSON.parse($('.penjualan-detail').text());
	show_detail_penjualan(detail);
})