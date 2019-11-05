$(document).ready(function (){
	$('#reset-pass-req-form').on('submit', function(event) {
    	const $btn = $(this).find('#btn-reset-pass'),
    		$xf = $(this).find('#_xf.send-ajax'),
    		data = $(this).find('.send-ajax').serialize();

    	event.preventDefault();
    	showLoading($btn);
		$.ajax({
			method: 'post',
			data: data,
			url: '/auth/reset/password',
			success: function(response) {
				removeLoading($btn, 'Reset Password');
				try {
					const userRes = JSON.parse(response);
					
					showMessage(userRes.message, !userRes.success);
					if(userRes.success) setTimeout(redirect, 2000, '/');
					else {
						focusError(userRes);
						$xf.val(userRes.ntk);
					}
				} catch (e) {
					showMessage('Request failed', true);
				}
			},
			failure: function() {
    			removeLoading($btn, 'Reset Password');
    			showMessage('Problem to contact server', true);
			}
		});
	});
});