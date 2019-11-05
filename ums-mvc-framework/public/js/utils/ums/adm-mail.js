$(document).ready(function(){
	ClassicEditor.create(document.querySelector('#content')).catch(error => {
		console.error(error);
	});

	$('#send-email-form').on('submit', function(event) {
		const $btn = $(this).find('#btn-send'),
			$xf = $(this).find('#_xf.send-ajax');

		var rsa = new RSAKey(),
			to = $(this).find('#to.send-ajax-crypt').val(),
			subject = $(this).find('#subject.send-ajax-crypt').val(),
			content = $(this).find('#content.send-ajax-crypt').val();
		
		event.preventDefault();
		showLoading($btn);

		try {
			rsa.setPublic(window.keyN, window.keyE);
			to = rsa.encrypt(to);
			subject = rsa.encrypt(subject);
			content = rsa.encrypt(content);
		} catch (e) {
			removeLoading($btn, 'Send');
			showMessage('Send email failed', true);
			return;
		}

		$.ajax({
			method: 'post',
			data: {
				to: to,
				subject: subject,
				content: content,
				_xf: $xf.val()
			},
			url: '/ums/email/send',
			success: function(response) {
        		removeLoading($btn, 'Send');
        		try {
        			const emailRes = JSON.parse(response);
        			
        			showMessage(emailRes.message, !emailRes.success);
        			if (emailRes.success) setTimeout(redirect, 2000, '/');
        			else {
        				focusError(emailRes);
        				$xf.val(emailRes.ntk);
        			}
				} catch (e) {
					showMessage('Send email failed', true);
				}
			},
			failure: function() {
				removeLoading($btn, 'Send');
				showMessage('Problem to contact server', true);
			}
		});
	});
});