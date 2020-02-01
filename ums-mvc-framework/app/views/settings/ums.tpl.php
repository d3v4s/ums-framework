<div class="container col-md-7 text-left">
    <h1 class="text-center p-3">UMS Settings</h1>
    <form id="settings-form" action="/<?=APP_SETTINGS_ROUTE.'/'.UMS.'/'.UPDATE_ROUTE?>" method="post" class="p-3">
    	<div class="form-group">
    		<label for="<?=DOMAIN_URL_LINK?>">Domain url used for enabler links</label>
    		<input id="<?=DOMAIN_URL_LINK?>" name="<?=DOMAIN_URL_LINK?>" value="<?=${DOMAIN_URL_LINK}?>" placeholder="Domain URL" class="form-control evidence-error send-ajax" type="url" required="required">
    	</div>
        <div class="form-group">
    		<label for="<?=ENABLER_EMAIL_FROM?>">Email validation from</label>
    		<input id="<?=ENABLER_EMAIL_FROM?>" name="<?=ENABLER_EMAIL_FROM?>" value="<?=${ENABLER_EMAIL_FROM}?>" placeholder="Enabler email from" class="form-control evidence-error send-ajax" type="email" required="required">
    	</div>
    	<div class="form-group">
    		<label for="<?=PASS_RESET_EMAIL_FROM?>">Password reset email from</label>
    		<input id="<?=PASS_RESET_EMAIL_FROM?>" name="<?=PASS_RESET_EMAIL_FROM?>" value="<?=${PASS_RESET_EMAIL_FROM}?>" placeholder="Password reset email from" class="form-control evidence-error send-ajax" type="email" required="required">
    	</div>
    	<div class="custom-control custom-switch">
			<input id="<?=REQUIRE_CONFIRM_EMAIL?>" name="<?=REQUIRE_CONFIRM_EMAIL?>" type="checkbox" class="custom-control-input send-ajax" value="on" <?=${NO_ESCAPE.REQUIRE_CONFIRM_EMAIL}?>>
			<label for="<?=REQUIRE_CONFIRM_EMAIL?>" class="custom-control-label">Require confirm email</label>
        </div>
    	<div class="form-group text-right mr-md-4 mt-md-4">
	    	<button id="btn-save" class="btn btn-success px-3 py-1 mx-2 my-2" type="submit">
	    		<i class="fas fa-check ico-btn"></i>
	    		<span class="spinner-border spinner-border-sm d-none spinner" role="status" aria-hidden="true"></span>
  				<span class="text-btn">Save</span>
	    	</button>
    	</div>
    	<input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_SETTINGS?>" value="<?=${TOKEN}?>">
    </form>
</div>
