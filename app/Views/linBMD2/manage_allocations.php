	<?php $session = session(); ?>
	
	<!-- line width is 1132 pixels -->
	<?php $sw = '50px'; ?>
	<?php $dd = '232px'; ?>
	<?php $dt = '110px'; ?>
	<?php $nw = '235px'; ?> <!-- $nw = ((1132 -(x * $sw) - (y * $dt) - $dd) / n), where x = number of sw lines, y = number of dt lines, n = number of nw lines -->
	
	<div class="">
		<table class="table table-hover">
			<thead class="d-block">
				<tr>
					<th style="padding:5px; text-align:center; width:<?php echo($nw); ?>;" scope="col">Allocation Name</th>
					<th style="padding:5px; text-align:center; width:<?php echo($dt); ?>;" scope="col">Start Date</th>
					<th style="padding:5px; text-align:center; width:<?php echo($dt); ?>;" scope="col">End Date</th>
					<th style="padding:5px; text-align:center; width:<?php echo($dt); ?>;" scope="col">Last page uploaded</th>
					<th style="padding:5px; text-align:center; width:<?php echo($sw); ?>;" scope="col">Status</th>
					<th style="padding:5px; text-align:center; width:<?php echo($nw); ?>;" scope="col">Last Action Performed</th>
					<th style="padding:5px; text-align:center; width:<?php echo($dd); ?>;" scope="col">What do you want to do?</th>
					<th style="padding:5px; text-align:center; width:<?php echo($sw); ?>;" scope="col">Select</th>
				</tr>
			</thead>

			<tbody class="d-block overflow-auto" style="height:500px;">
				<?php foreach ($session->allocations as $allocation): ?>
					<?php 	if ( $allocation['BMD_status'] == 'Open' )
									{ ?>
										<tr class="alert alert-success">
						<?php }
								else
									{ ?>
										<tr class="alert alert-light">
						<?php } ?>	
											<td style="padding:5px; text-align:left; width:<?php echo($nw); ?>;"><?php echo esc($allocation['BMD_allocation_name'])?></th>
											<td style="padding:5px; text-align:center; width:<?php echo($dt); ?>;"><?php echo esc($allocation['BMD_start_date'])?></td>
											<td style="padding:5px; text-align:center; width:<?php echo($dt); ?>;"><?php echo esc($allocation['BMD_end_date'])?></td>
											<td style="padding:5px; text-align:center; width:<?php echo($dt); ?>;"><?php echo esc($allocation['BMD_last_uploaded'])?></td>
											<td style="padding:5px; text-align:center; width:<?php echo($sw); ?>;"><?php echo esc($allocation['BMD_status'])?></td>
											<td style="padding:5px; text-align:center; width:<?php echo($nw); ?>;"><?php echo esc($allocation['BMD_last_action'])?></td>
											<td style="padding:5px; text-align:center; width:<?php echo($dd); ?>;">
												<label for="next_action" class="sr-only">Next action</label>
													<select name="next_action" id="next_action">
														<?php foreach ($session->transcription_cycles as $key => $transcription_cycle): ?>
															<?php if ( $transcription_cycle['BMD_cycle_type'] == 'ALLOC' ): ?>
																 <option value="<?php echo esc($transcription_cycle['BMD_cycle_code'])?>">
																	<?php echo esc($transcription_cycle['BMD_cycle_name'])?>
																</option>
															<?php endif; ?>
														<?php endforeach; ?>
													</select>
											</td>
											<td style="padding:5px; text-align:center; width:<?php echo($sw); ?>;">
												<button  
													data-id="<?php echo esc($allocation['BMD_allocation_index']); ?>" 
													class="go_button btn btn-outline-info btn-sm">Go
												</button>
											</td>
										</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	
	<div>
		<form action="<?php echo(base_url('allocation/next_action')); ?>" method="POST" name="form_next_action" >
			<input name="BMD_allocation_index" id="BMD_allocation_index" type="hidden" />
			<input name="BMD_next_action" id="BMD_next_action" type="hidden" />
		</form>
	</div>
	
	<div class="row mt-4 d-flex justify-content-between">	
		<a id="return" class="btn btn-primary mr-0 flex-column align-items-center" href="<?php echo(base_url('transcribe/transcribe_step1/0')); ?>">
			<span>Return?</span>
		</a>
		<a class="btn btn-primary btn-sm" href="<?php echo(base_url('allocation/create_allocation_step1/0')) ?>">Create a new allocation</a>
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
				$('#BMD_allocation_index').val(id);
				$('#BMD_next_action').val(BMD_next_action);
				// and submit the form
				$('form[name="form_next_action"]').submit();
			});
	});

</script>


