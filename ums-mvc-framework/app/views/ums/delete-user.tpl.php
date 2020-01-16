<div class="container col-md-7 text-center my-5 p-5">
	<h2>YOU ARE SURE? DELETE THIS ACCOUNT??</h2>
	<h3>Username: <?=$user->username?></h3>
    <form action="/ums/user/delete/confirm" method="post">
    		<a class="btn btn-primary mx-2 my-2" href="/ums/user/<?=$user->id?>"><i class="fas fa-arrow-left"></i> No</a>
	    	<button type="submit" class="btn btn-danger mx-2 my-2"><i class="fa fa-trash-alt fa-xs"></i> Delete</button>
	    	<input type="hidden" name="id" value="<?=$user->id?>">
	    	<input type="hidden" name="_xfdlu" value="<?=$token?>">
	</form>
</div>
