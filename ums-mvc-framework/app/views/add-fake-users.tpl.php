<div class="container col-md-5 text-center">
    <h1>Add Fake Users</h1>
    <form action="/ums/users/fake" method="POST">
    	<div class="form-group text-md-left">
    		<label for="n-users">N. Fake Users</label>
    		<input placeholder="N. Fake Users" class="form-control" type="number" name="n-users" id="n-users" required="required" autofocus="autofocus">
    	</div>
    	<input type="hidden" name="_xf" value="<?=$token?>">
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button class="btn btn-success px-3 py-1" type="submit">Add</button>
    	</div>
    </form>
</div>

