<div class="container col-md-7 text-center mb-5 p-4">
    <h1>Signup</h1>
    <form id="signup-form" action="/auth/signup" method="POST">
    	<div class="form-group text-md-left">
    		<label for="name">Full Name <span class="text-red">*</span></label>
    		<input id="name" placeholder="Full name" class="form-control" type="text" name="name" required="required" autofocus="autofocus">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="username">Username <span class="text-red">*</span></label>
    		<input id="username" name="username" placeholder="Username" class="form-control" type="text" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="email">Email <span class="text-red">*</span></label>
    		<input id="email" name="email" placeholder="Email" class="form-control" type="email" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="pass">Password <span class="text-red">*</span></label>
    		<input id="pass" name="pass"  placeholder="Password" class="form-control" type="password" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="cpass">Confirm password <span class="text-red">*</span></label>
    		<input id="cpass" name="cpass" placeholder="Confirm password" class="form-control" type="password" required="required">
    	</div>
    	<p class="text-red text-left text-small">* Required</p>
    	<div id="errors"></div>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button class="btn btn-success px-3 py-1" type="submit">Signup</button>
    	</div>
    	<input type="hidden" name="_xf" value="<?=$token?>">
    </form>
</div>

