$(document).ready(function(){
	$('#user-update-form #btn-delete').click(function(event) {
		const $form = $('#user-update-form'),
			$btn = $(this);
	
		event.preventDefault();
		showLoading($btn);
		$.MessageBox({
			buttonDone  : "Yes",
	    	buttonFail  : "No",
	    	message     : "Delete your account?"
		}).done(function(){
			const $xf = $form.find('#_xf.send-ajax'),
				data = $xf.serialize();
			$.ajax({
				method: 'post',
				data: data,
				url: '/user/settings/delete/confirm',
				success: function(response) {
					removeLoading($btn, 'Delete');
	
					try {
						const deleteRes = JSON.parse(response);
						
						showMessage(deleteRes.message, !deleteRes.success);
						if (deleteRes.success) setTimeout(redirect, 2000, '/');
						else $xf.val(deleteRes.ntk);
					} catch (e) {
						showMessage('Delete user failed', true);
					}
				},
				failure: function() {
					removeLoading($btn, 'Delete');
					showMessage('Problem to contact server', true);
				}
			});
		}).fail(function(){
			removeLoading($btn, 'Delete');
		});
	});

	$('#user-update-form').on('submit', function(event) {
		const $btn = $(this).find('#btn-update'),
			$xf = $(this).find('#_xf.send-ajax')
			data = $(this).find('.send-ajax').serialize();

		event.preventDefault();
		showLoading($btn);
		$.ajax({
			method: 'post',
			data: data,
			url: '/user/settings/update',
			success: function(response) {
    			removeLoading($btn, 'Update');

    			try {
    				const userRes = JSON.parse(response);
    				
    				showMessage(userRes.message, !userRes.success);
    				if (userRes.success) setTimeout(redirect, 2000, '/');
    				else {
    					focusError(userRes);
    					$xf.val(userRes.ntk);
    				}
				} catch (e) {
					showMessage('User update failed', true);
				}
			},
			failure: function() {
				removeLoading($btn, 'Update');
				showMessage('Problem to contact server', true);
			}
		});
	});
});