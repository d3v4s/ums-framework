<div class="container-fluid">
    <div class="table-responsive col-md-7 mx-auto">
        <table id="sessions-table" class="table table-striped">
        	<thead>
        		<tr>
        			<th class="align-middle" colspan="2">
                    	<form><input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_INVALIDATE_SESSION?>" value="<?=${TOKEN}?>"></form>
        				Active Sessions
    				</th>
        		</tr>
        		<tr>
        			<th class="align-middle">IP Address</th>
        			<th class="align-middle">Disable</th>
        		</tr>
        	</thead>
        	<tbody>
        		<?php foreach (${SESSIONS} as $sess): ?>
            		<tr id="session-<?=SESSION_ID?>">
            			<td class="text-primary align-middle">
            				<?=$sess->{IP_ADDRESS}?>
            				<?php if ($sess->{SESSION_ID} == ${CURRENT_SESSION}): ?>
            					<br>
        						<span class="text-success">Current Session</span>
            				<?php endif; ?>
        				</td>
            			<td class="align-middle">
            				<form action="/<?=ACCOUNT_SETTINGS_ROUTE.'/'.SESSIONS_ROUTE.'/'.INVALIDATE_ROUTE?>" method="post" class="remove-session">
            					<button class="btn btn-warning" type="submit">
            						<i class="fas fa-minus-circle ico-btn"></i>
                					<span class="spinner-border spinner-border-sm d-none spinner" role="status" aria-hidden="true"></span>
                  					<span class="text-btn">Remove</span>
            					</button>
            					<input name="<?=SESSION_ID?>" value="<?=$sess->{SESSION_ID}?>" type="hidden" class="send-ajax">
            				</form>
            			</td>
        			</tr>
        		<?php endforeach; ?>
        	</tbody>
        </table>
    </div>
    <div class="text-center container-fluid mx-auto my-3">
    	<div class="row justify-content-center">
	    	<a class="btn btn-warning mx-3 my-1 text-right" href="/<?=ACCOUNT_SETTINGS_ROUTE?>"><i class="fa fa-pen fa-xs"></i> Settings</a>
    	</div>
    </div>
</div>