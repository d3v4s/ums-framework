<div class="container-fluid">
    <div class="table-responsive col-md-6 mx-auto">
        <table class="table table-striped" id="users-table">
        	<tbody>
        		<tr>
        			<th class="align-middle" colspan="2">Session ID: <?=${SESSION}->{SESSION_ID}?></th>
        		</tr>
        		<tr>
        			<td class="text-primary align-middle">User ID</td>
        			<td class="align-middle"><?=${SESSION}->{USER_ID_FRGN}?></td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">Full name</td>
        			<td class="align-middle"><?=${SESSION}->{NAME}?></td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">Username</td>
        			<td class="align-middle">
        				<?php if (isset(${SESSION}->{USERNAME})): ?>
        					<a href="/ums/table/get/<?=USERS_TABLE.'/'.${SESSION}->{USER_ID_FRGN}?>"><?=${SESSION}->{USERNAME}?></a>
    					<?php else: ?>
    						<a href="/ums/table/get/<?=DELETED_USER_TABLE.'/'.${SESSION}->{USER_ID_FRGN}?>" class="text-danger">DELETE: <?=${SESSION}->{USER_ID_FRGN}?></a>
    					<?php endif; ?>
					</td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">Email</td>
        			<td class="align-middle">
        				<a href="<?=${SEND_EMAIL_LINK}.${SESSION}->{EMAIL}?>">
	        				<?=${SESSION}->{EMAIL}?>
        				</a>
        			</td>
    			</tr>
    			<tr>
    				<td class="text-primary align-middle">IP address</td>
    				<td class="align-middle"><?=${SESSION}->{IP_ADDRESS}?></td>
    			</tr>
    			<tr>
    				<td class="text-primary align-middle">Expire datetime</td>
    				<td class="align-middle">
    					<?=${SESSION}->{EXPIRE_DATETIME}?>
    					<br>
        				<?php if (${IS_EXPIRED}): ?>
        					<span class="text-danger"><?=${MESSAGE_EXPIRE}?></span>
        				<?php else: ?>
        					<span class="text-success"><?=${MESSAGE_EXPIRE}?></span>
        				<?php endif;?>
    				</td>
    			</tr>
    			<tr>
    				<td class="align-middle" colspan="2">
        				<?php if (isset(${SESSION}->{SESSION_TOKEN})): ?>
        					<span class="text-primary">TOKEN</span>
    					<?php else: ?>
    						<span class="text-danger">NO TOKEN</span>
						<?php endif; ?>
    				</td>
    			</tr>
        	</tbody>
        </table>
    </div>
    <div class="text-center container-fluid mx-auto my-3">
    	<div class="row justify-content-center">
    		<?php if (${CAN_REMOVE_SESSION} && !${IS_EXPIRED}): ?>
    	    	<form id="invalidate-form" action="/ums/table/action/<?=SESSIONS_TABLE?>/invalidate" method="post">
    	    		<button id="btn-invalidate" class="btn btn-danger mx-3 my-1" type="submit">
    	    			<i class="fa fa-user-times ico-btn"></i>
	    				<span class="spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  						<span class="text-btn">Invalidate Session</span>
	    			</button>
    		    	<input id="<?=INVALIDATE_TOKEN?>" name="<?=CSRF_INVALIDATE_SESSION?>" value="<?=${INVALIDATE_TOKEN}?>" type="hidden">
    		    	<input name="<?=SESSION_ID?>" value="<?=${SESSION}->{SESSION_ID}?>" class="send-ajax" type="hidden">
    	    	</form>
    	    <?php endif; ?>
    	</div>
    </div>
</div>