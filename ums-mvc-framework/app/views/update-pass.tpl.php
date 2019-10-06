<div class="container col-md-7 text-center mb-5 p-4">
    <h1>User: <?=$user->username?></h1>
    <form id="update-pass-form" action="/ums/user/update/pass" method="POST">
		<div class="form-group text-md-left">
    		<label for="pass">Password</label>
    		<input id="pass" name="pass"  placeholder="Password" class="form-control" type="password" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="cpass">Confirm password</label>
    		<input id="cpass" name="cpass" placeholder="Confirm password" class="form-control" type="password" required="required">
    	</div>
    	<div id="errors"></div>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button class="btn btn-success px-3 py-1" type="submit">Change</button>
    	</div>
    	<input type="hidden" name="_xf" value="<?=$token?>">
    	<input type="hidden" name="id" value="<?=$user->id?>">
    </form>
</div>

