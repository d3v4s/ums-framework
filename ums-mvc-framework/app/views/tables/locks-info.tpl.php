<div class="container-fluid">
    <div class="table-responsive col-md-6 mx-auto">
        <table class="table table-striped" id="users-table">
        	<tbody>
        		<tr>
        			<th class="align-middle" colspan="2">Locks ID: <?=${USER}->{USER_LOCK_ID}?></th>
        		</tr>
        		<tr>
        			<td class="text-primary align-middle">User ID</td>
        			<td class="align-middle"><?=${USER}->{USER_ID_FRGN}?></td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">Username</td>
        			<td class="align-middle">
        				<a href="/<?=UMS_TABLES_ROUTE.'/'.GET_ROUTE.'/'.USERS_TABLE.'/'.${USER}->{USER_ID}?>"><?=${USER}->{USERNAME}?></a>
        			</td>
    			</tr>
    			<tr>
    				<td class="text-primary align-middle">Wrong password counter</td>
    				<td class="align-middle"><?=${USER}->{COUNT_WRONG_PASSWORDS}.'/'.MAX_WRONG_PASSWORDS?></td>
    			</tr>
    			<tr>
    				<td class="text-primary align-middle">Wrong password expire date</td>
    				<td class="align-middle"><?=${USER}->{EXPIRE_TIME_WRONG_PASSWORD}?></td>
    			</tr>
    			<tr>
    				<td class="text-primary align-middle">Locks counter</td>
    				<td class="align-middle"><?=${USER}->{COUNT_LOCKS}.'/'.MAX_LOCKS?></td>
    			</tr>
    			<tr>
    				<td class="text-primary align-middle">Lock expiration date</td>
    				<td class="align-middle <?=${IS_LOCK} ? 'text-danger' : ''?>"><?=(${USER}->{EXPIRE_LOCK} ?? 'NULL').'<br>'.${MESSAGE_LOCK_ACC}?></td>
    			</tr>
    			<tr>
    				<td colspan="2" class="align-middle <?=${USER}->{ENABLED} ? 'text-success' : 'text-danger'?>"><?=${MESSAGE_ENABLE_ACC}?></td>
    			</tr>
        	</tbody>
        </table>
    </div>
    <div class="text-center container-fluid mx-auto my-3">
    	<div class="row justify-content-center">
	    	<?php if (${CAN_UNLOCK_USER}): ?>
	    		<form id="lock-user-reset-form" action="/<?=UMS_TABLES_ROUTE.'/'.ACTION_ROUTE.'/'.USER_LOCK_TABLE.'/'.RESET_ROUTE?>" method="post">
    	    		<button id="btn-lock-user-reset" class="btn btn-warning mx-3 my-1" type="submit">
    	    			<i class="fas fa-undo ico-btn"></i>
        				<span class="spinner-border spinner-border-sm d-none spinner" role="status" aria-hidden="true"></span>
    					<span class="text-btn">Reset Locks</span>
        			</button>
    		    	<input id="<?=LOCKS_USER_RESET_TOKEN?>" name="<?=CSRF_LOCK_USER_RESET?>" value="<?=${LOCKS_USER_RESET_TOKEN}?>" type="hidden">
    		    	<input name="<?=USER_ID?>" value="<?=${USER}->{USER_ID}?>" class="send-ajax" type="hidden">
    	    	</form>
	    	<?php endif; ?>
	    	<a class="btn btn-primary mx-3 my-1" href="/<?=UMS_TABLES_ROUTE.'/'.GET_ROUTE.'/'.USERS_TABLE.'/'.${USER}->{USER_ID_FRGN}?>"><i class="fas fa-user"></i> View User</a>
    	</div>
    </div>
</div>