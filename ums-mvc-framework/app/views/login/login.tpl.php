<div class="container col-md-7 text-center">
    <h1>Login</h1>
    <form id="login-form" action="/auth/login" method="POST">
    	<div class="form-group text-md-left">
    		<label for="user">Email/Username</label>
    		<input id="user" placeholder="Email or Username" class="form-control evidence-error send-ajax" type="text" name="user" required="required" autofocus="autofocus">
    	</div>
    	<input id="_xf" type="hidden" name="_xf" value="<?=$token?>">
    	<div class="form-group text-md-left">
    		<label for="pass">Password</label>
    		<input id="pass" placeholder="Password" class="form-control evidence-error send-ajax-crypt mb-1" type="password" name="pass" required="required">
    		<div class="text-left mx-1">
        		<a href="/auth/reset/password/req" class="link-danger">Forgot password</a>
    		</div>
    	</div>
    	<noscript>
    		<div class="container-fluid">
    			<h3 class="text-danger">ENABLE JAVASCRIPT TO LOGIN</h3>
    		</div>
    	</noscript>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button id="btn-login" class="btn btn-success px-3 py-1" type="submit">
	    		<i id="ico-btn" class="fas fa-sign-in-alt"></i>
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Login</span>
	    	</button>
    	</div>
    </form>
</div>