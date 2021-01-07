	<?php $session = session(); ?>	
	
	<br><br><br><br><br><br>
	<form action="<?php echo(base_url('deaths/transcribe_deaths_step2')) ?>" method="post">
		<div class="row">
			<h6 class="col-2 pl-0 small text-muted">Family Name*</h6>
			<h6 title="On start the system will calculate the next line number to use. To insert a line between lines 90 and 100, use a line number of 95." class="col-2 pl-0  small text-muted">Line Number* **</h6>
			<span class="col-2 pl-0"></span>
			<span class="col-2 pl-0"></span>
			<h6 class="col-2 pl-0 small text-muted">Lookup by volume</h6>
		</div>
		<div class="form-inline row">
			<label class="sr-only" for="familyname">Family Name</label>
			<input type="text" class="form-control col-2 pl-0" id="familyname" name="familyname" autocomplete="off" value="<?php echo esc($session->familyname);?>">
			<label class="sr-only" for="line">line</label>
			<input type="text" class="form-control col-2 pl-0" id="line" name="line" autocomplete="off" value="<?php echo esc($session->line);?>">
			<span class="col-2 pl-0"></span>
			<span class="col-2 pl-0"></span>	
			<label class="sr-only" for="reverselookup">Lookup by volume</label>
			<input type="text" class="form-control col-2 pl-0" id="reverselookup" name="reverselookup" autocomplete="off" value="<?php echo esc($session->reverselookup);?>">
		</div>
		<div class="form-inline row">
			<label class="sr-only" for="firstname">First Name</label>
			<input type="text" class="form-control col-2 pl-0" id="firstname" name="firstname" autocomplete="off" <?php  if ($session->position_cursor == '') { ?> autofocus <?php } ?> value="<?php echo esc($session->firstname);?>">
			<label class="sr-only" for="secondname">Second Name</label>
			<input type="text" class="form-control col-2 pl-0" id="secondname" name="secondname" autocomplete="off" <?php  if ($session->position_cursor == 'secondname') { ?> autofocus <?php } ?> value="<?php echo esc($session->secondname);?>">
			<label class="sr-only" for="thirdname">Third Name</label>
			<input type="text" class="form-control col-2 pl-0" id="thirdname" name="thirdname" autocomplete="off" value="<?php echo esc($session->thirdname);?>">
			<label class="sr-only" for="age">Age</label>
			<input type="text" class="form-control col-2 pl-0" id="age" name="age" autocomplete="off" value="<?php echo esc($session->age);?>">
			<label class="sr-only" for="district">District</label>
			<input type="text" class="form-control col-2 pl-0" id="district" name="district" autocomplete="off" value="<?php echo esc($session->district);?>">
			<label class="sr-only" for="page">Page</label>
			<input type="text" class="form-control col-1 pl-0" id="page" name="page" autocomplete="off" <?php if ($session->position_cursor == 'page') { ?> autofocus <?php } ?>  value="<?php echo esc($session->page);?>">
		</div>	
		<div class="row mt-2">
			<h6 class="col-2 pl-0 small text-muted">First Name*</h6>
			<h6 class="col-2 pl-0 small text-muted">Second Name/Initial</h6>
			<h6 class="col-2 pl-0 small text-muted">Third Name/Initial</h6>
			<h6 class="col-2 pl-0 small text-muted">Age</h6>
			<h6 title="The volume number will be calculated automatically from the district and allocation entries." class="col-2 pl-0 small text-muted">District Name* **</h6>
			<h6 title="Normally a 3 digit number. The system will ask for confirmation if anything else."class="col-1 pl-0 small text-muted">Page* **</h6>
		</div>


		
	



