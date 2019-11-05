var nRoutes= $('#lstk').val();

function deleteInputRoute(nameLayout) {
	$('#form-route-' + nameLayout).remove();
}

function addInputRoute() {
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

	window.nRoutes++;

	$div.attr('id', 'form-route-' + window.nRoutes);
	$div.attr('class', 'form-group justify-content-center row input-route p-2');

	$labelRoute.attr('for', 'route-' + window.nRoutes);
	$labelRoute.attr('class', 'col-10 text-center h3 text-primary');
	$labelRoute.text('Route ' + window.nRoutes);
	$div.append($labelRoute);

	$inputRoute.attr('id', 'route-' + window.nRoutes);
	$inputRoute.attr('name', 'route-' + window.nRoutes);
	$inputRoute.attr('placeholder', 'Route');
	$inputRoute.attr('class', 'form-control evidence-error send-ajax col-10');
	$inputRoute.attr('type', 'text');
	$inputRoute.attr('required', 'requiredd');
	$div.append($inputRoute);

	$labelLastmod.attr('for', 'lastmod-' + window.nRoutes);
	$labelLastmod.attr('class', 'col-10 mt-3');
	$labelLastmod.text('Last modification');
	$div.append($labelLastmod);

	$inputLastmod.attr('id', 'lastmod-' + window.nRoutes);
	$inputLastmod.attr('name', 'lastmod-' + window.nRoutes);
	$inputLastmod.attr('placeholder', 'Last modification');
	$inputLastmod.attr('class', 'form-control evidence-error send-ajax col-10');
	$inputLastmod.attr('type', 'date');
	$div.append($inputLastmod);

	$labelPriority.attr('for', 'priority-' + window.nRoutes);
	$labelPriority.attr('class', 'col-10 mt-3');
	$labelPriority.text('Priority');
	$div.append($labelPriority);

	$inputPriority.attr('id', 'priority-' + window.nRoutes);
	$inputPriority.attr('name', 'priority-' + window.nRoutes);
	$inputPriority.attr('placeholder', 'Priority');
	$inputPriority.attr('class', 'form-control evidence-error send-ajax col-10');
	$inputPriority.attr('type', 'number');
	$inputPriority.attr('step', '0.1');
	$inputPriority.attr('min', '0.0');
	$inputPriority.attr('max', '1.0');
	$div.append($inputPriority);

	$labelChangefreq.attr('for', 'changefreq-' + window.nRoutes);
	$labelChangefreq.attr('class', 'col-10 mt-3');
	$labelChangefreq.text('Change frequency');
	$div.append($labelChangefreq);

	$selectChangefreq.attr('id', 'changefreq-' + window.nRoutes);
	$selectChangefreq.attr('name', 'changefreq-' + window.nRoutes);
	$selectChangefreq.attr('class', 'evidence-error send-ajax col-10');

	$option.attr('selected', 'selected');
	$selectChangefreq.append($option);
	
	listChangefreq.forEach(function(item, index) {
		$option = $(document.createElement('option'));
		$option.attr('value', item);
		$option.text(item.ucFirst());
		$selectChangefreq.append($option);
	});
	$div.append($selectChangefreq);

	$divBtn.attr('class', 'col-10 text-right my-3');
	$btn.attr('type', 'button');
	$btn.attr('class', 'btn btn-danger mt-0 btn-delete-input-route');
	$btn.attr('value', window.nRoutes);
	$btn.text('Delete');
	$divBtn.append($btn);
	$div.append($divBtn);

	$hr.attr('class', 'col-10');
	$div.append($hr);

	$divInputLast.after($div);
}

function clickListenerOnBtnDelete() {
	$('form button.btn-delete-input-route').click(function(event) {
		event.preventDefault();
		deleteInputRoute($(this).val());
	});
}

$(document).ready(function() {
	clickListenerOnBtnDelete();

	$('form button.btn-add-input-route').click(function(event) {
		event.preventDefault();
		addInputRoute();
	});
});