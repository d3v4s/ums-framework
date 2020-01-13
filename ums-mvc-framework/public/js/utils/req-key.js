/* sucess function XML HTTP req */
funcSuccess = function(response) {
	/* if success */
	if (response.success) {
		/* set public key */
		window.keyN = response.keyN;
		window.keyE = response.keyE;
	/* else show error message */
	} else showMessage(response.message, true);
};

sendAjaxReq('/app/config/get/key/json', '', $('#_kxt'), funcSuccess);
