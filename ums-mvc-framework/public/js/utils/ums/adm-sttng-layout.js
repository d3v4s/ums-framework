/* var to count layouts */ 
var newLayout = 0;

/* function to delete a input layout */
function deleteInputLayout(nameLayout) {
	$('#form-layout-' + nameLayout).remove();
}

/* function to add a input layuot */
function addInputNewLayout() {
	/* define const last input */
	const $divInputLast = $('#layout-settings-form').find('div.input-new-layout').last()

	/* init and define vars */
	var $div = $(document.createElement('div')),
		$label = $(document.createElement('label')),
		$inputName = $(document.createElement('input')),
		$inputVal = $(document.createElement('input')),
		$divBtn = $(document.createElement('div')),
		$btn = $(document.createElement('button'));

	/* increment count layouts */
	window.newLayout++;

	/* set attribute for div container */
	$div.attr('id', 'form-layout-' + window.newLayout);
	$div.attr('class', 'form-group justify-content-center row input-new-layout p-2');

	/* set attribute for label and append it on div */
	$label.attr('for', 'name-layout-' + window.newLayout);
	$label.attr('class', 'col-md-10');
	$label.text('New Layout ' + window.newLayout);
	$div.append($label);

	/* set attribute for input name and append it on div */
	$inputName.attr('id', 'name-layout-' + window.newLayout);
	$inputName.attr('name', 'name-layout-' + window.newLayout);
	$inputName.attr('placeholder', 'Name layout');
	$inputName.attr('class', 'form-control evidence-error send-ajax col-md-4 m-2 mb-0');
	$inputName.attr('type', 'text');
	$inputName.attr('required', 'required');
	$div.append($inputName);

	/* set attribute for input value and append it on div */
	$inputVal.attr('id', 'val-layout-' + window.newLayout);
	$inputVal.attr('name', 'val-layout-' + window.newLayout);
	$inputVal.attr('placeholder', 'Value layout');
	$inputVal.attr('class', 'form-control evidence-error send-ajax col-md-4 m-2 mb-0');
	$inputVal.attr('type', 'text');
	$inputVal.attr('required', 'required');
	$div.append($inputVal);

	/* set attribute for button and append it on div */
	$divBtn.attr('class', 'col-md-8 col-sm-10 text-right');
	$btn.attr('type', 'button');
	$btn.attr('class', 'btn btn-link link-danger mt-0 p-0 btn-delete-input-layout');
	$btn.attr('value', window.newLayout);
	$btn.text('Delete');
	$divBtn.append($btn);
	$div.append($divBtn);

	/* append div after last input layputs */
	$div.append('<br><br>');
	$divInputLast.after($div);
}

/* function to add listener on button delete */
function clickListenerOnBtnDelete() {
	$('#layout-settings-form button.btn-delete-input-layout').click(function(event) {
		event.preventDefault();
		deleteInputLayout($(this).val());
	});
}

$(document).ready(function (){
	clickListenerOnBtnDelete();

	/* click event to add new input layout */
	$('#layout-settings-form button.btn-add-input-layout').click(function(event) {
		event.preventDefault();
		addInputNewLayout();
		clickListenerOnBtnDelete();
	});

	/* function to send XML HTTP request when submit logout form */
	$('#layout-settings-form').on('submit', function(event) {
		/* get button, token, and serialize data */
		const $xf = $(this).find('#_xf'),
			$btn = $(this).find('#btn-save'),
			data = $(this).find('.send-ajax').serialize();

		/* block default submit form and show loading */
		event.preventDefault();
		showLoading($btn);

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, 'Save');
			try {
				showMessage(response.message, !response.success);
				if (!response.success) {
					focusError(response);
					$xf.val(response.ntk);
				}
				else setTimeout(function() {location.reload();}, 2000); 
			} catch (e) {
				showMessage('Settings update failed', true);
			}

		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Save');
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq('/ums/app/settings/layout/update', data, $xf.val(), funcSuccess, funcFail);
	});

});