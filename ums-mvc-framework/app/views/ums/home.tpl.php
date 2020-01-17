<div class="container-fluid">
    <div class="table-responsive col-md-6 mx-auto">
        <table class="table table-striped" id="users-table">
        	<tbody>
        		<tr>
        			<th class="align-middle" colspan="2">UMS Info</th>
        		</tr>
        		<tr>
        			<td class="text-primary align-middle col-5">
        				<a href="/<?=UMS_TABLES_ROUTE.'/'.USERS_TABLE?>">Tot users</a>
        			</td>
        			<td class="align-middle col-5"><?=${TOT_USERS}?></td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">
        				<a href="/<?=UMS_TABLES_ROUTE.'/'.DELETED_USER_TABLE?>">Tot deleted users</a>
    				</td>
        			<td class="align-middle"><?=${TOT_DELETED_USERS}?></td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">
        				<a href="/<?=UMS_TABLES_ROUTE.'/'.PENDING_USERS_TABLE?>">Tot pending users</a>
    				</td>
        			<td class="align-middle"><?=${TOT_PENDING_USERS}?></td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">
        				<a href="/<?=UMS_TABLES_ROUTE.'/'.PENDING_EMAILS_TABLE?>">Tot pending mails</a>
        			</td>
        			<td class="align-middle"><?=${TOT_PENDING_MAILS}?></td>
    			</tr>
    			<tr>
    				<td class="text-primary align-middle">
    					<a href="/<?=UMS_TABLES_ROUTE.'/'.SESSIONS_TABLE?>">Tot active sessions</a>
					</td>
    				<td class="align-middle"><?=${TOT_SESSIONS}?></td>
    			</tr>
        	</tbody>
        </table>
    </div>
</div>