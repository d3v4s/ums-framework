<div class="container col-md-7 text-left">
    <h1 class="text-center p-3">App Settings</h1>
    <form id="settings-form" action="/<?=APP_SETTINGS_ROUTE.'/'.APP.'/'.UPDATE_ROUTE?>" method="post" class="p-3">
    	<div>
    		<a href="/<?=SITE_MAP_GENERATOR_ROUTE?>" class="btn btn-primary">Site Map Generator</a>
    	</div>
        <br><hr><br>
    	<div class="form-group">
	    	<label for="<?=DATE_FORMAT?>">Date format</label>
    		<input id="<?=DATE_FORMAT?>" name="<?=DATE_FORMAT?>" value="<?=${DATE_FORMAT}?>" placeholder="Date format" class="form-control evidence-error send-ajax" type="text" required="required">
    	</div>
    	<div class="form-group">
	    	<label for="<?=DATETIME_FORMAT?>">Datetime format</label>
    		<input id="<?=DATETIME_FORMAT?>" name="<?=DATETIME_FORMAT?>" value="<?=${DATETIME_FORMAT}?>" placeholder="Datetime format" class="form-control evidence-error send-ajax" type="text" required="required">
    	</div>
    	<br><hr><br>
    	<div class="form-group">
    		<label for="<?=SEND_EMAIL_FROM?>">Send Email From</label>
    		<input id="<?=SEND_EMAIL_FROM?>" name="<?=SEND_EMAIL_FROM?>" value="<?=${SEND_EMAIL_FROM}?>" placeholder="Send email from" class="form-control evidence-error send-ajax" type="email" required="required">
    	</div>
    	<br>
    	<div class="form-group text-right mr-md-4 mt-md-4">
	    	<button id="btn-save" class="btn btn-success px-3 py-1 mx-2 my-2" type="submit">
	    		<i id="ico-btn" class="fas fa-check"></i>
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Save</span>
	    	</button>
    	</div>
    	<input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_SETTINGS?>" value="<?=${TOKEN}?>">
    </form>
</div>
