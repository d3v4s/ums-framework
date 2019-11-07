<div class="container col-md-7 text-center my-5 p-5">
	<h2>YOU ARE SURE? DELETE YOUR ACCOUNT??</h2>
    <form action="/user/settings/delete/confirm" method="post">
    		<a class="btn btn-primary mx-2 my-2" href="/user/settings"><i class="fas fa-arrow-left"></i> No</a>
	    	<button type="submit" class="btn btn-danger mx-2 my-2"><i class="fa fa-trash-alt fa-xs"></i> Delete</button>
	    	<input type="hidden" name="_xfdl" value="<?=$token?>">
	</form>
</div>
