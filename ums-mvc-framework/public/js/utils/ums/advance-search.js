/* function to manage inputs for params of table */
function handlerTableParams($tableParams) {
	$tableParams.find('input[type=checkbox][data-toggle=param]').each(function(index, item) {
		/* get input and target */
		var $item = $(item),
			$target = $($item.data('target'))
		;

		handlerItem($item, $target);
	});
}

/* function to disable or enable item */
function handlerItem($item, $target) {
	if ($item.is(':checked')) $target.removeAttr('disabled');
	else $target.attr('disabled', 'disabled');
}

$(document).ready(function() {
	/* add listener to change params by table select */
	$('#advance-search-form div.table-list input[type=radio][data-toggle=table]').each(function(index, item) {
		/* get input and target */
		var $item = $(item),
			$target = $($item.data('target'))
		;

		handlerTableParams($target);
		$item.change(function() {
			$('.table-params .param').attr('disabled', 'disabled');
			handlerTableParams($target);
			$('.collapse.table-params').collapse('hide');
			if ($item.is(':checked')) $target.collapse('show');
		});
	});

	/* add listener to disbale or enable input by checkbox */
	$('#advance-search-form div.table-params input[type=checkbox][data-toggle=param]').each(function(index, item) {
		/* get input and target */
		var $item = $(item),
			$target = $($item.data('target'));

		/* set change listener to enable/disable input */
		$item.change(function() {
			handlerItem($item, $target);
		})
	});
});
