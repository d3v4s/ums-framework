$(document).ready(function() {
	/* add listener for change inputs */
	$('#advance-search-form input[type=radio][data-toggle=table]').each(function(index, item) {
		/* get input and target */
		var $item = $(item),
			$target = $($item.data('target'));

		$item.change(function() {
//			$('input[type=radio][name="' + item.name + '"]').on('change', function() {
			$('.table-param .search-param').attr('disabled', 'disabled');
			$('.collapse.table-param').collapse('hide');
			if ($item.is(':checked')) $target.collapse('show');
		});
	});

	$('#advance-search-form div.table-param input[type=checkbox][data-toggle=param]').each(function(index, item) {
		/* get input and target */
		var $item = $(item),
			$target = $($item.data('target'));

		console.log($target);
		/* set change listener to enable/disable input */
		$item.change(function() {
			console.log($item);
			console.log($(this));
			console.log($target);
			console.log($item.is(':checked'));
			if ($item.is(':checked')) $target.removeAttr('disabled');
			else $target.attr('disabled', 'disabled');
		})
	});
});
