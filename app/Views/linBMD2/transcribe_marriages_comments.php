	<?php $session = session(); ?>
		
	<br><br><br><br>
		<form action="<?php echo(base_url('marriages/comment_step2')) ?>" method="post">
			<div class="form-group row mt-2">
				<label class="col-2 pl-0" for="comment_type">Comment type</label>
				<select name="comment_type" id="comment_type" class="col-2">
					<?php foreach ($session->comment_types as $key => $type): ?>
						 <option value="<?php echo esc($key)?>"<?php if ( $key == $session->comment_type ) {echo esc(' selected');} ?>><?php echo esc($type)?></option>
					<?php endforeach; ?>
				</select>
				<small class="form-text text-muted col-8" id="userHelp">Choose from dropdown.</small>
			</div>
			<div class="form-group row mt-2">
				<label class="col-2 pl-0" for="comment_span">Number of lines</label>
				<input type="text" class="form-control col-1 pl-0" id="comment_span" name="comment_span" aria-describedby="userHelp" value="<?php echo esc($session->comment_span);?>">
				<small class="form-text text-muted col-9" id="userHelp">Number of lines this comment applies to after anchor sequence.</small>
			</div>	
			<div class="form-group row mt-2">
				<label class="col-2 pl-0" for="comment_text">Comment text</label>
				<input type="text" class="form-control col-5 pl-0" id="comment_text" name="comment_text" value="<?php echo esc($session->comment_text);?>">
				<small class="form-text text-muted col-5" id="userHelp">eg => Entry reads CRITCHER or SMITH for mother's name.</small>
			</div>	
		
			<div class="row mt-4 d-flex justify-content-between">
				
					<a id="return" class="btn btn-primary mr-0 flex-column align-items-center" href="<?php echo(base_url('marriages/transcribe_marriages_step1/0')); ?>">
						<span>Return</span>
					</a>

					<button type="submit" class="btn btn-primary mr-0 flex-column align-items-center">
						<span>Submit</span>	
					</button>
				
			</div>
		</form>


		
	



