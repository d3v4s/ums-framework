$(document).ready(function() {
	$('#signup-confirm-form').on('submit', function(event) {
		const $btn = $(this).find('#btn-resend-email'),
			$xf = $(this).find('#_xf.send-ajax'),
			data = $(this).find('.send-ajax').serialize();

		event.preventDefault();
    	showLoading($btn);
		$.ajax({
			method: 'post',
			url: '/auth/signup/confirm/email/resend',
			data: data,
			success: function(response) {
				removeLoading($btn, 'Resend email');
				try {
					const signupRes = JSON.parse(response);
					
					showMessage(signupRes.message, !signupRes.success);
					
					$xf.val(signupRes.ntk);
				} catch (e) {
					showMessage('Resend email failed', true);
				}
			},
			failure: function() {
				removeLoading($btn, 'Resend email');
				showMessage('Problem to contact server', true);
			}
		});
	});
});