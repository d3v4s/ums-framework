$(document).ready(function (){
	$('#site-map-update-form').on('submit', function(event) {
		const $btn = $(this).find('#btn-update'),
			$xf = $(this).find('#_xf.send-ajax'),
			data = $(this).find('.send-ajax').serialize();

		event.preventDefault();
		showLoading($btn);
		$.ajax({
			method: 'post',
			data: data,
			url: '/ums/generator/site/map',
			success: function(response) {
    			removeLoading($btn, 'Update');
    			try {
    				const sitemapRes = JSON.parse(response);
    				
    				showMessage(sitemapRes.message, !sitemapRes.success);
    				if (!sitemapRes.success) focusError(sitemapRes);
    				
    				$xf.val(sitemapRes.ntk);
				} catch (e) {
					showMessage('Sitemap update failed', true);
				}
			},
			failure: function() {
				removeLoading($btn, 'Update');
				showMessage('Problem to contact server', true);
			}
		});
	});

});