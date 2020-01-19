<div class="container-fluid">
    <div class="table-responsive col-md-6 mx-auto">
        <table class="table table-striped" id="users-table">
        	<tbody>
        		<tr>
        			<th class="align-middle" colspan="2">Session: <?=${SESSION}->{SESSION_ID}?></th>
        		</tr>
        		<tr>
        			<td class="text-primary align-middle">User ID</td>
        			<td class="align-middle">
        				<?php if (isset(${SESSION}->{USERNAME})): ?>
        					<a href="/<?=USER_ROUTE.'/'.${SESSION}->{USER_ID_FRGN}?>"><?=${SESSION}->{USER_ID_FRGN}?></a>
    					<?php else: ?>
    						<a href="/<?=DELETED_USER_ROUTE.'/'.${SESSION}->{USER_ID_FRGN}?>" class="text-danger">DELETE: <?=${SESSION}->{USER_ID_FRGN}?></a>
    					<?php endif; ?>
    				</td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">Full name</td>
        			<td class="align-middle"><?=${SESSION}->{NAME}?></td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">Username</td>
        			<td class="align-middle"><?=${SESSION}->{USERNAME}?></td>
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
    				<td class="align-middle"><?=${SESSION}->{EXPIRE_DATETIME}?></td>
    			</tr>
    			<tr>
    				<td class="align-middle" colspan="2"><?=${SESSION}->{SESSION_TOKEN} ? 'TOKEN' : 'NO TOKEN'?></td>
    			</tr>
        	</tbody>
        </table>
    </div>
    <div class="text-center container-fluid mx-auto my-3">
    	<div class="row justify-content-center">
    	</div>
    </div>
</div>