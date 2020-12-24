<?php namespace App\Controllers;
	
	function database_backup()
	{
		// initialise
		$session = session();
		// delete old file
		if ( file_exists(getcwd()."/Backups/linBMD2.sql") )
			{ 
				unlink(getcwd()."/Backups/linBMD2.sql");
			}
		// backup the database
		exec("mysqldump  --user='linBMD2' --password='linBMD2' --databases linBMD2 > ".getcwd()."/Backups/linBMD2.sql");
		// check file exists
		if ( ! file_exists(getcwd()."/Backups/linBMD2.sql") )
			{
				$session->set('message_2', 'The linBMD2 backup failed. Send email to Send email to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('housekeeping/index/2') );
			}
		// does it contain data?
		if ( filesize(getcwd()."/Backups/linBMD2.sql") == 0 )
			{
				$session->set('message_2', 'The linBMD2 backup failed. Send email to '.$session->linbmd2_email.' File size 0');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('housekeeping/index/2') );
			}
		// set flag
		$session->set('database_backup_performed', 1);
		// all good - bye bye
	}
