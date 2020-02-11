<div class="container col-md-7 text-left">
    <h1 class="text-center p-3">RSA Key Pair Settings</h1>
    <form id="settings-form" action="/ums/app/settings/rsa/update" method="post" class="p-3">
    	<div class="form-group">
    		<label for="<?=RSA_PRIV_KEY_FILE?>">RSA private key file</label>
    		<input id="<?=RSA_PRIV_KEY_FILE?>" name="<?=RSA_PRIV_KEY_FILE?>" value="<?=${RSA_PRIV_KEY_FILE}?>" placeholder="RSA private key file" class="form-control evidence-error send-ajax" type="text">
    		<span class="text-muted">Insert your file on: <?=${PATH_PRIV_KEY}?></span>
    	</div>
    	<div class="form-group text-right mr-md-4 mt-md-4">
	    	<button id="btn-save" class="btn btn-success px-3 py-1 mx-2 my-2" type="submit">
	    		<i id="ico-btn" class="fas fa-check"></i>
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Save</span>
	    	</button>
    	</div>
    	<input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_SETTINGS?>" value="<?=${TOKEN}?>">
    </form>
    <div class="row mt-5">
    	<a href="/ums/generator/rsa?>" class="btn btn-primary m-2">RSA Key Pair Generator</a>
    	<form id="rsa-gen-save-form" action="/ums/generator/rsa/save" method="post">
    		<input id="<?=RSA_TOKEN?>" type="hidden" name="<?=CSRF_GEN_SAVE_RSA?>" value="<?=${RSA_TOKEN}?>" class="send-ajax">
	    	<button id="btn-gen-save-key" type="submit" class="btn btn-primary m-2">
	    		<i class="fas fa-save ico-btn"></i>
	    		<span class="spinner-border spinner-border-sm d-none spinner" role="status" aria-hidden="true"></span>
  				<span class="text-btn">Generate and Save Key</span>
	    	</button>
    	</form>
    </div>
</div>