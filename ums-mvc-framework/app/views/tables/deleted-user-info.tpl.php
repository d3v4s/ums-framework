<div class="container-fluid">
    <div class="table-responsive col-md-6 mx-auto">
        <table class="table table-striped" id="users-table">
        	<tbody>
        		<tr>
        			<th class="align-middle" colspan="2">Deleted user: <?=${USER}->{USERNAME}?></th>
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
        			<td class="align-middle">
        				<a href="<?=${SEND_EMAIL_LINK}.${USER}->{EMAIL}?>">
	        				<?=${USER}->{EMAIL}?>
        				</a>
        			</td>
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
    				<td class="text-primary align-middle">Delete date</td>
    				<td class="align-middle"><?=${USER}->{DELETE_DATETIME}?></td>
    			</tr>
        	</tbody>
        </table>
    </div>
    <div class="text-center container-fluid mx-auto my-3">
    	<div class="row justify-content-center">
    		<?php if (${CAN_RESTORE_USER}): ?>
    	    	<form id="restore-user-form" action="/ums/table/action/<?=DELETED_USER_TABLE?>/restore" method="post">
    	    		<button id="btn-restore-user" class="btn btn-danger mx-3 my-1" type="submit">
    	    			<i class="fa fa-trash-restore-alt fa-xs ico-btn"></i>
	    				<span class="spinner-border spinner-border-sm d-none spinner" role="status" aria-hidden="true"></span>
  						<span class="text-btn">Restore</span>
	    			</button>
    		    	<input id="<?=RESTORE_TOKEN?>" name="<?=CSRF_RESTORE_USER?>" value="<?=${RESTORE_TOKEN}?>" type="hidden">
    		    	<input name="<?=USER_ID?>" value="<?=${USER}->{USER_ID}?>" class="send-ajax" type="hidden">
    	    	</form>
    	    <?php endif; ?>
    	</div>
    </div>
</div>