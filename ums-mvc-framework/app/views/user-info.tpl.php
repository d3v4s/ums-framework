<div class="container-fluid">
    <div class="table-responsive col-md-4  mx-auto">
        <table class="table table-striped" id="users-table">
        	<tbody>
        		<tr>
        			<th colspan="2">User: <?=$user->username?></th>
        		</tr>
        		<tr>
        			<td class="text-primary">ID</td>
        			<td><?=$user->id?></td>
    			</tr>
    			<tr>
        			<td class="text-primary">Full Name</td>
        			<td><?=$user->name?></td>
    			</tr>
    			<tr>
        			<td class="text-primary">Username</td>
        			<td><?=$user->username?></td>
    			</tr>
    			<tr>
        			<td class="text-primary">Email</td>
        			<td><?=$user->email?></td>
    			</tr>
    			<?php if (isUserAdmin()): ?>
        			<tr>
            			<td class="text-primary">Role</td>
            			<td><?=$user->roletype?></td>
        			</tr>
    			<?php endif;?>
        	</tbody>
        </table>
    </div>
    <div class="text-center container-fluid mx-auto my-3">
    	<div class="row justify-content-center">
    	    <?php if (userCanUpdate()): ?>
    	    	<a class="btn btn-warning mx-3 my-1" href="/ums/user/<?=$user->id?>/update"><i class="fa fa-pen fa-xs"></i> Update</a>
    	    <?php endif;?>
    	    <?php if(userCanDelete()): ?>
    	    	<form action="/ums/user/delete" method="POST">
    		    	<button type="submit" class="btn btn-danger mx-3 my-1" onclick="return confirm('Delete user??')"><i class="fa fa-trash-alt fa-xs"></i> Delete</button>
    		    	<input type="hidden" name="id" value="<?=$user->id?>">
    		    	<input type="hidden" name="_xf" value="<?=$token?>">
    	    	</form>
    	    	<a class="btn btn-primary mx-3 my-1" href="/ums/user/<?=$user->id?>/update/pass"><i class="fas fa-key"></i> Change Password</a>
    	    <?php endif;?>
    	</div>
    </div>
</div>