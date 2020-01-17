/* var to count routes */
var nRoutes= $('#lstk').val();

/* function to delete a input for a route */
function deleteInputRoute(nameLayout) {
	$('#form-route-' + nameLayout).remove();
}

/* function to add new input for a route */
function addInputRoute() {
	/* define const last input and list of change frequecies */
	const $divInputLast = $('form').find('div.input-route').last(),
		listChangefreq = [
			'always',
			'hourly',
			'daily',
			'weekly',
			'monthly',
			'yearly',
			'never'
		];

	/* init and define a vars */
	var $div = $(document.createElement('div')),
		$labelRoute = $(document.createElement('label')),
		$inputRoute= $(document.createElement('input')),
		$labelLastmod = $(document.createElement('label')),
		$inputLastmod = $(document.createElement('input')),
		$labelPriority = $(document.createElement('label')),
		$inputPriority = $(document.createElement('input')),
		$labelChangefreq = $(document.createElement('label')),
		$selectChangefreq = $(document.createElement('select')),
		$option = $(document.createElement('option')),
		$divBtn = $(document.createElement('div')),
		$btn = $(document.createElement('button')),
		$hr = $(document.createElement('hr'));

	/* increment count routes */
	window.nRoutes++;

	/* set attribute for div container */
	$div.attr('id', 'form-route-' + window.nRoutes);
	$div.attr('class', 'form-group justify-content-center row input-route p-2');

	/* set attribute of label and append it on div */
	$labelRoute.attr('for', 'route-' + window.nRoutes);
	$labelRoute.attr('class', 'col-10 text-center h3 text-primary');
	$labelRoute.text('Route ' + window.nRoutes);
	$div.append($labelRoute);

	/* set attribute of input route and append it on div */
	$inputRoute.attr('id', 'route-' + window.nRoutes);
	$inputRoute.attr('name', 'route-' + window.nRoutes);
	$inputRoute.attr('placeholder', 'Route');
	$inputRoute.attr('class', 'form-control evidence-error send-ajax col-10');
	$inputRoute.attr('type', 'text');
	$inputRoute.attr('required', 'requiredd');
	$div.append($inputRoute);

	/* set attribute of label and append it on div */
	$labelLastmod.attr('for', 'lastmod-' + window.nRoutes);
	$labelLastmod.attr('class', 'col-10 mt-3');
	$labelLastmod.text('Last modification');
	$div.append($labelLastmod);

	/* set attribute of input last modify and append it on div */
	$inputLastmod.attr('id', 'lastmod-' + window.nRoutes);
	$inputLastmod.attr('name', 'lastmod-' + window.nRoutes);
	$inputLastmod.attr('placeholder', 'Last modification');
	$inputLastmod.attr('class', 'form-control evidence-error send-ajax col-10');
	$inputLastmod.attr('type', 'date');
	$div.append($inputLastmod);

	/* set attribute of label and append it on div */
	$labelPriority.attr('for', 'priority-' + window.nRoutes);
	$labelPriority.attr('class', 'col-10 mt-3');
	$labelPriority.text('Priority');
	$div.append($labelPriority);

	/* set attribute of input priority and append it on div */
	$inputPriority.attr('id', 'priority-' + window.nRoutes);
	$inputPriority.attr('name', 'priority-' + window.nRoutes);
	$inputPriority.attr('placeholder', 'Priority');
	$inputPriority.attr('class', 'form-control evidence-error send-ajax col-10');
	$inputPriority.attr('type', 'number');
	$inputPriority.attr('step', '0.1');
	$inputPriority.attr('min', '0.0');
	$inputPriority.attr('max', '1.0');
	$div.append($inputPriority);

	/* set attribute of label and append it on div */
	$labelChangefreq.attr('for', 'changefreq-' + window.nRoutes);
	$labelChangefreq.attr('class', 'col-10 mt-3');
	$labelChangefreq.text('Change frequency');
	$div.append($labelChangefreq);

	/* set attribute of select chenge frequecies */
	$selectChangefreq.attr('id', 'changefreq-' + window.nRoutes);
	$selectChangefreq.attr('name', 'changefreq-' + window.nRoutes);
	$selectChangefreq.attr('class', 'evidence-error send-ajax col-10');

	/* append empty option and select it */
	$option.attr('selected', 'selected');
	$selectChangefreq.append($option);

	/* append list options on selct */
	listChangefreq.forEach(function(item, index) {
		$option = $(document.createElement('option'));
		$option.attr('value', item);
		$option.text(item.ucFirst());
		$selectChangefreq.append($option);
	});
	/* append select on div */
	$div.append($selectChangefreq);

	/* set attibute of button and append it on div */
	$divBtn.attr('class', 'col-10 text-right my-3');
	$btn.attr('type', 'button');
	$btn.attr('class', 'btn btn-danger mt-0 btn-delete-input-route');
	$btn.attr('value', window.nRoutes);
	$btn.text('Delete');
	$divBtn.append($btn);
	$div.append($divBtn);

	/* append hr on div */
	$hr.attr('class', 'col-10');
	$div.append($hr);

	/* append div after last input route */
	$divInputLast.after($div);
}

/* function to add listener for click on delete button */
function clickListenerOnBtnDelete() {
	$('form button.btn-delete-input-route').click(function(event) {
		event.preventDefault();
		deleteInputRoute($(this).val());
	});
}

$(document).ready(function() {
	clickListenerOnBtnDelete();

	/* function click event on add input route boutton */
	$('form button.btn-add-input-route').click(function(event) {
		event.preventDefault();
		addInputRoute();
	});

	/* submit event on sitemap genrator form to send XML HTTP request */
	$('#sitemap-generator-form').on('submit', function(event) {
		/* get button, token, and serialize data */
		const $xf = $(this).find('#_xf'),
			actionUrl = $(this).attr('action'),
			$btn = $(this).find('#btn-generate'),
			data = $(this).find('.send-ajax').serialize();

		/* block default submit form and disable button */
		event.preventDefault();
		showLoading($btn);

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, 'Generate');
			try {
				showMessage(response.message, !response.success);
				if (!response.success) focusError(response);
				else setTimeout(redirect, 2000, response.redirect_to);

				if (response.ntk !== undefined) $xf.val(response.ntk);
			} catch (e) {
				showMessage('Generation sitemap failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Generate');
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq(actionUrl, data, $xf, funcSuccess, funcFail);
	});
});