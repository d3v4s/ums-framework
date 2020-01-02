<div class="container col-md-5 text-center">
    <h1>Add Fake Users</h1>
    <form id="fakeusr-generator-form" action="/ums/users/fake" method="POST">
    	<div class="form-group text-md-left">
    		<label for="n-users">N. fake users</label>
    		<input id="n-users" placeholder="N. Fake Users" class="form-control send-ajax evidence-error" type="number" name="n-users" required="required" autofocus="autofocus">
    	</div>
    	<input id="_xf" type="hidden" name="_xf" value="<?=$token?>">
    	<div class="custom-control custom-switch text-md-left">
			<input id="enabled" name="enabled" type="checkbox" class="custom-control-input send-ajax" value="true" checked="checked">
			<label class="custom-control-label" for="enabled">Enabled</label>
        </div>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button id="btn-add" class="btn btn-success px-3 py-1" type="submit">
	    		<i id="ico-btn" class="fas fa-check"></i>
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Add</span>
	    	</button>
    	</div>
    </form>
</div>