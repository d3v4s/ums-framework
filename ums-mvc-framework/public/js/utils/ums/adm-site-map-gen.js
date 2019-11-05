$(document).ready(function (){
	$('#site-map-generator-form').on('submit', function(event) {
		const $btn = $(this).find('#btn-generate'),
			$xf = $(this).find('#_xf.send-ajax'),
			data = $(this).find('.send-ajax').serialize();

		event.preventDefault();
		showLoading($btn);
		$.ajax({
			method: 'post',
			data: data,
			url: '/ums/generator/site/map',
			success: function(response) {
    			removeLoading($btn, 'Generate');
    			try {
    				const sitemapRes = JSON.parse(response);
    				
    				showMessage(sitemapRes.message, !sitemapRes.success);
    				if (!sitemapRes.success) focusError(sitemapRes);
    				else setTimeout(redirect, 2000, '/ums/generator/site/map/update');

    				$xf.val(sitemapRes.ntk);
				} catch (e) {
					showMessage('Generation sitemap failed', true);
				}
			},
			failure: function() {
				removeLoading($btn, 'Generate');
				showMessage('Problem to contact server', true);
			}
		});
	});

});