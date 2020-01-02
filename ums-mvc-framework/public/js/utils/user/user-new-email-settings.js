$(document).ready(function() {
	/* click event on delete new email button to send XML HTTP request */
	$('#user-update-form #btn-delete-new-email').click(function(event) {
		/* get button and token */
		const $form = $('#user-update-form'),
			$btn = $(this);

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
			const $xf = $form.find('#_xf');

			/* success function */
			funcSuccess = function(response) {
				enableElement($btn);
				try {
					showMessage(response.message, !response.success);
					if (response.success) setTimeout(function() {location.reload();}, 2000);
					else $xf.val(response.ntk);
				} catch (e) {
					showMessage('Delete new email failed', true);
				}
			};

			/* fail function */
			funcFail = function() {
				enableElement($btn);
				showMessage('Problem to contact server', true);
			};

			sendAjaxReq('/user/settings/new/email/delete', {}, $xf.val(), funcSuccess, funcFail);
//			$.ajax({
//				method: 'post',
//				data: data,
//				url: '/user/settings/new/email/delete',
//				success: function(response) {
//					
//				},
//				failure: function() {
//					enableElement($btn);
//					showMessage('Problem to contact server', true);
//				}
//			});
		}).fail(function(){
			enableElement($btn);
		});
	});

	/* click event on delete user button to send XML HTTP request */
	$('#user-update-form #btn-resend-email').click(function(event) {
		/* get form, button and token */
		const $form = $('#user-update-form'),
			$btn = $(this),
			$xf = $form.find('#_xf');

		/* block default submit form and show disable button */
		event.preventDefault();
		disableElement($btn);

		/* success function */
		funcSuccess = function(response) {
			enableElement($btn);
			try {
				showMessage(response.message, !response.success);
				$xf.val(response.ntk);
			} catch (e) {
				showMessage('Email resend failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			enableElement($btn);
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq('/user/settings/new/email/resend/validation', {}, $xf.val(), funcSuccess, funcFail);
//		$.ajax({
//			method: 'post',
//			data: data,
//			url: '/user/settings/new/email/resend/validation',
//			success: function(response) {
//				enableElement($btn);
//				try {
//					const response = JSON.parse(response);
//					
//					showMessage(response.message, !response.success);
//					$xf.val(response.ntk);
//				} catch (e) {
//					showMessage('Email resend failed', true);
//				}
//			},
//			failure: function() {
//				enableElement($btn);
//				showMessage('Problem to contact server', true);
//			}
//		});
	});
});