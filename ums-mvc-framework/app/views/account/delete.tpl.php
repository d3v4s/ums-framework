<div class="container col-md-7 text-center my-5 p-5">
	<h2><?=${LANG}['confirm_delete']?></h2>
    <form id="delete-account-form" action="/user/settings/delete" method="post">
		<a class="btn btn-primary mx-2 my-2" href="/user/settings"><i class="fas fa-arrow-left"></i> <?=${LANG}[DENY]?></a>
    	<button id="btn-delete" type="submit" class="btn btn-danger mx-2 my-2">
    		<i class="fas fa-trash-alt ico-btn"></i>
    		<span class="spinner-border spinner-border-sm d-none spinner" role="status" aria-hidden="true"></span>
			<span class="text-btn"><?=${LANG}[DELETE]?></span>
		</button>
    	<input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_DELETE_ACCOUNT?>" value="<?=${TOKEN}?>">
	</form>
</div>