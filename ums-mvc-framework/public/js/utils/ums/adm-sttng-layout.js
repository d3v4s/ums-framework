var newLayout = 0;

function deleteInputLayout(nameLayout) {
	$('#form-layout-' + nameLayout).remove();
}

function addInputNewLayout() {
	const $divInputLast = $('#layout-settings-form').find('div.input-new-layout').last()

	var $div = $(document.createElement('div')),
		$label = $(document.createElement('label')),
		$inputName = $(document.createElement('input')),
		$inputVal = $(document.createElement('input')),
		$divBtn = $(document.createElement('div')),
		$btn = $(document.createElement('button'));

	window.newLayout++;

	$div.attr('id', 'form-layout-' + window.newLayout);
	$div.attr('class', 'form-group justify-content-center row input-new-layout p-2');

	$label.attr('for', 'name-layout-' + window.newLayout);
	$label.attr('class', 'col-md-10');
	$label.text('New Layout ' + window.newLayout);
	$div.append($label);

	$inputName.attr('id', 'name-layout-' + window.newLayout);
	$inputName.attr('name', 'name-layout-' + window.newLayout);
	$inputName.attr('placeholder', 'Name layout');
	$inputName.attr('class', 'form-control evidence-error send-ajax col-md-4 m-2 mb-0');
	$inputName.attr('type', 'text');
	$inputName.attr('required', 'required');
	$div.append($inputName);

	$inputVal.attr('id', 'val-layout-' + window.newLayout);
	$inputVal.attr('name', 'val-layout-' + window.newLayout);
	$inputVal.attr('placeholder', 'Value layout');
	$inputVal.attr('class', 'form-control evidence-error send-ajax col-md-4 m-2 mb-0');
	$inputVal.attr('type', 'text');
	$inputVal.attr('required', 'required');
	$div.append($inputVal);

	$divBtn.attr('class', 'col-md-8 col-sm-10 text-right');
	$btn.attr('type', 'button');
	$btn.attr('class', 'btn btn-link link-danger mt-0 p-0 btn-delete-input-layout');
	$btn.attr('value', window.newLayout);
	$btn.text('Delete');
	$divBtn.append($btn);
	$div.append($divBtn);

	$div.append('<br><br>');
	$divInputLast.after($div);
}

function clickListenerOnBtnDelete() {
	$('#layout-settings-form button.btn-delete-input-layout').click(function(event) {
		event.preventDefault();
		deleteInputLayout($(this).val());
	});
}

$(document).ready(function (){
	clickListenerOnBtnDelete();

	$('#layout-settings-form button.btn-add-input-layout').click(function(event) {
		event.preventDefault();
		addInputNewLayout();
		clickListenerOnBtnDelete();
	});

	$('#layout-settings-form').on('submit', function(event) {
		const $btn = $(this).find('#btn-save'),
			$xf = $(this).find('#_xf.send-ajax'),
			data = $(this).find('.send-ajax').serialize();

		event.preventDefault();
		showLoading($btn);
		$.ajax({
			method: 'post',
			data: data,
			url: '/ums/app/settings/layout/update',
			success: function(response) {
    			removeLoading($btn, 'Save');
    			try {
    				const settingsRes = JSON.parse(response);
    				
    				showMessage(settingsRes.message, !settingsRes.success);
    				
    				if (!settingsRes.success) {
    					focusError(settingsRes);
    					$xf.val(settingsRes.ntk);
    				}
    				else setTimeout(function() {location.reload();}, 2000); 
				} catch (e) {
					showMessage('Settings update failed', true);
				}

			},
			failure: function() {
				removeLoading($btn, 'Save');
				showMessage('Problem to contact server', true);
			}
		});
	});

});