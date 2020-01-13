$(document).ready(function () {
	/* submit event on signup form to send XML HTTP request */
	$('#signup-form').on('submit', function(event) {
		/* get button and token */
		const $xf = $(this).find('#_xf'),
			$btn = $(this).find('#btn-signup'),
			actionUrl = $(this).attr('action'),
			$cryptElem = $('.send-ajax-crypt');

		/* serialize data */
		var data = $(this).find('.send-ajax').serialize();

		/* block default submit form and show loading */
		event.preventDefault();
		showLoading($btn);

		try {
			/* crypt password and append on data */
			data += '&' + cryptSerialize($cryptElem);
		} catch (e) {
			removeLoading($btn, 'Signup');
			showMessage('Signup failed', true);
			return;
		}

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, 'Signup');
			try {
				showMessage(response.message, !response.success);
				if (response.success) setTimeout(redirect, 2000, response.redirect_to);
				else {
					focusError(response);
					if (response.ntk !== undefined) $xf.val(response.ntk);
				}
			} catch (e) {
				showMessage('Signup failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Signup');
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq(actionUrl, data, $xf, funcSuccess, funcFail);
	});
});