$(document).ready(function () {
	/* submit event on logut form to send XML HTTP request */
	$('#logout-form').on('submit', function(event) {
		/* get button, token, and serialize data */
		const $xf = $(this).find('#_xf-out'),
			$btn = $(this).find('#btn-logout'),
			data = $(this).find('.send-ajax').serialize();

		/* block default submit form and disable button */
		event.preventDefault();
		disableElement($btn);

		/* success function */
		funcSuccess = function(response) {
			console.log(response);
			enableElement($btn);
			try {
				showMessage(response.message, !response.success);
				if(response.success) setTimeout(redirect, 2000, '/');
				else {
					focusError(response);
					$xf.val(response.ntk);
				}
			} catch (e) {
				showMessage('Logout failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			enbleElement($btn);
			showMessage('Problem to contact server', true);
		};
		
		sendAjaxReq('/auth/logout', data, $xf.val(), funcSuccess, funcFail, 'XS-TKN-OUT');
	});
});