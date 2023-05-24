function show_alert(title, content, icon, timer) {
	
	message = parse_message(content);
	
	const setting = { 
		title: title,
		html: message,
		icon: icon,
		showConfirmButton : true
	}
	
	if (timer) {
		setting.timer = timer
		setting.showConfirmButton = false
	}
	
	Swal.fire( setting )
}

function generate_alert(type, message) {
	return '<div class="alert alert-dismissible alert-'+type+'" role="alert">' + message + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
	
}

function format_date(pattern) {
	var now = new Date();
	var dd = String(now.getDate()).padStart(2, '0');
	var mm = String(now.getMonth() + 1).padStart(2, '0');
	var yyyy = now.getFullYear();
	
	result = pattern.replace('dd', dd);
	result = result.replace('mm', mm);
	result = result.replace('yyyy', yyyy);
	
	return result;
}

function parse_message(content) {
	let message = content;
	if (typeof (content) == 'object') 
	{
		keys = Object.keys(content);
		
		if (keys.length == 1) {
			for (k in content) {
				message = content[k];
			}
		} else {
			message = '<ul>';
			for (k in content) {
				message += '<li>' + content[k] + '</li>';
			}
			message += '</ul>';
		}
	}
	
	return message;
}

function format_ribuan(bilangan) 
{
	if (!bilangan) {
		return 0;
	}
	
	bilangan = bilangan.toString().replace(/[^-\d]/g, '');
	
	if (bilangan == '-')
		return 0;
	
	bilangan = parseFloat( bilangan );

	let minus = bilangan.toString().substr(0,1) == '-' ? '-' : '';
	
	var	reverse = bilangan.toString().split('').reverse().join(''),
		ribuan 	= reverse.match(/\d{1,3}/g);
		ribuan	= ribuan.join('.').split('').reverse().join('');
	
	return minus + ribuan;
}

function setInt (number) {
	if (!number)
		return 0;
	
	number = parseFloat( number.toString().replace(/[^-\d]/g, '') );
	if (!number)
		return 0;
	return number;
}