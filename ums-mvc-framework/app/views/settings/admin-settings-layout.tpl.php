<div class="container col-md-6 text-center">
    <h1 class="p-3">Layout Settings</h1>
    <form id="layout-settings-form" action="/ums/app/settings/layout/update" method="POST" class="p-3">
    	<?php foreach ($layout as $key => $val): ?>
        	<div id="form-layout-<?=$key?>" class="form-group justify-content-center row input-new-layout p-2">
        		<label for="name-layout-<?=$key?>" class="col-md-10">Layout <span class="text-primary"><?=$key?></span></label>
        		<input id="name-layout-<?=$key?>" name="name-layout-<?=$key?>" value="<?=$key?>" placeholder="Name Layout" class="form-control evidence-error send-ajax col-md-4 m-2 mb-0" type="text" required="required" readonly="readonly">
        		<input id="val-layout-<?=$key?>" name="val-layout-<?=$key?>" value="<?=$val?>" placeholder="Value Layout" class="form-control evidence-error send-ajax col-md-4 m-2 mb-0" type="text" required="required">
        		<?php if (!($key === 'default' || $key === 'ums' || $key === 'email' || $key === 'email-reset-password' || $key === 'email-validation')): ?>
        			<div class="col-md-8 col-sm-10 text-right">
	        			<button type="button" value="<?=$key?>" class="btn btn-link link-danger mt-0 p-0 btn-delete-input-layout">Delete</button>
        			</div>
        		<?php endif; ?>
            	<br><br>
        	</div>
    	<?php endforeach; ?>
    	<div class="form-group container-fluid text-right col-10 pb-2">
    		<button class="btn btn-primary px-3 py-2 btn-add-input-layout" type="button"><i class="fas fa-plus-circle"></i></button>
    	</div>
    	<div class="form-group text-right container-fluid col-10 pt-4">
	    	<button id="btn-save" class="btn btn-success px-3 py-1 mx-2 my-2" type="submit">
	    		<i id="ico-btn" class="fas fa-check"></i>
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Save</span>
	    	</button>
    	</div>
    	<input id="_xf" type="hidden" name="_xf" value="<?=$token?>">
    </form>
</div>