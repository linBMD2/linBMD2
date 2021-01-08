<?php $session = session(); ?>
	
	<?php
		if ( ! isset($session->feh_show) )
			{
				// show the feh window
				shell_exec($session->feh_command);
				$session->set('feh_show', 1);
				// make it always visible
				sleep(1); // need this to allow window manager to settle before executing wmctrl
				shell_exec('wmctrl -r feh -b toggle,above');
			}
	?>
	
	<div class="row">
		<h6 class="col-2 pl-0 small text-muted">Image Tips</h6>
	</div>
	<div class="row">
		<h6 class="col-2 pl-0 small text-muted">Move Image</h6>
		<h6 class="col-8 pl-0 small text-muted">ie. to reveal next line. With cursor in image window, click and hold left mouse button, slide cursor to move image in window. Up/Down, Left/Right.</h6>
	</div>
	<div class="row">
		<h6 class="col-2 pl-0 small text-muted">Sharpen/Blur Image</h6>
		<h6 class="col-8 pl-0 small text-muted">Sometimes it is useful to sharpen the image to reveal pixels. With cursor in image window, click and hold CTRL+left mouse button, slide horizontal left to blur, slide horizontal right to sharpen.</h6>
	</div>
	<div class="row">
		<h6 class="col-2 pl-0 small text-muted">Rotate Image</h6>
		<h6 class="col-8 pl-0 small text-muted">With cursor in image window, click and hold CTRL+middle mouse button, slide left to rotate left, slide right to rotate right. Be careful this is very sensitive.</h6>
	</div>
	
