<div class="container col-md-7 text-center my-5 p-5">
	<h2>YOU ARE SURE? DELETE YOUR ACCOUNT??</h2>
    <form id="delete-account-form" action="/<?=ACCOUNT_SETTINGS_ROUTE.'/'.DELETE_ROUTE?>" method="post">
		<a class="btn btn-primary mx-2 my-2" href="/<?=ACCOUNT_SETTINGS_ROUTE?>"><i class="fas fa-arrow-left"></i> No</a>
    	<button id="btn-delete" type="submit" class="btn btn-danger mx-2 my-2"><i class="fa fa-trash-alt fa-xs"></i> Delete</button>
    	<input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_DELETE_ACCOUNT?>" value="<?=${TOKEN}?>">
	</form>
</div>