$(document).ready(function(){
	/* click event on delete user button to send XML HTTP request */
	$('#user-update-form #btn-delete').click(function(event) {
		/* get form, button and action url */
		const $btn = $(this),
			$form = $('#user-update-form'),
			actionUrl = $btn.attr('href');

		/* block default submit form and show loading */
		event.preventDefault();
		var txtBttn = showLoading($btn);

		/* show messagebox */
		$.MessageBox({
			buttonDone  : "Yes",
	    	buttonFail  : "No",
	    	message     : "Delete your account?"

		}).done(function(){
			/* confirm function */
			redirect(actionUrl);
		}).fail(function(){
			/* fail function*/
			removeLoading($btn, txtBttn);
		});
	});

	/* submit event on update user form to send XML HTTP request */
	$('#user-update-form').on('submit', function(event) {
		/* get button, token and serialize data */
		const $xf = $(this).find('#_xf_upd'),
			$btn = $(this).find('#btn-update'),
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
				if (response.success) setTimeout(redirect, 2000, response.redirect_to);
				else {
					focusError(response);
					if (response.ntk !== undefined) $xf.val(response.ntk);
				}
			} catch (e) {
				showMessage('User update failed', true);
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