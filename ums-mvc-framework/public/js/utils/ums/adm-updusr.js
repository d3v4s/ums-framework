$(document).ready(function (){
	$('#user-update-form').on('submit', function(event) {
		const $btn = $(this).find('#btn-update'),
			$xf = $(this).find('#_xf.send-ajax'),
			data = $(this).find('.send-ajax').serialize();

		event.preventDefault();
		showLoading($btn);
		$.ajax({
			method: 'post',
			data: data,
			url: '/ums/user/update',
			success: function(response) {
    			removeLoading($btn, 'Update');
    			try {
    				const userRes = JSON.parse(response);
    				
    				showMessage(userRes.message, !userRes.success);
    				if (userRes.success) setTimeout(redirect, 2000, '/ums/user/' + userRes.userId);
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