$(document).ready(function() {
	/* click event on delete user button to send XML HTTP request */
	$('#invalidate-form').on('submit', function(event) {
		/* get form and button */
		const $form = $(this),
			actionUrl = $(this).attr('action'),
			$btn = $(this).find('#btn-invalidate');

		/* block default submit form and show loading */
		event.preventDefault();
		var txtBttn = showLoading($btn);

		/* show messagebox */
		$.MessageBox({
			buttonDone  : "Yes",
	    	buttonFail  : "No",
	    	message     : "Invalidate?"
		}).done(function(){
			/* confirm function */
			/* get token and serialize data */
			const $xf = $form.find('#_xf_invldt'),
				data = $form.find('.send-ajax').serialize();
			
			/* success function */
			funcSuccess = function(response) {
				removeLoading($btn, txtBttn);
				try {
					showMessage(response.message, !response.success);
					if (response.success) setTimeout(redirect, 2000, response.redirect_to);
					else if (response.ntk !== undefined) $xf.val(response.ntk);
					
				} catch (e) {
					showMessage('Invalidate failed', true);
				}
			};
			
			/* fail function */
			funcFail = function() {
				removeLoading($btn, txtBttn);
				showMessage('Problem to contact server', true);
			};
			
			sendAjaxReq(actionUrl, data, $xf, funcSuccess, funcFail);
		}).fail(function(){
			/* fail function */
			removeLoading($btn, txtBttn);
		});
	});
});
