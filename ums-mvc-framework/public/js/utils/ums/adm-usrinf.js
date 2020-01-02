$(document).ready(function() {
	/* click event on delete user button to send XML HTTP request */
	$('#delete-user-form #btn-delete-user').click(function(event) {
		/* get form and button */
		const $form = $('#delete-user-form'), 
			$btn = $(this);

		/* block default submit form and show loading */
		event.preventDefault();
		showLoading($btn);

		/* show messagebox */
		$.MessageBox({
			buttonDone  : "Yes",
	    	buttonFail  : "No",
	    	message     : "Delete this account?"

		}).done(function(){ /* confirm function */
			/* get token and serialize data */
			const $xf = $form.find('#_xf-du'),
				data = $form.find('.send-ajax').serialize();

			/* success function */
			funcSuccess = function(response) {
				removeLoading($btn, 'Delete');
				try {
					showMessage(response.message, !response.success);
					if (response.success) setTimeout(redirect, 2000, '/ums/users');
					else $xf.val(response.ntk);
					
				} catch (e) {
					showMessage('Delete user failed', true);
				}
			};

			/* fail function */
			funcFail = function() {
				removeLoading($btn, 'Delete');
				showMessage('Problem to contact server', true);
			};

			sendAjaxReq('/ums/user/delete/confirm', data, $xf.val(), funcSuccess, funcFail, 'XS-TKN-DU');
//			$.ajax({
//				method: 'post',
//				data: data,
//				url: '/ums/user/delete/confirm',
//				success: function(response) {
//					removeLoading($btn, 'Delete');
//					try {
//						const deleteRes = JSON.parse(response);
//						
//						showMessage(deleteRes.message, !deleteRes.success);
//						if (deleteRes.success) setTimeout(redirect, 2000, '/ums/users');
//						else $form.find('#_xf-du.send-ajax').val(deleteRes.ntk);
//						
//					} catch (e) {
//						showMessage('Delete user failed', true);
//					}
//				},
//				failure: function() {
//					removeLoading($btn, 'Delete');
//					showMessage('Problem to contact server', true);
//				}
//			});
		}).fail(function(){ /* fail function */
			removeLoading($btn, 'Delete');
		});
	});

	/* submit event on login form to send XML HTTP request */
	$('#delete-new-email-form').on('submit', function(event) {
		/* get button, token, and serialize data */
		const $btn = $(this).find('#btn-delete-new-email'),
			$xf = $(this).find('#_xf-dnm'),
			data = $(this).find('.send-ajax').serialize();

		/* block default submit form and disable button */
		event.preventDefault();
		disableElement($btn);

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

		sendAjaxReq('/ums/user/delete/new/email', data, $xf.val(), funcSuccess, funcFail, 'XS-TKN-DNM');
//		$.ajax({
//			method: 'post',
//			data: data,
//			url: '/ums/user/delete/new/email',
//			success: ,
//			failure: 
//		});
	});

	/* submit event on reset wrong password form to send XML HTTP request */
	$('#reset-wrong-pass-form').on('submit', function(event) {
		/* get button, token, and serialize data */
		const $xf = $(this).find('#_xf-rwp'),
			$btn = $(this).find('#btn-reset-wrong-pass'),
			data = $(this).find('.send-ajax').serialize();

		/* block default submit form and disable button */
		event.preventDefault();
		disableElement($btn);

		/* success function */
		funcSuccess = function(response) {
			enableElement($btn);
			try {
				showMessage(response.message, !response.success);
				if (response.success) setTimeout(function() {location.reload();}, 2000);
				else $xf.val(response.ntk);
			} catch (e) {
				showMessage('Reset wrong password failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			enableElement($btn);
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq('/ums/user/update/reset/wrong/pass', data, $xf.val(), funcSuccess, funcFail, 'XS-TKN-RWP');
//		$.ajax({
//			method: 'post',
//			data: data,
//			url: '',
//			success:,
//			failure: 
//		});
	});

	/* submit event on login form to send XML HTTP request */
	$('#reset-lock-user-form').on('submit', function(event) {
		/* get button, token, and serialize data */
		const $xf = $(this).find('#_xf-rlu'),
			$btn = $(this).find('#btn-reset-lock'),
			data = $(this).find('.send-ajax').serialize();

		/* block default submit form and disable button */
		event.preventDefault();
		disableElement($btn);

		/* success function */
		funcSuccess = function(response) {
			enableElement($btn);
			try {
				showMessage(response.message, !response.success);
				if (response.success) setTimeout(function() {location.reload();}, 2000);
				else $xf.val(response.ntk);
			} catch (e) {
				showMessage('Reset locks user failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			enableElement($btn);
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq('/ums/user/update/reset/lock', data, $xf.val(), funcSuccess, funcFail, 'XS-TKN-RLU');
	});
});