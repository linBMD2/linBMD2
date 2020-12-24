	<?php $session = session(); ?>
	
	<!-- line width is 1132 pixels -->
	<?php $sw = '50px'; ?>
	<?php $dd = '232px'; ?>
	<?php $dt = '110px'; ?>
	<?php $nw = '156px'; ?>
	
	<?php $lw = '566px'; ?>
	
	<div class="">
		<table class="table table-hover">
			<thead class="d-block">
				<tr class="font-italic text-info">
						<th colspan="5" style="padding:5px; text-align:center;">Allocation</th>
						<th colspan="4" style="padding:5px; text-align:center;">Syndicate</th>
				</tr>
				<tr>
					<th style="padding:5px; text-align:left; width:<?php echo($nw); ?>;">BMD File</th>
					<th style="padding:5px; text-align:center; width:<?php echo($nw); ?>;">BMD Scan Name</th>
					<th style="padding:5px; text-align:center; width:<?php echo($sw); ?>;">N° lines trans</th>
					<th style="padding:5px; text-align:center; width:<?php echo($dt); ?>;">Start date</th>
					<th style="padding:5px; text-align:center; width:<?php echo($dt); ?>;">Upload date</th>
					<th style="padding:5px; text-align:center; width:<?php echo($dt); ?>;">Upload status</th>
					<th style="padding:5px; text-align:center; width:<?php echo($nw); ?>;">Last Action Performed</th>
					<th style="padding:5px; text-align:center; width:<?php echo($dd); ?>;">What do you want to do?</th>
					<th style="padding:5px; text-align:right; width:<?php echo($sw); ?>;">Select</th>
				</tr>
			</thead>

			<tbody class="d-block overflow-auto" style="height:450x;">
				<?php foreach ($session->headers as $header): ?>
					<tr class="font-italic text-info">
						<td colspan="5" style="padding:5px; text-align:center;"><?php echo esc($header['BMD_allocation_name'])?></td>
						<td colspan="4" style="padding:5px; text-align:center;"><?php echo esc($header['BMD_syndicate_name'])?></td>
					</tr>
				<tr>
					<td style="padding:5px; text-align:left; width:<?php echo($nw); ?>;"><?php echo esc($header['BMD_file_name'])?></th>
					<td style="padding:5px; text-align:center; width:<?php echo($nw); ?>;"><?php echo esc($header['BMD_scan_name'])?></td>
					<td style="padding:5px; text-align:center; width:<?php echo($sw); ?>;"><?php echo esc($header['BMD_records'])?></td>
					<td style="padding:5px; text-align:center; width:<?php echo($dt); ?>;"><?php echo esc($header['BMD_start_date'])?></td>
					<td style="padding:5px; text-align:center; width:<?php echo($dt); ?>;"><?php echo esc($header['BMD_submit_date'])?></td>
					<td style="padding:5px; text-align:center; width:<?php echo($dt); ?>;"><?php echo esc($header['BMD_submit_status'])?></td>
					<td style="padding:5px; text-align:center; width:<?php echo($nw); ?>;"><?php echo esc($header['BMD_last_action'])?></td>
					<td style="padding:5px; text-align:center; width:<?php echo($dd); ?>;">
						
						<label for="next_action" class="sr-only">Next action</label>
							<select name="next_action" id="next_action">
								<?php foreach ($session->transcription_cycles as $key => $transcription_cycle): ?>
									 <?php if ( $transcription_cycle['BMD_cycle_type'] == 'TRANS' ): ?>
										 <option value="<?php echo esc($transcription_cycle['BMD_cycle_code'])?>">
											<?php echo esc($transcription_cycle['BMD_cycle_name'])?>
										</option>
									<?php endif; ?>
								<?php endforeach; ?>
							</select>
					</td>
					<td style="padding:5px; text-align:right; width:<?php echo($sw); ?>;">
						<button  
							data-id="<?php echo esc($header['BMD_header_index']); ?>" 
							class="go_button btn btn-outline-info btn-sm">Go
						</button>
					</td>					
				</tr>
			
				 <?php endforeach; ?>
			</tbody>
		</table>
	</div>
	
	<div>
		<form action="<?php echo(base_url('transcribe/next_action')); ?>" method="POST" name="form_next_action" >
			<input name="BMD_header_index" id="BMD_header_index" type="hidden" />
			<input name="BMD_next_action" id="BMD_next_action" type="hidden" />
		</form>
	</div>
	
	<div class="row mt-4 d-flex justify-content-between">	
		<a class="btn btn-primary btn-sm" href="<?php echo(base_url('allocation/manage_allocations/0')) ?>">Manage Allocations</a>
		<a class="btn btn-primary btn-sm" href="<?php echo(base_url('header/reopen_BMD_step1/0')) ?>">Reopen Transcription</a>
		<a class="btn btn-primary btn-sm" href="<?php echo(base_url('header/create_BMD_step1/0')) ?>">Start a new BMD scan transcription</a>
	</div>

<script>
	
$(document).ready(function()
	{	
		$('.go_button').on("click", function()
			{
				// define the variables
				var id=$(this).data('id');
				var BMD_next_action=$(this).parents('tr').find('select[name="next_action"]').val();
				// load variables to form
				$('#BMD_header_index').val(id);
				$('#BMD_next_action').val(BMD_next_action);
				// and submit the form
				$('form[name="form_next_action"]').submit();
			});
	});

</script>


