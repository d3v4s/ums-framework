<div class="container-fluid">
    <div class="table-responsive col-md-6 mx-auto">
        <table class="table table-striped" id="users-table">
        	<tbody>
        		<tr>
        			<th class="align-middle" colspan="2"><h4>UMS Info</h4></th>
        		</tr>
        		<tr>
        			<td class="text-primary align-middle col-5">
        				<a href="/ums/table/<?=USERS_TABLE?>">Users</a>
        			</td>
        			<td class="align-middle col-5"><?=${ENABLED_USERS}.'/'.${TOT_USERS}?></td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">
        				<a href="/ums/table/<?=DELETED_USER_TABLE?>">Deleted users</a>
    				</td>
        			<td class="align-middle"><?=${TOT_DELETED_USERS}?></td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">
        				<a href="/ums/table/<?=PENDING_USERS_TABLE?>">Pending users</a>
    				</td>
        			<td class="align-middle"><?=${PENDING_USERS}.'/'.${TOT_PENDING_USERS}?></td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">
        				<a href="/ums/table/<?=PENDING_EMAILS_TABLE?>">Pending mails</a>
        			</td>
        			<td class="align-middle"><?=${PENDING_EMAILS}.'/'.${TOT_PENDING_MAILS}?></td>
    			</tr>
    			<tr>
    				<td class="text-primary align-middle">
    					<a href="/ums/table/<?=SESSIONS_TABLE?>">Active sessions</a>
					</td>
    				<td class="align-middle"><?=${VALID_SESSIONS}.'/'.${TOT_SESSIONS}?></td>
    			</tr>
        	</tbody>
        </table>
    </div>
</div>