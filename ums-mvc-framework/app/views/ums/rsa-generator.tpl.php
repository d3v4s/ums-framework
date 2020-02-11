<div class="container col-md-8 text-center">
    <h1>RSA Key Pair Generator</h1>
    <form id="rsa-generator-form" method="post" action="/ums/generator/rsa/get">
		<div class="form-group text-md-left">
    		<label for="<?=PRIV_KEY?>">Private Key</label>
    		<textarea id="<?=PRIV_KEY?>" class="form-control" readonly="readonly"></textarea>
    	</div>
    	<div class="form-group text-md-left">
    		<label for="<?=PUBL_KEY?>">Public Key</label>
    		<textarea id="<?=PUBL_KEY?>" class="form-control" readonly="readonly"></textarea>
    	</div>
    	<input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_GEN_RSA?>" value="<?=${TOKEN}?>">
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button id="btn-generate" class="btn btn-success px-3 py-1" type="submit">
	    		<i class="ico-btn fas fa-check"></i>
				<span class="spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span class="text-btn">Generate</span>
			</button>
    	</div>
    </form>
</div>