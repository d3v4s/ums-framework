<div class="container col-md-10 text-left">
    <h1 class="text-center">Site Map Update</h1>
    <form id="site-map-update-form" action="/ums/generator/site/map" method="post" class="p-3">
    	<div class="form-group justify-content-center row p-2">
    		<label for="url-server" class="col-10">Url server</label>
    		<input id="url-server" name="url-server" value="<?=$urlServer?>" placeholder="Url server" class="form-control evidence-error send-ajax col-10 m-2" type="url" required="required">
    	</div>
    	<br><hr class="col-12"><br>
    	<?php foreach ($routes as $key => $route): ?>
        	<div id="form-route-<?=$key?>" class="form-group justify-content-center row input-route p-2">
        		<label for="route-<?=$key?>" class="col-10 text-center h3 text-primary">Route <?=$key?></label>
        		<input id="route-<?=$key?>" name="route-<?=$key?>" value="<?=$route['loc']?>" placeholder="Route" class="form-control evidence-error send-ajax col-10" type="text" required="required">
        		<label for="lastmod-<?=$key?>" class="col-10 mt-3">Last modification</label>
        		<input id="lastmod-<?=$key?>" name="lastmod-<?=$key?>" value="<?=$route['lastmod']?>" placeholder="Last modification" class="form-control evidence-error send-ajax col-10" type="date">
        		<label for="priority-<?=$key?>" class="col-10 mt-3">Priority</label>
        		<input id="priority-<?=$key?>" name="priority-<?=$key?>" value="<?=$route['priority']?>" placeholder="Priority" class="form-control evidence-error send-ajax col-10" type="number" step="0.1" min="0.0" max="1.0">
    			<label for="changefreq-<?=$key?>" class="col-10 mt-3">Change frequency</label>
        		<select id="changefreq-<?=$key?>" name="changefreq-<?=$key?>" class="evidence-error send-ajax col-10">
        			<option></option>
        			<?php foreach ($changefreqList as $changefreq): ?>
        				<option value="<?=$changefreq?>" <?=$changefreq === $route['changefreq'] ? 'selected="selected"' : ''?>><?=ucfirst($changefreq)?></option>
        			<?php endforeach; ?>
        		</select>
    			<div class="col-10 text-right my-3">
        			<button type="button" value="<?=$key?>" class="btn btn-danger mt-0 btn-delete-input-route">Delete</button>
    			</div>
            	<hr class="col-10">
        	</div>
    	<?php endforeach; ?>
    	<input id="lstk" value="<?=$key?>" type="hidden">
    	<div class="form-group container-fluid text-right col-10 pb-2">
    		<button type="button" class="btn btn-primary px-3 py-2 btn-add-input-route"><i class="fas fa-plus-circle"></i></button>
    	</div>
    	<div class="form-group text-right container-fluid col-10 pt-4">
	    	<button id="btn-update" class="btn btn-success px-3 py-1 mx-2 my-2" type="submit">
	    		<i id="ico-btn" class="fas fa-check"></i>
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Update</span>
	    	</button>
    	</div>
    	<input id="_xf" type="hidden" name="_xf" value="<?=$token?>">
    </form>
</div>