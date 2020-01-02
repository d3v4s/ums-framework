$(document).ready(function(){
	/* click event on delete user button to send XML HTTP request */
	$('#user-update-form #btn-delete').click(function(event) {
		/* get form and button */
		const $form = $('#user-update-form'),
			$btn = $(this);

		/* block default submit form and show loading */
		event.preventDefault();
		showLoading($btn);

		/* show messagebox */
		$.MessageBox({
			buttonDone  : "Yes",
	    	buttonFail  : "No",
	    	message     : "Delete your account?"

		}).done(function(){ /* confirm function */
			/* get token */
			const $xf = $form.find('#_xf');

			/* success function */
			funcSuccess = function(response) {
				removeLoading($btn, 'Delete');
				
				try {
					showMessage(response.message, !response.success);
					if (response.success) setTimeout(redirect, 2000, '/');
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

			sendAjaxReq('/user/settings/delete/confirm', {}, $xf.val(), funcSuccess, funcFail);
//			$.ajax({
//				method: 'post',
//				data: data,
//				url: '/user/settings/delete/confirm',
//				success: function(response) {
//					removeLoading($btn, 'Delete');
//	
//					try {
//						const response = JSON.parse(response);
//						
//						showMessage(response.message, !response.success);
//						if (response.success) setTimeout(redirect, 2000, '/');
//						else $xf.val(response.ntk);
//					} catch (e) {
//						showMessage('Delete user failed', true);
//					}
//				},
//				failure: function() {
//					removeLoading($btn, 'Delete');
//					showMessage('Problem to contact server', true);
//				}
//			});
		}).fail(function(){
			removeLoading($btn, 'Delete');
		});
	});

	/* submit event on update user form to send XML HTTP request */
	$('#user-update-form').on('submit', function(event) {
		/* get button, token and serialize data */
		const $xf = $(this).find('#_xf'),
			$btn = $(this).find('#btn-update'),
			data = $(this).find('.send-ajax').serialize();

		/* block default submit form and show loading */
		event.preventDefault();
		showLoading($btn);

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, 'Update');

			try {
				showMessage(response.message, !response.success);
				if (response.success) setTimeout(redirect, 2000, '/');
				else {
					focusError(response);
					$xf.val(response.ntk);
				}
			} catch (e) {
				showMessage('User update failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Update');
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq('/user/settings/update', data, $xf.val(), funcSuccess, funcFail);
//		$.ajax({
//			method: 'post',
//			data: data,
//			url: '/user/settings/update',
//			success: function(response) {
//    			removeLoading($btn, 'Update');
//
//    			try {
//    				const userRes = JSON.parse(response);
//    				
//    				showMessage(userRes.message, !userRes.success);
//    				if (userRes.success) setTimeout(redirect, 2000, '/');
//    				else {
//    					focusError(userRes);
//    					$xf.val(userRes.ntk);
//    				}
//				} catch (e) {
//					showMessage('User update failed', true);
//				}
//			},
//			failure: function() {
//				
//			}
//		});
	});
});