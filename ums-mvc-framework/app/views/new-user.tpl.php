<div class="container col-md-7 text-center mb-5 p-4">
    <h1>New User</h1>
    <form id="newuser-form" action="/ums/user/new" method="POST">
    	<div class="form-group text-md-left">
    		<label for="name">Full Name</label>
    		<input id="name" placeholder="Full name" class="form-control" type="text" name="name" required="required" autofocus="autofocus">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="username">Username</label>
    		<input id="username" name="username" placeholder="Username" class="form-control" type="text" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="email">Email</label>
    		<input id="email" name="email" placeholder="Email" class="form-control" type="email" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="pass">Password</label>
    		<input id="pass" name="pass"  placeholder="Password" class="form-control" type="password" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="cpass">Confirm password</label>
    		<input id="cpass" name="cpass" placeholder="Confirm password" class="form-control" type="password" required="required">
    	</div>
    	<div class="form-group text-md-left my-3">
    		<label for="role">Role</label>
    		<select id="role" name="role">
    			<option selected value="user">User</option>
    			<option value="editor">Editor</option>
    			<option value="admin">Admin</option>
    		</select>
    	</div>
    	<div class="custom-control custom-switch text-md-left">
			<input id="enabled" name="enabled" type="checkbox" class="custom-control-input" value="true">
			<label class="custom-control-label" for="enabled">Enabled</label>
        </div>
    	<div id="errors"></div>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button class="btn btn-success px-3 py-1" type="submit">Save</button>
    	</div>
    	<input type="hidden" name="_xf" value="<?=$token?>">
    </form>
</div>

