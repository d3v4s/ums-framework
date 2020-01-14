$(document).ready(function(){
	/* click event on delete account button to send XML HTTP request */
	$('#delete-account-form').on('submit', function(event) {
		/* get form, button, token and action url */
		const $xf = $(this).find('#_xf'),
			$btn = $(this).find('#btn-delete'),
			actionUrl = $(this).attr('action');

		/* block default submit form and show loading */
		event.preventDefault();
		showLoading($btn);

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, 'Delete');

			try {
				showMessage(response.message, !response.success);
				if (response.success) setTimeout(redirect, 2000, '/');
				else if (response.ntk !== undefined) $xf.val(response.ntk);
			} catch (e) {
				showMessage('Delete user failed', true);
			}
		};
		
		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Delete');
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq(actionUrl, {}, $xf, funcSuccess, funcFail);
	});
});