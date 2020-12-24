	<?php $session = session(); ?>
	
	<!-- line width is 1132 pixels -->
	<?php $sw = '50px'; ?>
	<?php $nw = '300px'; ?>
	<br><br>
	
	<div class="">
		<table class="table table-hover">
			<thead class="d-block">
				<tr>
					<th class="pl-0" style="padding:5px; text-align:left; width:<?php echo($nw); ?>;" scope="col">Name</th>
					<th style="padding:5px; text-align:right; width:<?php echo($sw); ?>;" scope="col">Popularity</th>
				</tr>
			</thead>

			<tbody class="d-block overflow-auto" style="height:150px;">
				<?php if( $session->names ): ?>
					<?php foreach ($session->names as $name): ?>
						<tr>
							<td class="pl-0" style="padding:5px; text-align:left; width:<?php echo($nw); ?>;" ><?php echo esc($name['name'])?></td>
							<td style="padding:5px; text-align:right; width:<?php echo($sw); ?>;" ><?php echo esc($name['popularity'])?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		
		<div class="row mt-4 d-flex justify-content-between">	
			<a id="return" class="btn btn-primary mr-0 flex-column align-items-center" href="<?php echo(base_url('housekeeping/index/0')); ?>">
				<span>Return?</span>
			</a>
		</div
	</div>


		
	



