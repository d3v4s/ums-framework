$(document).ready(function() {
	/* click event on delete new email button to send XML HTTP request */
	$('#user-update-form #btn-delete-new-email').click(function(event) {
		/* get button and token */
		const $btn = $(this),
			actionUrl = $btn.val(),
			$form = $('#user-update-form');

		/* block default click event and disable button */
		event.preventDefault();
		disableElement($btn);

		/* show messagebox */
		$.MessageBox({
			buttonDone  : "Yes",
			buttonFail  : "No",
			message     : "Delete your new email?"
		}).done(function() { /* confirm function */
			/* get token */
			const $xf = $form.find('#_xf_del_ml');

			/* success function */
			funcSuccess = function(response) {
				enableElement($btn);
				try {
					showMessage(response.message, !response.success);
					if (response.success) setTimeout(function() {location.reload();}, 2000);
					else if (response.ntk !== undefined) $xf.val(response.ntk);
				} catch (e) {
					showMessage('Delete new email failed', true);
				}
			};

			/* fail function */
			funcFail = function() {
				enableElement($btn);
				showMessage('Problem to contact server', true);
			};

			sendAjaxReq(actionUrl, {}, $xf, funcSuccess, funcFail);
		}).fail(function(){
			enableElement($btn);
		});
	});

	/* click event on delete user button to send XML HTTP request */
	$('#user-update-form #btn-resend-email').click(function(event) {
		/* get form, button and token */
		const $btn = $(this),
			actionUrl = $btn.val(),
			$form = $('#user-update-form'),
			$xf = $form.find('#_xf_res_ml');

		/* block default submit form and show disable button */
		event.preventDefault();
		disableElement($btn);

		/* success function */
		funcSuccess = function(response) {
			enableElement($btn);
			try {
				showMessage(response.message, !response.success);
				if (response.ntk !== undefined) $xf.val(response.ntk);
			} catch (e) {
				showMessage('Email resend failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			enableElement($btn);
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq(actionUrl, {}, $xf, funcSuccess, funcFail);
	});
});