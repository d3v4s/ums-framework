<div class="container col-md-7 text-center">
    <h1>New Email</h1>
    <form id="send-email-form" action="/<?=SEND_EMAIL_ROUTE?>" method="post">
    	<div class="form-group text-md-left">
    		<label for="<?=TO?>">To</label>
    		<input id="<?=TO?>" name="<?=TO?>" value="<?=${TO}?>" placeholder="To" class="form-control evidence-error send-ajax-crypt validate-email" type="email" required="required" autofocus="autofocus">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="<?=SUBJETC?>">Subject</label>
    		<input id="<?=SUBJETC?>" name="<?=SUBJETC?>" placeholder="Subject" class="form-control send-ajax-crypt" type="text">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="<?=CONTENT?>">Text</label>
    		<span class="text-left text-dark">
	    		<textarea id="<?=CONTENT?>" name="<?=CONTENT?>" class="form-control evidence-error send-ajax-crypt" rows="10" required="required"></textarea>
    		</span>
    	</div>
    	<noscript>
    		<div class="container-fluid">
    			<h3 class="text-danger">ENABLE JAVASCRIPT TO SEND EMAIL</h3>
    		</div>
    	</noscript>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button id="btn-send" class="btn btn-success px-3 py-1" type="submit">
	    		<i id="ico-btn" class="fas fa-paper-plane"></i>
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Send</span>
	    	</button>
    	</div>
    	<input id="<?=GET_KEY_TOKEN?>" type="hidden" name="<?=CSRF_KEY_JSON?>" value="<?=${GET_KEY_TOKEN}?>">
    	<input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_NEW_EMAIL?>" value="<?=${TOKEN}?>">
    </form>
</div>