<div class="container col-md-7 text-center">
    <h1>User: <?=$user->username?></h1>
    <form id="update-pass-form" action="/ums/user/update/pass" method="POST">
		<div class="form-group text-md-left">
    		<label for="pass">Password</label>
    		<input id="pass" name="pass"  placeholder="Password" class="form-control confirm-password-1 send-ajax-crypt" type="password" required="required" autofocus="autofocus">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="cpass">Confirm password</label>
    		<input id="cpass" name="cpass" placeholder="Confirm password" class="form-control confirm-password-2 evidence-error send-ajax-crypt" type="password" required="required">
    	</div>
    	<noscript>
    		<div class="container-fluid">
    			<h3 class="text-danger">ENABLE JAVASCRIPT TO UPDATE PASSWORD</h3>
    		</div>
    	</noscript>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button id="btn-change" class="btn btn-success px-3 py-1" type="submit">
	    		<i id="ico-btn" class="fas fa-check"></i>
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Change</span>
	    	</button>
    	</div>
    	<input id="_xf" type="hidden" name="_xf" value="<?=$token?>" class="send-ajax">
    	<input id="id" type="hidden" name="id" value="<?=$user->id?>" class="send-ajax">
    </form>
</div>