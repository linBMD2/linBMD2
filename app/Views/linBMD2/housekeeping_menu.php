	<?php $session = session(); ?>
	
	<div class="row">
		<label for="database_backup" class="col-8 pl-0">Backup your linBMD2 database.</label>
		<a id="database_backup" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="<?php echo(base_url('housekeeping/database_backup')) ?>">
			<span>Backup linBMD2 database</span>
		</a>
	</div>
	
	<div class="row">
		<label for="districts_staleness" class="col-8 pl-0">Test to see if your local Districts database is stale before refreshing.</label>
		<a id="districts_staleness" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="<?php echo(base_url('housekeeping/districts_staleness')) ?>">
			<span>Districts stale?</span>
			<span id="districts_staleness_spinner" class="spinner-border"  role="status">
				<span class="sr-only">Loading...</span>
			</span>
		</a>
	</div>
	
	<div class="row">
		<label for="districts_refresh" class="col-8 pl-0">Refresh districts and volumes database?</label>
		<a id="districts_refresh" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="<?php echo(base_url('housekeeping/districts_refresh')) ?>">
			<span>Refresh Districts</span>
			<span id="districts_refresh_spinner" class="spinner-border"  role="status">
				<span class="sr-only">Loading...</span>
			</span>
		</a>
	</div>
	
	<div class="row">
		<label for="manage_allocations" class="col-8 pl-0">Manage Allocations?</label>
		<a id="manage_allocations" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="<?php echo(base_url('allocation/manage_allocations/0')) ?>">
			<span>Manage Allocations</span>
		</a>
	</div>
	
	<br>
	
	<div class="row">
		<label for="firstnames" class="col-8 pl-0">Show given names</label>
		<a id="firstnames" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="<?php echo(base_url('housekeeping/firstnames')) ?>">
			<span>Show given names</span>
		</a>
	</div>
	
	<div class="row">
		<label for="surnames" class="col-8 pl-0">Show family names</label>
		<a id="surnames" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="<?php echo(base_url('housekeeping/surnames')) ?>">
			<span>Show family names</span>
		</a>
	</div>
		
	<br>
	
	<div class="row">
		<label for="phpinfo" class="col-8 pl-0">Show PHP info</label>
		<a id="phpinfo" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="<?php echo(base_url('housekeeping/phpinfo')) ?>">
			<span>Show PHP info</span>
		</a>
	</div>
	
	<div class="row mt-4 d-flex justify-content-between">	
		<a id="return" class="btn btn-primary mr-0 flex-column align-items-center" href="<?php echo(base_url('transcribe/transcribe_step1/0')); ?>">
			<span>Return?</span>
		</a>
	</div>	
	
	<script type="text/javascript">
		$( document ).ready(function() 
		{
			let $button_districts_staleness = $('#districts_staleness');
			$button_districts_staleness.on("click",function()
				{
					let $districts_staleness_spinner = $('#districts_staleness_spinner');
					$districts_staleness_spinner.addClass("active");
				});
				
			let $button_districts_refresh = $('#districts_refresh');
			$button_districts_refresh.on("click",function()
				{
					let $districts_refresh_spinner = $('#districts_refresh_spinner');
					$districts_refresh_spinner.addClass("active");
				});
		});
	</script>


