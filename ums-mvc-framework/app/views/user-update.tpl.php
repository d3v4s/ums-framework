<div class="container col-md-7 text-center mb-5 p-4">
    <h1>User: <?=$user->username?></h1>
    <form id="user-update-form" action="/ums/user/update" method="POST">
    	<div class="form-group text-md-left">
    		<label for="name">Full Name</label>
    		<input id="name" name="name" value="<?=$user->name?>" placeholder="Full name" class="form-control" type="text" required="required" autofocus="autofocus">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="username">Username</label>
    		<input id="username" name="username" value="<?=$user->username?>" placeholder="Username" class="form-control" type="text" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="email">Email</label>
    		<input id="email" name="email" value="<?=$user->email?>" placeholder="Email" class="form-control" type="email" required="required">
    	</div>
    	<?php if (isUserAdmin()): ?>
        	<div class="form-group text-md-left my-3">
        		<label for="role">Role</label>
        		<select id="role" name="role">
        			<option <?=$user->roletype === 'user' ? 'selected' : ''?> value="user">User</option>
        			<option <?=$user->roletype === 'editor' ? 'selected' : ''?> value="editor">Editor</option>
        			<option <?=$user->roletype === 'admin' ? 'selected' : ''?> value="admin">Admin</option>
        		</select>
        	</div>
        	<!-- <div class="form-group text-md-left">
        		<select id="enabled" name="enabled">
        			<option < ?=$user->enabled ? 'selected' : ''?> value="1">Enabled</option>
        			<option < ?=$user->enabled ? '' : 'selected'?> value="0">Disabled</option>
        		</select>
        	</div>
        	 -->
        	<div class="custom-control custom-switch text-md-left">
				<input id="enabled" name="enabled" type="checkbox" class="custom-control-input" value="true">
				<label class="custom-control-label" for="enabled">Enabled</label>
            </div>
    	<?php endif;?>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button class="btn btn-success px-3 py-1" type="submit">Update</button>
    	</div>
    	<input type="hidden" name="_xf" value="<?=$token?>">
    	<input type="hidden" name="id" value="<?=$user->id?>">
    </form>
</div>

