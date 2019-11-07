$(document).ready(function () {
	$('#logout-form').on('submit', function(event) {
		const $btn = $(this).find('#btn-logout'),
			data = $(this).find('.send-ajax').serialize();

		event.preventDefault();
		disableElement($btn);
		$.ajax({
			method: 'post',
			data: data,
			url: '/auth/logout',
			success: function(response) {
				enableElement($btn);
				try {
					const userRes = JSON.parse(response);
					
					showMessage(userRes.message, !userRes.success);
					if(userRes.success) setTimeout(redirect, 2000, '/');
					else {
						focusError(userRes);
						$(this).find('#_xf-out.send-ajax').val(userRes.ntk);
					}
				} catch (e) {
					showMessage('Logout failed', true);
				}
			},
			failure: function() {
				enbleElement($btn);
    			showMessage('Problem to contact server', true);
			}
		});
	});
});