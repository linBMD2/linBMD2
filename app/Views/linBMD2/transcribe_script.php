<?php $session = session(); ?>	
<script>
$(document).keypress(function()
	{
		$( "#firstname" ).autocomplete(
			{
				minLength: 2,
				source: "<?php echo(base_url('transcribe/search_firstnames')) ?>",
			})

		$( "#secondname" ).autocomplete(
			{
				minLength: 2,
				source: "<?php echo(base_url('transcribe/search_firstnames')) ?>",
			})
			
		$( "#thirdname" ).autocomplete(
			{
				minLength: 2,
				source: "<?php echo(base_url('transcribe/search_firstnames')) ?>",
			})
		
		$( "#familyname" ).autocomplete(
			{
				minLength: 2,
				source: "<?php echo(base_url('transcribe/search_surnames')) ?>",
			})
			
		$( "#partnername" ).autocomplete(
			{
				minLength: 2,
				source: "<?php echo(base_url('transcribe/search_surnames')) ?>",
			})
			
		$( "#district" ).autocomplete(
			{
				minLength: 2,
				source: "<?php echo(base_url('transcribe/search_districts')) ?>",
			})
			
		$( "#reverselookup" ).autocomplete(
			{
				minLength: 1,
				source: "<?php echo(base_url('transcribe/search_volumes')) ?>",
			})	
	});
	
window.onkeydown= function(dup)
		{ 
			// test which key was pressed
			switch (dup.keyCode)
				{
					case 45: // Insert key pressed = duplicate
						// test which field to duplicate but only if records exist
						switch (dup.target.id)
							{
								case 'firstname':
									$('#firstname').val("<?php echo $session->dup_firstname; ?>");
									$('#secondname').focus();
									break;
								case 'secondname':
									$('#secondname').val("<?php echo $session->dup_secondname; ?>");
									$('#thirdname').focus();
									break;
								default:
									break;
							}
						break;
					case 36: // Home key pressed = duplicate all
						$('#firstname').val("<?php echo $session->dup_firstname; ?>");
						$('#secondname').val("<?php echo $session->dup_secondname; ?>");
						$('#thirdname').val("<?php echo $session->dup_thirdname; ?>");
						$('#partnername').val("<?php echo $session->dup_partnername; ?>");
						$('#age').val("<?php echo $session->dup_age; ?>");
						$('#district').val("<?php echo $session->dup_district; ?>");
						$('#registration').val("<?php echo $session->dup_registration; ?>");
						$('#page').val('');
						$('#page').focus();
						break;
					case 33: // Page Up pressed = duplicate family name to partner name
						$('#partnername').val($('#familyname').val());
						$('#district').focus();
						break;
					default:
						break;
				}
		};
  </script>
