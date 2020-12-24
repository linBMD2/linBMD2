<?php namespace App\Controllers;

use App\Models\Identity_Model;
use App\Models\Parameter_Model;


class Home extends BaseController
{
	function __construct() 
	{
        helper('common');
        helper('backup');
    }
	
	public function index()
	{
		// initialise method
		$session = session();
		$identity_model = new Identity_Model();
		$parameter_model = new Parameter_Model();
		// get version
		$parameter = $parameter_model->where('Parameter_key', 'version')->findAll();
		$session->set('version', $parameter[0]['Parameter_value']);
		// get environment
		$parameter = $parameter_model->where('Parameter_key', 'environment')->findAll();
		$session->set('environment', $parameter[0]['Parameter_value']);
		// set heading
		$session->set('title', 'linBMD2 - A linux based system for FreeBMD transcription. Version: '.$session->version.'. Environment: '.$session->environment);
		$session->set('realname', '');
		
		// test to see if any identities exist
		$identities = $identity_model->findAll();
		
		// were any found? if not, this is first use of the system
		if ( ! $identities )
			{
				var_dump('first_use');
			}
		
		// signin
		return redirect()->to( base_url('identity/signin_step1/0') );
	}
	
	public function signout()
	{
		// declare session
		$session = session();
		
		// destroy the session
		$session->destroy();
		
		// destroy any feh windows
		shell_exec('pkill feh');
		$session->remove('feh_show');
		
		// backup the database
		database_backup();
		
		// clean session files
		// get the session save path
		$config = config('App');
		$sessionSavePath = $config->sessionSavePath;
		// find session files
		foreach( glob($sessionSavePath.'/ci_session*') as $file )
			{
				// check if it is a file
				if( is_file($file) )
					{
						// delete file
						unlink($file);
					}
			}
		
		// return
		return redirect()->to( base_url('home') );
	}
	
	public function close()
	{
		// declare session
		$session = session();
		
		// destroy the session
		$session->destroy();
		
		// destroy any feh windows
		shell_exec('pkill feh');
		$session->remove('feh_show');
		
		// tell user to exit using ALT+F4
		echo view('linBMD2/close');
	}
	
	public function help()
	{
		// declare session
		$session = session();
		
		// set path to help file
		$help_file = getcwd().'/Help/linBMD2_help.pdf';
		// show the help text
		shell_exec('firefox --new-tab '.$help_file.' &');
		// go back to calling routine
		return redirect()->to( $session->_ci_previous_url );		
		
	}
	
}
