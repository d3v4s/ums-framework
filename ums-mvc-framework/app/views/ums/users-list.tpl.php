<h2 class="text-center">USER LIST</h2>
<div class="container-fluid">
    <header class="p-3">
    	<nav class="navbar navbar-expand-md justify-content-center">
    		<form id="search-form" class="form-inline" action="<?=$searchAction?>" method="get">
                <input 	
                		class="form-control mr-sm-2"
                		name="search" id="search"
                		type="text"
                		placeholder="Search"
                		value="<?=$search?>"
                		aria-label="Search"
                		autofocus="autofocus"
        		>

                <button class="btn btn-outline-success m-2 my-sm-0" type="submit">Search</button>
                <a href="/ums/users/" class="btn btn-outline-warning m-2 my-sm-0">Reset</a>
    		</form>
    	</nav>
    	<div class="row justify-content-center col-7 mx-auto">
    		<label class="my-auto mx-2">Users for page</label>
    		<?php foreach ($usersForPageList as $ufp): ?>
    			<a
    				class="btn btn-<?=$usersForPage == $ufp ? 'secondary disabled' : 'primary'?> p-1 px-2 m-2"
    				href="<?=$baseLinkUfp . $ufp . $searchQuery?>"
				><?=$ufp?></a>
    		<?php endforeach; ?>
    	</div>
    </header>
    <div class="table-responsive col-md-10 mx-auto">
        <table class="table table-striped" id="users-table">
        	<thead>
        		<tr>
        			<th colspan="9" class="text-center">
        				<span>TOTAL USERS <?=$totUsers?> - Page <?=$page?>/<?=$maxPages?></span>
    				</th>
    			</tr>
        		<tr>
        			<th class="w-5">
        				<a href="<?=$linkHeadId?>">#</a>
        				<i class="<?=$classHeadId?>"></i>
        			</th>
        			<th>
        				<a href="<?=$linkHeadName?>">NAME</a>
        				<i class="<?=$classHeadName?>"></i>
        			</th>
        			<th>
        				<a href="<?=$linkHeadUsername?>">USERNAME</a>
        				<i class="<?=$classHeadUsername?>"></i>
        			</th>
        			<th>
        				<a href="<?=$linkHeadEmail?>">EMAIL</a>
        				<i class="<?=$classHeadEmail?>"></i>
        			</th>
					<th>
        				<a href="<?=$linkHeadEnabled?>">STATE</a>
        				<i class="<?=$classHeadEnabled?>"></i>
        			</th>
        			<?php if (isUserAdmin()): ?>
            			<th>
            				<a href="<?=$linkHeadRole?>">ROLE</a>
            				<i class="<?=$classHeadRole?>"></i>
            			</th>
        			<?php endif; ?>
        			<th>
        			</th>
        		</tr>
        	</thead>
        	<tbody>
        	<?php
            	if (isset($users)) :
            	    foreach ($users as $user):
            	    ?>
            	        <tr>
            	        	<td class="align-middle"><?= $user->id?></td>
            	        	<td class="align-middle"><?= $user->name?></td>
            	        	<td class="align-middle">
            	        		<a href="/ums/user/<?=$user->id?>">
    		        	        	<?= $user->username?>
            	        		</a>
            	        	</td>
            	        	<td class="align-middle">
            	        		<a href="mailto:<?=$user->email?>">
            	        			<?=$user->email?>
            	        		</a>
            	        	</td>
            	        	<td class="align-middle"><?= $user->enabled ? 'enabled' : 'disabled'?></td>
            	        	<?php if (isUserAdmin()): ?>
            	        		<td class="align-middle"><?= $user->roletype?></td>
            	        	<?php endif;?>
            	        	<td class="align-middle">
            	        		<div class="row">
            	        			<div class="col-lg-6 col-md-6 col-sm-7 col-xs-8 my-1">
            	        				<a class="btn btn-warning text-cente" href="/ums/user/<?=$user->id?>/update">Update</a>
            	        			</div>
            	        		</div>
            	        	</td>
            	        </tr>
            	    <?php endforeach;
                else: ?>
            	    <tr><td colspan="9" class="text-center"><h2>ERR 404!!<br>No records found</h2></td></tr>
            <?php endif; ?>
        	</tbody>
        </table>
    </div>
    <?php require getViewsPath().'/utils/pagination.php';?>
    <?php if ($viewAddFakeUsers): ?>
		<div class="container text-left p-5 ml-5">
    		<a class="btn btn-primary ml-5" href="/ums/users/fake">Add Fake Users</a>
    	</div>
    <?php endif; ?>
</div>