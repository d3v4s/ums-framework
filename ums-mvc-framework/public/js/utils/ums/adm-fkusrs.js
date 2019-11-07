$(document).ready(function() {
	$('#fakeusr-generator-form').on('submit', function(event) {
		const $btn = $(this).find('#btn-add'),
			$xf = $(this).find('#_xf.send-ajax'),
			data = $(this).find('.send-ajax').serialize();

		event.preventDefault();
		showLoading($btn);
		$.ajax({
			method: 'post',
			data: data,
			url: '/ums/users/fake',
			success: function(response) {
				removeLoading($btn, 'Add');
				try {
					const fkusrRes = JSON.parse(response);
					showMessage(fkusrRes.message, !fkusrRes.success)
					if (fkusrRes.success) setTimeout(redirect, 2000, '/ums/users/');
					else $xf.val(fkusrRes.ntk);
				} catch (e) {
					showMessage('Add fake users failed', true);
				}
			},
			failure: function() {
				removeLoading($btn, 'Add');
				showMessage('Problem to contact server', true);
			}
		});
	});
});