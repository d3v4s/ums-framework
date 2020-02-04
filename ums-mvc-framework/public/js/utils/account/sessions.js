$(document).ready(function(){
	/* submit event to send remove session on XML HTTP request */
	$('form.remove-session').on('submit', function(event) {
		/* get button, token, action and serialize data */
		const $xf = $('#sessions-table').find('#_xf'),
			$btn = $(this).find('.btn[type="submit"]'),
			actionUrl = $(this).attr('action'),
			data = $(this).find('.send-ajax').serialize();

		/* block default submit form and show loading */
		event.preventDefault();
		var txtBttn = showLoading($btn);

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, txtBttn);

			try {
				showMessage(response.message, !response.success);
				if (response.success) setTimeout(redirect, 2000, location.href);
				else {
					focusError(response);
					if (response.ntk !== undefined) $xf.val(response.ntk);
				}
			} catch (e) {
				showMessage('Remove session failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, txtBttn);
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq(actionUrl, data, $xf, funcSuccess, funcFail);
	});
});