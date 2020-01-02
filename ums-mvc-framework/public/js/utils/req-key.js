$(document).ready(function() {
	/* sucess function XML HTTP req */
	funcSuccess = function(response) {
		try {
//			const res = JSON.parse(response);
			window.keyN = response.keyN;
			window.keyE = response.keyE;
			$('#_xf').val(response.ntk);
		} catch (e) {
			showMessage('Invalid server response', true);
		}
	};
	sendAjaxReq('/app/config/get/key/json', {}, $('#_xf').val(), funcSuccess);
});