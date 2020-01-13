<div class="container col-md-7 text-left">
    <h1 class="text-center p-3">App Settings</h1>
    <form id="app-settings-form" action="/ums/app/settings/app/update" method="post">
    	<div class="form-group">
    		<label for="urlServer">URL Server</label>
    		<input id="urlServer" value="<?=$urlServer?>" class="form-control" type="url" readonly="readonly">
    	</div>
    	<div class="custom-control custom-switch">
			<input id="onlyHttps" name="onlyHttps" type="checkbox" class="custom-control-input send-ajax" value="on" <?=$_checkedOnlyHttps?>>
			<label for="onlyHttps" class="custom-control-label">Only HTTPS</label>
        </div>
        <br>
        <div class="custom-control custom-switch">
			<input id="blockChangeIp" name="blockChangeIp" type="checkbox" class="custom-control-input send-ajax" value="on" <?=$_checkedBlockChangeIp?>>
			<label for="blockChangeIp" class="custom-control-label">Block change ip</label>
        </div>
        <br>
    	<div>
    		<a href="/ums/generator/site/map" class="btn btn-primary">Site Map Generator</a>
    	</div>
        <br><hr><br>
    	<div class="form-group">
    		<label for="pageNotFound">Page not found</label>
    		<input id="pageNotFound" name="pageNotFound" value="<?=$pageNotFound?>" placeholder="Page not found" class="form-control evidence-error send-ajax" maxlength="150" type="text" required="required">
    	</div>
    	<br>
    	<div class="custom-control custom-switch">
			<input id="showMessageException" name="showMessageException" type="checkbox" class="custom-control-input send-ajax" value="on" <?=$_checkedShowMessageException?>>
			<label for="showMessageException" class="custom-control-label">Show message exception</label>
        </div>
        <div class="form-group">
    		<label for="pageException">Page exception</label>
    		<input id="pageException" name="pageException" value="<?=$pageException?>" placeholder="Page exception" class="form-control evidence-error send-ajax" maxlength="150" type="text" required="required">
    	</div>
    	<br>
    	<div class="custom-control custom-switch">
			<input id="checkConnectTimeLoginSession" name="checkConnectTimeLoginSession" type="checkbox" class="custom-control-input send-ajax" value="on" <?=$_checkedConnectTimeLoginSession?>>
			<label for="checkConnectTimeLoginSession" class="custom-control-label">Check connect time login session</label>
        </div>
        <div class="form-group row">
    		<label for="maxTimeUnconnectedLoginSession" class="col-10">Max time disconnected login session</label>
    		<input id="maxTimeUnconnectedLoginSession" name="maxTimeUnconnectedLoginSession" value="<?=$maxTimeUnconnectedLoginSession?>" placeholder="Max time unconnected login session" class="form-control evidence-error send-ajax col-3 m-2 ml-3" min="-1" max="9999" type="number" required="required">
    		<select id="unitMaxTimeUnconnectedLoginSession" name="unitMaxTimeUnconnectedLoginSession" class="col-5 m-2 send-ajax evidence-error">
    			<?php foreach ($unitsTimeList as $unitTime): ?>
    				<option value="<?=$unitTime?>" <?=$unitMaxTimeUnconnectedLoginSession === $unitTime ? 'selected' : ''?>><?=$unitTime?></option>
    			<?php endforeach; ?>
    		</select>
		</div>
		<br>
		<div class="form-group">
    		<label for="maxWrongPassword">Max wrong password</label>
    		<input id="maxWrongPassword" name="maxWrongPassword" value="<?=$maxWrongPassword?>" placeholder="Max wrong password" min="1" max="999" class="form-control evidence-error send-ajax" type="number" required="required">
    	</div>
		<div class="form-group row">
    		<label for="passwordTryTime" class="col-10">Password try time</label>
    		<input id="passwordTryTime" name="passwordTryTime" value="<?=$passwordTryTime?>" placeholder="Password try time" class="form-control evidence-error send-ajax col-3 m-2 ml-3" min="0" max="9999" type="number" required="required">
    		<select id="unitPasswordTryTime" name="unitPasswordTryTime" class="col-5 m-2 send-ajax evidence-error">
    			<?php foreach ($unitsTimeList as $unitTime): ?>
    				<option value="<?=$unitTime?>" <?=$untiPasswordTryTime === $unitTime ? 'selected' : ''?>><?=$unitTime?></option>
    			<?php endforeach; ?>
    		</select>
		</div>
		<div class="form-group row">
    		<label for="userLockTime" class="col-10">User lock time</label>
    		<input id="userLockTime" name="userLockTime" value="<?=$userLockTime?>" placeholder="User lock time" class="form-control evidence-error send-ajax col-3 m-2 ml-3" min="0" max="9999" type="number" required="required">
    		<select id="unitUserLockTime" name="unitUserLockTime" class="col-5 m-2 send-ajax evidence-error">
    			<?php foreach ($unitsTimeList as $unitTime): ?>
    				<option value="<?=$unitTime?>" <?=$unitUserLockTime === $unitTime ? 'selected' : ''?>><?=$unitTime?></option>
    			<?php endforeach; ?>
    		</select>
		</div>
		<div class="form-group">
    		<label for="maxLocks">Max locks</label>
    		<input id="maxLocks" name="maxLocks" value="<?=$maxLocks?>" placeholder="Max locks" min="0" max="999" class="form-control evidence-error send-ajax" type="number" required="required">
    	</div>
    	<br><hr><br>
    	<div class="form-group">
    		<label for="minLengthName">Min. lenght name</label>
    		<input id="minLengthName" name="minLengthName" value="<?=$minLengthName?>" placeholder="Min. lenght name" min="1" max="255" class="form-control evidence-error send-ajax" type="number" required="required">
    	</div>
    	<div class="form-group">
    		<label for=maxLengthName>Max. lenght name</label>
    		<input id="maxLengthName" name="maxLengthName" value="<?=$maxLengthName?>" placeholder="Max. lenght name" min="1" max="255" class="form-control evidence-error send-ajax" type="number" required="required">
    	</div>
    	<div class="form-group">
    		<label for="minLengthUsername">Min. lenght username</label>
    		<input id="minLengthUsername" name="minLengthUsername" value="<?=$minLengthUsername?>" placeholder="Min. lenght username" min="1" max="255" class="form-control evidence-error send-ajax" type="number" required="required">
    	</div>
    	<div class="form-group">
    		<label for="maxLengthUsername">Max. lenght username</label>
    		<input id="maxLengthUsername" name="maxLengthUsername" value="<?=$maxLengthUsername?>" placeholder="Max. lenght username" min="1" max="255" class="form-control evidence-error send-ajax" type="number" required="required">
    	</div>
    	<div class="form-group">
    		<label for="minLengthPassword">Min. lenght password</label>
    		<input id="minLengthPassword" name="minLengthPassword" value="<?=$minLengthPassword?>" placeholder="Min. lenght password" min="1" max="255" class="form-control evidence-error send-ajax" type="number" required="required">
    	</div>
    	<div class="custom-control custom-switch text-left">
			<input id="checkMaxLengthPassword" name="checkMaxLengthPassword" type="checkbox" class="custom-control-input send-ajax" value="on" <?=$_checkedMaxLenghtPassword?> >
			<label for="checkMaxLengthPassword" class="custom-control-label">Check Max. lenght password</label>
        </div>
    	<div class="form-group">
    		<label for="maxLengthPassword">Max. lenght password</label>
    		<input id="maxLengthPassword" name="maxLengthPassword" value="<?=$maxLengthPassword?>" placeholder="Max. lenght password" min="1" max="255" class="form-control evidence-error send-ajax" type="number" required="required">
    	</div>
        <div class="custom-control custom-switch">
			<input id="requireHardPassword" name="requireHardPassword" type="checkbox" class="custom-control-input send-ajax" value="on" <?=$_checkedRequireHardPassword?> >
			<label for="requireHardPassword" class="custom-control-label">Require hard password</label>
        </div>
        <br>
    	<div class="form-group">
    		<label for="passDefault">Password default</label>
    		<input id="passDefault" name="passDefault" value="<?=$passDefault?>" placeholder="Password default" class="form-control evidence-error send-ajax" type="text" required="required">
    	</div>
		<br><hr><br>
        <div class="custom-control custom-switch">
			<input id="useRegex" name="useRegex" type="checkbox" class="custom-control-input send-ajax" value="on" <?=$_checkedUseRegex?> >
			<label for="useRegex" class="custom-control-label">Use regex</label>
        </div>
    	<div class="form-group">
    		<label for="regexName">Regex name</label>
    		<input id="regexName" name="regexName" value="<?=$regexName?>" placeholder="Regex name" class="form-control evidence-error send-ajax" type="text">
    	</div>
    	<div class="form-group">
    		<label for="regexUsername">Regex username</label>
    		<input id="regexUsername" name="regexUsername" value="<?=$regexUsername?>" placeholder="Regex username" class="form-control evidence-error send-ajax" type="text">
    	</div>
    	<div class="form-group">
    		<label for="regexPassword">Regex password</label>
    		<input id="regexPassword" name="regexPassword" value="<?=$regexPassword?>" placeholder="Regex password" class="form-control evidence-error send-ajax" type="text">
    	</div>
    	<div class="custom-control custom-switch">
			<input id="useRegexEmail" name="useRegexEmail" type="checkbox" class="custom-control-input send-ajax" value="on" <?=$_checkedUseRegexEmail?> >
			<label for="useRegexEmail" class="custom-control-label">Use regex email</label>
        </div>
    	<div class="form-group">
    		<label for="regexEmail">Regex email</label>
    		<input id="regexEmail" name="regexEmail" value="<?=$regexEmail?>" placeholder="Regex email" class="form-control evidence-error send-ajax" type="text">
    	</div>
    	<br><hr><br>
    	<div class="form-group">
    		<label for="sendEmailFrom">Send Email From</label>
    		<input id="sendEmailFrom" name="sendEmailFrom" value="<?=$sendEmailFrom?>" placeholder="Send email from" class="form-control evidence-error send-ajax" type="email" required="required">
    	</div>
    	<br>
        <div class="custom-control custom-switch">
			<input id="requireConfirmEmail" name="requireConfirmEmail" type="checkbox" class="custom-control-input send-ajax" value="on" <?=$_checkedRequireConfirmEmail?> >
			<label for="requireConfirmEmail" class="custom-control-label">Require confirm email</label>
        </div>
        <div class="form-group">
    		<label for="emailValidationFrom">Email validation from</label>
    		<input id="emailValidationFrom" name="emailValidationFrom" value="<?=$emailValidationFrom?>" placeholder="Email validation from" class="form-control evidence-error send-ajax" type="email" required="required">
    	</div>
    	<div class="custom-control custom-switch">
			<input id="useServerDomainEmailValidationLink" name="useServerDomainEmailValidationLink" type="checkbox" class="custom-control-input send-ajax" value="on" <?=$_checkedUseServerDomainEmailValidationLink?> >
			<label for="useServerDomainEmailValidationLink" class="custom-control-label">Use the server url for email validation link</label>
        </div>
    	<div class="form-group">
    		<label for="urlDomainEmailValidationLink">URL domain email validation link</label>
    		<input id="urlDomainEmailValidationLink" name="urlDomainEmailValidationLink" value="<?=$urlDomainEmailValidationLink?>" placeholder="URL domain email validation link" class="form-control evidence-error send-ajax" type="url" required="required">
    	</div>
    	<br>
    	<div class="form-group">
    		<label for="emailResetPassFrom">Password reset email from</label>
    		<input id="emailResetPassFrom" name="emailResetPassFrom" value="<?=$emailResetPassFrom?>" placeholder="Email reset password from" class="form-control evidence-error send-ajax" type="email" required="required">
    	</div>
    	<div class="custom-control custom-switch">
			<input id="useServerDomainResetPassLink" name="useServerDomainResetPassLink" type="checkbox" class="custom-control-input send-ajax" value="on" <?=$_checkedUseServerDomainResetPassLink?> >
			<label for="useServerDomainResetPassLink" class="custom-control-label">Use the server url for password reset link</label>
        </div>
    	<div class="form-group">
    		<label for="urlDomainResetPasswordLink">URL domain reset passwor link</label>
    		<input id="urlDomainResetPasswordLink" name="urlDomainResetPasswordLink" value="<?=$urlDomainResetPasswordLink?>" placeholder="URL domain reset passwor link" class="form-control evidence-error send-ajax" type="url" required="required">
    	</div>
    	<div class="form-group row">
    		<label for="expirationTimeResetPassword" class="col-10">Expiration time link for password reset</label>
    		<input id="expirationTimeResetPassword" name="expirationTimeResetPassword" value="<?=$expirationTimeResetPassword?>" placeholder="Expiration time link reset password" class="form-control evidence-error send-ajax col-3 m-2 ml-3" min="-1" max="9999" type="number" required="required">
    		<select id="unitExpirationTimeResetPassword" name="unitExpirationTimeResetPassword" class="col-5 m-2 send-ajax evidence-error">
    			<?php foreach ($unitsTimeList as $unitTime): ?>
    				<option value="<?=$unitTime?>" <?=$unitExpirationTimeResetPassword === $unitTime ? 'selected' : ''?>><?=$unitTime?></option>
    			<?php endforeach; ?>
    		</select>
		</div>
    	<br><hr><br>
    	<div class="custom-control custom-switch">
			<input id="addFakeUsersPage" name="addFakeUsersPage" type="checkbox" class="custom-control-input send-ajax" value="on" <?=$_checkedAddFakeUsersPage?> >
			<label for="addFakeUsersPage" class="custom-control-label">Add fake users page</label>
        </div>
        <br>
    	<div class="form-group">
    		<label for="usersForPageList">Users for page list</label>
    		<input id="usersForPageList" name="usersForPageList" value="<?=$usersForPageList?>" placeholder="Users for page list" class="form-control evidence-error send-ajax" type="text" required="required">
    	</div>
    	<div class="form-group">
    		<label for="linkPagination">N. link pagination</label>
    		<input id="linkPagination" name="linkPagination" value="<?=$linkPagination?>" placeholder="N. link pagination" min="1" max="30" class="form-control evidence-error send-ajax" type="number" required="required">
    	</div>
    	<br>
    	<div class="form-group">
	    	<label for="dateFormat">Date format</label>
    		<input id="dateFormat" name="dateFormat" value="<?=$dateFormat?>" placeholder="Date format" class="form-control evidence-error send-ajax" type="text" required="required">
    	</div>
    	<div class="form-group">
	    	<label for="datetimeFormat">Datetime format</label>
    		<input id="datetimeFormat" name="datetimeFormat" value="<?=$datetimeFormat?>" placeholder="Datetime format" class="form-control evidence-error send-ajax" type="text" required="required">
    	</div>
    	<br>
    	<div class="form-group text-right mr-md-4 mt-md-4">
	    	<button id="btn-save" class="btn btn-success px-3 py-1 mx-2 my-2" type="submit">
	    		<i id="ico-btn" class="fas fa-check"></i>
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Save</span>
	    	</button>
    	</div>
    	<input id="_xf" type="hidden" name="_xf" value="<?=$token?>">
    </form>
</div>
