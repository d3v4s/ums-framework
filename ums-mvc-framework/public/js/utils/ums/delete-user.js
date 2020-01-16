$(document).ready(function() {
	/* click event on delete user button to send XML HTTP request */
	$('#delete-user-form').click(function(event) {
		/* get form and button */
		const $btn = $(this).find('#btn-delete-user'),
			actionUrl = $(this).attr('action');

		/* block default submit form and show loading */
		event.preventDefault();
		showLoading($btn);

		/* get token and serialize data */
		const $xf = $form.find('#_xf'),
			data = $form.find('.send-ajax').serialize();
		
		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, 'Delete');
			try {
				showMessage(response.message, !response.success);
				if (response.success) setTimeout(redirect, 2000, response.redirect_to);
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
		
		sendAjaxReq(actionUrl, data, $xf, funcSuccess, funcFail);
//		$.ajax({
//			method: 'post',
//			data: data,
//			url: '/ums/user/delete/confirm',
//			success: function(response) {
//				removeLoading($btn, 'Delete');
//				try {
//					const deleteRes = JSON.parse(response);
//					
//					showMessage(deleteRes.message, !deleteRes.success);
//					if (deleteRes.success) setTimeout(redirect, 2000, '/ums/users');
//					else $form.find('#_xf-du.send-ajax').val(deleteRes.ntk);
//					
//				} catch (e) {
//					showMessage('Delete user failed', true);
//				}
//			},
//			failure: function() {
//				removeLoading($btn, 'Delete');
//				showMessage('Problem to contact server', true);
//			}
//		});
//		/* show messagebox */
//		$.MessageBox({
//			buttonDone  : "Yes",
//	    	buttonFail  : "No",
//	    	message     : "Delete this account?"
//		}).done(function(){
//			/* confirm function */
//			redirect(actionUrl);
//			
//		}).fail(function(){ /* fail function */
//			removeLoading($btn, 'Delete');
//		});
	});
});