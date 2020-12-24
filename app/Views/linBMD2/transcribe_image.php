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
