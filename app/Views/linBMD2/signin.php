	
	<form action="<?php echo(base_url('identity/signin_step2')) ?>" method="post">
		<div class="form-group row">
			<label for="identity" class="col-2 pl-0">Identity</label>
			<input type="text" class="form-control col-2" id="identity" name="identity" aria-describedby="userHelp">
			<small id="userHelp" class="form-text text-muted col-2">This must be your FreeBMD user name.</small>
			<label for="password" class="col-2">Password</label>
			<input type="password" class="form-control col-2" id="password" name="password">
			<small id="userHelp" class="form-text text-muted col-2">This must be your FreeBMD password.</small>
		</div>
	
		<div class="row mt-4 d-flex justify-content-between">
			
			<a id="return" class="btn btn-primary mr-0 flex-column align-items-center" href="<?php echo(base_url("home/close/")); ?>">
				<span>Close application</span>
			</a>
			
			<button type="submit" class="btn btn-primary mr-0 d-flex flex-column align-items-center">
				<span>Sign in</span>	
			</button>
				
		</div>
	</form>
	
	<br><br>
	
	<div class="row">
		<label for="retrieve_password" class="col-8 pl-0">Forgotton your password?</label>
		<a id="retrieve_password" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="<?php echo(base_url('identity/retrieve_password_step1/0')) ?>">
			<span>Retrieve your password</span>
		</a>
	</div>
	<div class="row">
		<label for="create_identity" class="col-8 pl-0">You have a FreeBMD identity but you haven't registered it on this system?</label>
		<a id="create_identity" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="<?php echo(base_url('identity/create_identity_step1/0')) ?>">
			<span>Create New Identity</span>
		</a>
	</div>
	<div class="row">
		<label for="create_freebmd_identity" class="col-8 pl-0">You don't have a FreeBMD Identity?</label>
		<a id="create_freebmd_identity" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="https://www.freebmd.org.uk/Signup.html">
			<span>Start the FreeBMD registration process</span>
		</a>
	</div>
	<div class="row">
		<label for="change_password" class="col-8 pl-0">You changed your FreeBMD password? You must also change it here too.</label>
		<a id="change_password" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="<?php echo(base_url('identity/change_password_step1/0')) ?>">
			<span>Change your linBMD2 password</span>
		</a>
	</div>
	
	



