$(document).ready(function() {
	$.ajax({
		method: 'post',
		data: {
			_xf: $('#_xf.send-ajax').val()
		},
		url: '/app/config/get/key/json',
		success: function(response) {
			try {
				const res = JSON.parse(response);
				window.keyN = res.keyN;
				window.keyE = res.keyE;
				$('#_xf.send-ajax').val(res.ntk);
			} catch (e) {
				showMessage('Invalid server response', true);
			}
		},
		failure: function() {
			showMessage('Problem to contact server', true);
		}
	});
});