<div class="container-fluid">
    <div class="table-responsive col-md-6 mx-auto">
        <table class="table table-striped" id="users-table">
        	<tbody>
        		<tr>
        			<th class="align-middle" colspan="2">User: <?=${USER}->{USERNAME}?></th>
        		</tr>
        		<tr>
        			<td class="text-primary align-middle">ID</td>
        			<td class="align-middle"><?=${USER}->{USER_ID}?></td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">Full name</td>
        			<td class="align-middle"><?=${USER}->{NAME}?></td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">Username</td>
        			<td class="align-middle"><?=${USER}->{USERNAME}?></td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">Email</td>
        			<td class="align-middle"><?=${USER}->{EMAIL}?></td>
    			</tr>
    			<?php if (${VIEW_ROLE}): ?>
        			<tr>
            			<td class="text-primary align-middle">Role</td>
            			<td class="align-middle"><?=${USER}->{ROLE}?></td>
        			</tr>
    			<?php endif; ?>
    			<tr>
    				<td class="text-primary align-middle">Registration date</td>
    				<td class="align-middle"><?=${USER}->{REGISTRATION_DATETIME}?></td>
    			</tr>
    			<tr>
    				<td colspan="2" class="align-middle <?=${CLASS_ENABLE_ACC}?>"><?=${NO_ESCAPE.MESSAGE_ENABLE_ACC}.${NO_ESCAPE.MESSAGE_LOCK_ACC}?></td>
    			</tr>
        	</tbody>
        </table>
    </div>
    <div class="text-center container-fluid mx-auto my-3">
    	<div class="row justify-content-center">
    	    <?php if (${CAN_UPDATE_USER}): ?>
    	    	<a class="btn btn-warning mx-3 my-1" href="/<?=USER_ROUTE.'/'.${USER}->{USER_ID}.'/'.UPDATE_ROUTE?>"><i class="fa fa-pen fa-xs"></i> Update</a>
    	    <?php endif; ?>
    	    <?php if (${CAN_DELETE_USER}): ?>
    	    	<form id="delete-user-form" action="/<?=USER_ROUTE.'/'.DELETE_ROUTE?>" method="get">
    	    		<button id="btn-delete-user" class="btn btn-danger mx-3 my-1" type="submit">
    	    			<i id="ico-btn" class="fa fa-trash-alt fa-xs"></i>
	    				<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  						<span id="text-btn">Delete</span>
	    			</button>
    		    	<input id="<?=TOKEN?>" name="<?=CSRF_DELETE_USER?>" value="<?=${TOKEN}?>" type="hidden">
    		    	<input name="<?=USER_ID?>" value="<?=${USER}->{USER_ID}?>" class="send-ajax" type="hidden">
    	    	</form>
    	    <?php endif; ?>
    	    <?php if (${CAN_CHANGE_PASSWORD}): ?>
    	    	<a class="btn btn-primary mx-3 my-1" href="/<?=USER_ROUTE.'/'.${USER}->{USER_ID}.'/'.PASS_UPDATE_ROUTE?>"><i class="fas fa-key"></i> Change Password</a>
	    	<?php endif; ?>
    	</div>
    </div>
</div>