<div class="container col-md-7 text-center">
    <h1>User: <?=$user->username?></h1>
    <form id="user-update-form" action="/ums/user/update" method="POST">
    	<div class="form-group text-md-left">
    		<label for="name">Full Name</label>
    		<input id="name" name="name" value="<?=$user->name?>" placeholder="Full name" class="form-control validate-name evidence-error send-ajax" type="text" required="required" autofocus="autofocus">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="username">Username</label>
    		<input id="username" name="username" value="<?=$user->username?>" placeholder="Username" class="form-control validate-username evidence-error send-ajax" type="text" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="email">Email</label>
    		<input id="email" name="email" value="<?=$user->email?>" placeholder="Email" class="form-control validate-email evidence-error send-ajax" type="email" required="required">
    	</div>
    	<?php if (isUserAdmin()): ?>
        	<div class="form-group text-left my-3">
        		<label for="role">Role</label>
        		<select id="role" name="role" class="send-ajax evidence-error">
        			<?php foreach ($userRoles as $role): ?>
    	    			<option <?=$role === $user->roletype ? 'selected="selected"' : ''?> value="<?=$role?>"><?=ucfirst($role)?></option>
        			<?php endforeach; ?>
        		</select>
        	</div>
        	<div class="custom-control custom-switch text-left">
				<input id="enabled" name="enabled" type="checkbox" class="custom-control-input send-ajax" value="true" <?=$_checkedEnableAccount?> >
				<label class="custom-control-label" for="enabled">Enabled</label>
            </div>
    	<?php endif; ?>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
    		<a class="btn btn-primary px-3 py-1 mx-2 my-2" href="/ums/user/<?=$user->id?>"><i class="fas fa-info"></i> Info</a>
    		<?php if (userCanChangePasswords()): ?>
    			<a class="btn btn-warning px-3 py-1 mx-2 my-2" href="/ums/user/<?=$user->id?>/update/pass"><i class="fas fa-key"></i> Change Password</a>
			<?php endif; ?>
	    	<button id="btn-update" class="btn btn-success px-3 py-1 mx-2 my-2" type="submit">
	    		<i id="ico-btn" class="fas fa-check"></i>
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Update</span>
	    	</button>
    	</div>
    	<input id="_xf" type="hidden" name="_xf" value="<?=$token?>" class="send-ajax">
    	<input id="id" type="hidden" name="id" value="<?=$user->id?>" class="send-ajax">
    </form>
</div>