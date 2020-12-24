	<?php $session = session(); ?>
	
	<br><br><br><br><br><br>
	<div>
		<i>
			<div class="row">
				<p class="col-2 pl-0">Default zoom factor</p>
				<p class="col-1">1</p>
				<p class="col-2">Default width px</p>
				<p class="col-1">1100px</p>
				<p class="col-2">Default height px</p>
				<p class="col-1">60px</p>
			</div>
		</i>
	</div>
	
	<div>
		<b>
			<div class="row">
				<p class="col-2 pl-0">Current zoom factor</p>
				<p class="col-1"><?php echo($session->transcribe_header[0]['BMD_image_zoom']);?></p>
				<p class="col-2">Current width px</p>
				<p class="col-1"><?php echo($session->transcribe_header[0]['BMD_image_x'].'px');?></p>
				<p class="col-2">Current height px</p>
				<p class="col-1"><?php echo($session->transcribe_header[0]['BMD_image_y'].'px');?></p>
			</div>
		</b>
	</div>

	<div>
		<form action="<?php echo(base_url('transcribe/image_parameters_step2/'.$session->transcribe_header[0]['BMD_header_index'])); ?>" method="post">
			<div class="form-group row">
				<label for="image_zoom" class="col-2 pl-0">New zoom factor =></label>
				<input type="text" class="form-control col-1" id="image_zoom" name="image_zoom" autofocus value="<?php echo esc($session->image_zoom);?>">
				<label for="image_width" class="col-2">New width px =></label>
				<input type="text" class="form-control col-1" id="image_width" name="image_width" autofocus value="<?php echo esc($session->image_width);?>">
				<label for="image_height" class="col-2">New height px =></label>
				<input type="text" class="form-control col-1" id="image_height" name="image_height" autofocus value="<?php echo esc($session->image_height);?>">
			</div>
		
		<div class="row d-flex justify-content-end mt-4">
				<button type="submit" class="btn btn-primary mr-0 d-flex flex-column align-items-center">
					<span>Apply</span>	
				</button>
			</div>
			
		</form>
	</div>
	
