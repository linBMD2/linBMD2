	<?php $session = session(); ?>
	
	<!-- line width is 1132 pixels -->
	<?php $sw = '50px'; ?>
	<?php $nw = '145px'; ?>
	<br>
	
	<div class="">
		<div class="alert alert-dark row" role="alert">
			<span class="col-12 text-center"><b><?= esc($session->table_title) ?></b></span>
		</div>
		<table class="table table-hover">
			<thead class="d-block">
				<tr class="row">
					<th class="pl-0" style="padding:5px; text-align:center; width:<?php echo($sw); ?>;" scope="col">Del</th>
					<th style="padding:5px; text-align:center; width:<?php echo($sw); ?>;" scope="col">Line</th>
					<th style="padding:5px; text-align:center; width:<?php echo($nw); ?>;" scope="col">Family Name</th>
					<th style="padding:5px; text-align:center; width:<?php echo($nw); ?>;" scope="col">First Name</th>
					<th style="padding:5px; text-align:center; width:<?php echo($nw); ?>;" scope="col">Second Name</th>
					<th style="padding:5px; text-align:center; width:<?php echo($nw); ?>;" scope="col">Third Name</th>
					<th style="padding:5px; text-align:center; width:<?php echo($nw); ?>;" scope="col">Partner Name</th>
					<th style="padding:5px; text-align:center; width:<?php echo($nw); ?>;" scope="col">District:Vol</th>
					<th style="padding:5px; text-align:center; width:<?php echo($sw); ?>;" scope="col">Page</th>
					<th style="padding:5px; text-align:center; width:<?php echo($sw); ?>;" scope="col">Com +</th>
					<th class="pr-0" style="padding:5px; text-align:center; width:<?php echo($sw); ?>;" scope="col">Com -</th>
				</tr>
			</thead>

			<tbody class="d-block overflow-auto" style="height:150px;">
				<?php if( $session->transcribe_detail_data ): ?>
					<?php foreach ($session->transcribe_detail_data as $detail): ?>
						<tr class="row">
							<th class="pl-0" style="padding:5px; text-align:center; width:<?php echo($sw); ?>;" scope="row">
								<a id="delete_line" href="<?php echo(base_url('marriages/delete_line_step1/'.esc($detail['BMD_index']))) ?>"</a>
								<span><input type="checkbox" </input></span>
							</th>
							<td style="padding:5px; text-align:center; width:<?php echo($sw); ?>;">
								<a id="select_line" href="<?php echo(base_url('marriages/select_line/'.esc($detail['BMD_index']))) ?>"</a>
								<span><?php echo esc($detail['BMD_line_sequence'])?></span>
							</td>
							<td style="padding:5px; text-align:center; width:<?php echo($nw); ?>;" ><?php echo esc($detail['BMD_surname'])?></td>
							<td style="padding:5px; text-align:center; width:<?php echo($nw); ?>;" ><?php echo esc($detail['BMD_firstname'])?></td>
							<td style="padding:5px; text-align:center; width:<?php echo($nw); ?>;" ><?php echo esc($detail['BMD_secondname'])?></td>
							<td style="padding:5px; text-align:center; width:<?php echo($nw); ?>;" ><?php echo esc($detail['BMD_thirdname'])?></td>
							<td style="padding:5px; text-align:center; width:<?php echo($nw); ?>;" ><?php echo esc($detail['BMD_partnername'])?></td>
							<td style="padding:5px; text-align:center; width:<?php echo($nw); ?>;" ><?php echo esc($detail['BMD_district'].':'.$detail['BMD_volume'])?></td>
							<td style="padding:5px; text-align:center; width:<?php echo($sw); ?>;" ><?php echo esc($detail['BMD_page'])?></td>
							<td style="padding:5px; text-align:center; width:<?php echo($sw); ?>;">
								<a id="select_line" href="<?php echo(base_url('marriages/select_comment/'.esc($detail['BMD_index']))) ?>"</a>
								<?php if ( empty($detail['BMD_comment_type']) ) 
									{ ?> 
										<span><?php echo '+';?></span></td>
									<?php }
									else 
										{?>
										<span><?php echo esc($detail['BMD_comment_type']);?></span></td>
									<?php }?>
							<td class="pr-0" style="padding:5px; text-align:center; width:<?php echo($sw); ?>;">
								<a id="remove_comments" href="<?php echo(base_url('marriages/remove_comments/'.esc($detail['BMD_index']).'/'.esc($detail['BMD_line_sequence']))) ?>"</a>
								<span><?php echo '-';?></span></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>


		
	



