<div class="container col-md-7 text-left">
    <h1 class="text-center p-3">RSA Key Pair Settings</h1>
    <form id="rsa-settings-form" action="/ums/app/settings/rsa/update" method="POST">
    	<div class="form-group">
    		<label for="digestAlg">Digest algorithm</label>
    		<input id="digestAlg" name="digestAlg" value="<?=$digestAlg?>" placeholder="Digest algorithm" class="form-control evidence-error send-ajax" type="text" required="required" autofocus="autofocus">
    	</div>
    	<div class="form-group">
    		<label for="privateKeyBits">Private key bits</label>
    		<input id="privateKeyBits" name="privateKeyBits" value="<?=$privateKeyBits?>" placeholder="Private key bits" min="1" max="1024000" class="form-control evidence-error send-ajax" type="number" required="required">
    	</div>
    	<br><hr><br>
    	<div class="custom-control custom-switch">
			<input id="rsaKeyStatic" name="rsaKeyStatic" type="checkbox" class="custom-control-input send-ajax" value="on" <?=$_checkedRsaKeyStatic?> >
			<label for="rsaKeyStatic" class="custom-control-label">RSA key static</label>
        </div>
    	<div class="form-group">
    		<label for="rsaPrivKeyFile">RSA private key file</label>
    		<input id="rsaPrivKeyFile" name="rsaPrivKeyFile" value="<?=$rsaPrivKeyFile?>" placeholder="RSA private key file" class="form-control evidence-error send-ajax" type="text">
    		<span class="text-muted">Insert your file on: <?=$pathPrivKey?></span>
    	</div>
    	<div class="form-group text-right mr-md-4 mt-md-4">
	    	<button id="btn-save" class="btn btn-success px-3 py-1 mx-2 my-2" type="submit">
	    		<i id="ico-btn" class="fas fa-check"></i>
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Save</span>
	    	</button>
    	</div>
    	<input id="_xf" type="hidden" name="_xf" value="<?=$token?>" class="send-ajax">
    </form>
    <div class="row">
    	<a href="/ums/generator/rsa" class="btn btn-primary m-2">RSA Key Pair Generator</a>
    	<form id="rsa-gen-save-form" action="/ums/generator/rsa/save" method="POST">
    		<input id="rsa-settings-form" id="_xfgs" type="hidden" name="_xfgs" value="<?=$tokenGenSave?>" class="send-ajax">
	    	<button id="btn-gen-save-key" type="submit" class="btn btn-primary m-2">
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Generate and Save Key</span>
	    	</button>
    	</form>
    </div>
</div>