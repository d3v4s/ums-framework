<div class="container col-md-7 text-center p-3">
	<div class="container-fluid justify-content-right text-right">
    	<form action="/auth/logout" method="post">
			<input id="_xf-out" type="hidden" name="_xf-out" value="<?=$tokenLogout?>" class="send-ajax">
			<button id="btn-logout" class="btn btn-danger m-2" type="submit"><i id="ico-btn" class="fas fa-sign-out-alt"></i> Logout</button>
		</form>
	</div>
    <form id="user-update-form" action="/user/settings/update" method="POST">
    	<div class="form-group text-md-left">
    		<label for="name">Full name</label>
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
    	<?php if ($confirmNewEmail): ?>
    		<div class="form-group text-md-left">
    			<label for="new-email">New email</label>
    			<input id="new-email" value="<?=$user->new_email?>" class="form-control" type="email" readonly="readonly">
    			<div class="row ">
    				<div class="col-6 text-left">
            			<button id="btn-resend-email" class="btn btn-link link-primary p-0" type="button">Resend email</button>
    				</div>
    				<div class="col-6 text-right">
            			<button id="btn-delete-new-email" class="btn btn-link link-danger p-0" type="button">Delete</button>
    				</div>
    			</div>
    		</div>
    	<?php endif; ?>
    	<?php if (isNotSimpleUser()): ?>
        	<div class="form-group text-md-left my-3">
        		<label for="role">Role</label>
        		<select id="role" disabled="disabled">
        			<?php foreach ($userRoles as $role): ?>
    	    			<option <?=$role === $user->roletype ? 'selected="selected"' : ''?> value="<?=$role?>"><?=ucfirst($role)?></option>
        			<?php endforeach; ?>
        		</select>
        	</div>
    	<?php endif; ?>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
    		<a id="btn-delete" class="btn btn-danger px-3 py-1 mx-2 my-2" href="/user/settings/delete">
    			<i id="ico-btn" class="fas fa-trash-alt"></i>
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Delete Account</span>
			</a>
	    	<a class="btn btn-warning px-3 py-1 mx-2 my-2" href="/user/settings/pass"><i class="fas fa-key"></i> Change Password</a>
	    	<button id="btn-update" class="btn btn-success px-3 py-1 mx-2 my-2" type="submit">
	    		<i id="ico-btn" class="fas fa-check"></i>
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Update</span>
	    	</button>
    	</div>
    	<input id="_xf" type="hidden" name="_xf" value="<?=$token?>">
    </form>
</div>
