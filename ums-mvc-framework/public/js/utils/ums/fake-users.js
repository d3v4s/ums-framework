$(document).ready(function() {
	/* submit event on fake user generator form to send XML HTTP request */
	$('#fakeusr-generator-form').on('submit', function(event) {
		/* get button, token, and serialize data */
		const $xf = $(this).find('#_xf'),
			$btn = $(this).find('#btn-add'),
			actionUrl = $(this).attr('action'),
			data = $(this).find('.send-ajax').serialize();

		/* block default submit form and show loading */
		event.preventDefault();
		showLoading($btn);

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, 'Add');
			try {
				showMessage(response.message, !response.success)
				if (response.success) setTimeout(redirect, 2000, response.redirect_to);
				else if (response.ntk !== undefined) $xf.val(response.ntk);
			} catch (e) {
				showMessage('Add fake users failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Add');
			showMessage('Problem to contact server', true);
		};
		
		sendAjaxReq(actionUrl, data, $xf, funcSuccess, funcFail);
	});
});