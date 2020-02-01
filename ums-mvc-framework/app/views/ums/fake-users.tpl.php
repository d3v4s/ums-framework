<div class="container col-md-6 text-center">
    <h1 class="p-4">Add Fake Users</h1>
    <form id="fakeusr-generator-form" action="/<?=FAKE_USERS_ROUTE?>" method="post">
    	<div class="form-group text-md-left">
    		<label for="<?=N_USERS?>">N. fake users</label>
    		<input id="<?=N_USERS?>" name="<?=N_USERS?>" value="1" placeholder="N. Fake Users" class="form-control send-ajax evidence-error" type="number" required="required" autofocus="autofocus" min="1" max="<?=MAX_FAKE_USERS?>">
    	</div>
    	<br>
    	<div class="custom-control custom-switch text-left">
			<input id="<?=PENDING?>" name="<?=PENDING?>" type="checkbox" class="custom-control-input send-ajax" value="true">
			<label class="custom-control-label" for="<?=PENDING?>">Insert on pending table</label>
        </div>
        <br>
        <div class="custom-control custom-switch text-left">
			<input id="<?=ENABLED?>" name="<?=ENABLED?>" type="checkbox" class="custom-control-input send-ajax" value="true" checked="checked">
			<label class="custom-control-label" for="<?=ENABLED?>">Enable fake users</label>
        </div>
    	<div class="form-group text-right mr-md-4 mt-md-4 mt-3">
	    	<button id="btn-add" class="btn btn-success px-3 py-1" type="submit">
	    		<i class="ico-btn fas fa-check"></i>
	    		<span class="spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span class="text-btn">Add</span>
	    	</button>
    	</div>
    	<input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_ADD_FAKE_USER?>" value="<?=${TOKEN}?>">
    </form>
</div>