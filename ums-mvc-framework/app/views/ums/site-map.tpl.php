<div class="container col-md-10 text-left">
    <h1 class="text-center">Site Map Generator</h1>
    <?php if (${SITE_MAP_EXISTS}): ?>
        <div class="container col-10 text-md-right text-center p-3">
        	<span class="text-danger h4 align-middle">Site map already exists</span>
        	<a href="/ums/generator/site/map/update" class="btn btn-primary mx-2 align-middle">Update site map</a>
        </div>
    <?php endif; ?>
    <form id="sitemap-generator-form" action="/ums/generator/site/map" method="post" class="p-3">
    	<div class="form-group justify-content-center row p-2">
    		<label for="<?=URL_SERVER?>" class="col-10">Url server</label>
    		<input id="<?=URL_SERVER?>" name="<?=URL_SERVER?>" value="<?=${URL_SERVER}?>" placeholder="Url server" class="form-control evidence-error send-ajax col-10 m-2" type="url" required="required">
    	</div>
    	<br><hr class="col-12"><br>
    	<?php foreach (${ROUTES} as $key => $route): ?>
        	<div id="form-route-<?=$key?>" class="form-group justify-content-center row input-route p-2">
        		<label for="<?=SITEMAP_ROUTE.$key?>" class="col-10 text-center h3 text-primary">Route <?=$key?></label>
        		<input id="<?=SITEMAP_ROUTE.$key?>" name="<?=SITEMAP_ROUTE.$key?>" value="<?=$route[LOCATION] ?? ''?>" placeholder="Route" class="form-control evidence-error send-ajax col-10" type="text" required="required">
        		<label for="<?=SITEMAP_LASTMOD.$key?>" class="col-10 mt-3">Last modification</label>
        		<input id="<?=SITEMAP_LASTMOD.$key?>" name="<?=SITEMAP_LASTMOD.$key?>" value="<?=$route[LASTMOD] ?? ''?>" placeholder="Last modification" class="form-control evidence-error send-ajax col-10" type="date">
        		<label for="<?=SITEMAP_PRIORITY.$key?>" class="col-10 mt-3">Priority</label>
        		<input id="<?=SITEMAP_PRIORITY.$key?>" name="<?=SITEMAP_PRIORITY.$key?>" value="<?=$route[PRIORITY] ?? ''?>"placeholder="Priority" class="form-control evidence-error send-ajax col-10" type="number" step="0.1" min="0.0" max="1.0">
    			<label for="<?=SITEMAP_CHANGEFREQ.$key?>" class="col-10 mt-3">Change frequency</label>
        		<select id="<?=SITEMAP_CHANGEFREQ.$key?>" name="<?=SITEMAP_CHANGEFREQ.$key?>" class="evidence-error send-ajax col-10">
        			<?php if (!isset($route[CHANGEFREQ])): ?>
        				<option selected="selected"></option>
    				<?php endif; ?>
        			<?php foreach (CHANGE_FREQ_LIST as $changefreq): ?>
        				<option value="<?=$changefreq?>" <?=$changefreq === $route[CHANGEFREQ] ? 'selected="selected"' : ''?> ><?=ucfirst($changefreq)?></option>
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
	    	<button id="btn-generate" class="btn btn-success px-3 py-1 mx-2 my-2" type="submit">
	    		<i class="fas fa-check ico-btn"></i>
	    		<span class="spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span class="text-btn">Generate</span>
	    	</button>
    	</div>
    	<input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_GEN_SITEMAP?>" value="<?=${TOKEN}?>">
    </form>
</div>