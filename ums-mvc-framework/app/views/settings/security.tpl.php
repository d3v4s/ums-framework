<div class="container col-md-7 text-left">
    <h1 class="text-center p-3">Security Settings</h1>
    <form id="settings-form" action="/<?=APP_SETTINGS_ROUTE.'/'.SECURITY.'/'.UPDATE_ROUTE?>" method="post" class="p-3">
        <div class="custom-control custom-switch">
			<input id="<?=BLOCK_CHANGE_IP?>" name="<?=BLOCK_CHANGE_IP?>" type="checkbox" class="custom-control-input send-ajax" value="on" <?=${NO_ESCAPE.BLOCK_CHANGE_IP}?>>
			<label for="<?=BLOCK_CHANGE_IP?>" class="custom-control-label">Block change ip</label>
        </div>
        <br>
        <div class="custom-control custom-switch">
			<input id="<?=ONLY_HTTPS?>" name="<?=ONLY_HTTPS?>" type="checkbox" class="custom-control-input send-ajax" value="on" <?=${NO_ESCAPE.ONLY_HTTPS}?>>
			<label for="<?=ONLY_HTTPS?>" class="custom-control-label">Redirect to https</label>
        </div>
        <br>
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
