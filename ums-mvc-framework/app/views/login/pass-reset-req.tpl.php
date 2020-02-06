<div class="container col-md-7 text-center">
    <h1><?=${LANG}['forgot_password']?></h1>
    <form id="reset-pass-req-form" action="/<?=PASS_RESET_REQ_ROUTE?>" method="post">
    	<div class="form-group text-md-left">
    		<label for="<?=EMAIL?>">Email</label>
    		<input id="<?=EMAIL?>" placeholder="Email" class="form-control evidence-error send-ajax validate-email" type="text" name="<?=EMAIL?>" required="required" autofocus="autofocus">
    	</div>
    	<input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_PASS_RESET_REQ?>" value="<?=${TOKEN}?>">
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button id="btn-reset-pass" class="btn btn-primary px-3 py-1" type="submit">
	    		<i class="fas fa-redo-alt ico-btn"></i>
	    		<span class="spinner-border spinner-border-sm d-none spinner" role="status" aria-hidden="true"></span>
  				<span class="text-btn">Reset password</span>
	    	</button>
    	</div>
    </form>
</div>