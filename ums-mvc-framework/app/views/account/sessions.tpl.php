<div class="container-fluid">
    <div class="table-responsive col-md-7 mx-auto">
        <table id="sessions-table" class="table table-striped">
        	<thead>
        		<tr>
        			<th class="align-middle" colspan="2">
                    	<form><input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_INVALIDATE_SESSION?>" value="<?=${TOKEN}?>"></form>
        				<?=${LANG}['active_sessions']?>
    				</th>
        		</tr>
        		<tr>
        			<th class="align-middle"><?=${LANG}[IP_ADDRESS]?></th>
        			<th class="align-middle"><?=${LANG}[REMOVE]?></th>
        		</tr>
        	</thead>
        	<tbody>
        		<?php foreach (${SESSIONS} as $sess): ?>
            		<tr id="session-<?=SESSION_ID?>">
            			<td class="text-primary align-middle">
            				<?=$sess->{IP_ADDRESS}?>
            				<?php if ($sess->{SESSION_ID} == ${CURRENT_SESSION}): ?>
            					<br>
        						<span class="text-success"><?=${LANG}['current_session']?></span>
            				<?php endif; ?>
        				</td>
            			<td class="align-middle">
            				<form action="/user/settings/sessions/invalidate" method="post" class="remove-session">
            					<button class="btn btn-warning" type="submit">
            						<i class="fas fa-minus-circle ico-btn"></i>
                					<span class="spinner-border spinner-border-sm d-none spinner" role="status" aria-hidden="true"></span>
                  					<span class="text-btn"><?=${LANG}[REMOVE]?></span>
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
	    	<a class="btn btn-warning mx-3 my-1 text-right" href="/user/settings"><i class="fa fa-pen fa-xs"></i> <?=${LANG}[SETTINGS]?></a>
    	</div>
    </div>
</div>