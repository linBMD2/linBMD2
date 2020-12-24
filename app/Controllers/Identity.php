<?php namespace App\Controllers;

use App\Models\Identity_Model;
use App\Models\Parameter_Model;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Identity extends BaseController
{
	public function signin_step1($start_message)
		{
			// initialise
			$session = session();
			
			if ( $start_message == 0 )
				{
					$session->set('message_1', 'Welcome, please sign in.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
				}
				
			if ( $start_message == 2 )
				{
					$session->set('message_1', 'Welcome, please sign in.');
					$session->set('message_class_1', 'alert alert-primary');
				}
			
			// show view
			echo view('templates/header');
			echo view('linBMD2/signin');
		}
	
	public function signin_step2()
	{
		// initialise method
		$session = session();
		$model = new Identity_Model();
		
		// find identity entered by user
		$identity = $model->where('BMD_user', $this->request->getPost('identity'))->find();

		// was it found?
		if ( ! $identity )
			{
				$session->set('message_2', 'The identity ou entered is not defined on this system => '.$this->request->getPost('identity'));
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('identity/signin_step1/1') );
			}

		// test correct password
		if ( $this->request->getPost('password') != $identity[0]['BMD_password'] )
			{
				$session->set('message_2', 'Password is not correct for this identity.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('identity/signin_step1/1') );
			}
				
		// store identity index
		$session->set('BMD_identity_index', $identity[0]['BMD_identity_index']);
		$session->set('realname', $identity[0]['BMD_realname']);
		return redirect()->to( base_url('transcribe/transcribe_step1/0') );
	}

	public function create_identity_step1($start_message)
	{
		// initialise
		$session = session();

		if ( $start_message == 0 )
			{
				$session->set('message_1', 'Create your Identity by entering the following information.');
				$session->set('message_class_1', 'alert alert-primary');
				$session->set('message_2', '');
				$session->set('message_class_2', '');
				
				$session->set('identity', '');
				$session->set('password', '');
				$session->set('realname', '');
				$session->set('email', '');	
			}
		
		// show view
		echo view('templates/header');
		echo view('linBMD2/create_identity');
		echo view('templates/footer');
	}
	
	public function create_identity_step2()
	{
		// initialise method
		$session = session();
		$model = new Identity_Model();
		$parameter_model = new Parameter_Model();
		
		// get user data
		$session->set('identity', $this->request->getPost('identity'));
		$session->set('password', $this->request->getPost('password'));
		$session->set('realname', $this->request->getPost('realname'));
		$session->set('email', $this->request->getPost('email'));
		
		// find identity entered by user
		$identity = $model->where('BMD_user', $session->identity)->find();
		// was it found?
		if ( $identity )
			{
				$session->set('message_2', 'This Identity already exists => '.$session->identity);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('identity/create_identity_step1/1') );
			}

		// test identity / password on FreeBMD by trying to upload a file
		// set curl handle and results file handle - need to get defaults from parameters table because the common_helper has not yet been run.
		// load autoupload url for curl
		$parameter = $parameter_model->where('Parameter_key', 'autouploadurl')->findAll();
		$session->set('autouploadurl', $parameter[0]['Parameter_value']);
		// load programme name
		$parameter = $parameter_model->where('Parameter_key', 'programname')->findAll();
		$session->set('programname', $parameter[0]['Parameter_value']);
		// load version
		$parameter = $parameter_model->where('Parameter_key', 'version')->findAll();
		$session->set('version', $parameter[0]['Parameter_value']);
		// load uploadagent
		$parameter = $parameter_model->where('Parameter_key', 'uploadagent')->findAll();
		$session->set('uploadagent', $parameter[0]['Parameter_value']);
		// set curl
		$curl_url = $session->autouploadurl;
		$ch = curl_init($curl_url);
		$fp = fopen(getcwd()."/curl_result.txt", "w");				
		// set up the fields to pass
		$postfields = array(
										"UploadAgent" => $session->uploadagent,
										"user" => $session->identity,
										"password" => $session->password,
										"file" => "TEST",
										"content2" => @getcwd()."/curl_result.txt",
										"data_version" => "districts.txt:??"
										);
		// set the curl options
		curl_setopt($ch, CURLOPT_USERAGENT, $session->programname.':'.$session->version);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		// execute curl
		if ( curl_exec($ch) === false )
			{
				// problem so send error message
				$session->set('message_2', 'A technical problem occurred. Send an email to '.$session->linbmd2_email.' describing what you were doing when the error occurred => Failed to fetch references in Identity::create_identity_step2, around line 133 => '.$curl_url);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('identity/create_identity_step1/1') );
			}
		// close the curl and file handles
		curl_close($ch);
		fclose($fp);
		// search the curl result for error
		$fp = fopen(getcwd()."/curl_result.txt", "r");
		while (!feof($fp))
		{
			$buffer = fgets($fp);
			if (strpos($buffer, "passwordfailed") !== FALSE)
				{
					fclose($fp);
					$session->set('message_2', 'This identity and password is not valid for the FreeBMD site. Do you have a FreeBMD account? => '.$session->identity);
					$session->set('message_class_2', 'alert alert-danger');
					return redirect()->to( base_url('identity/create_identity_step1/2') );
				}
		}

		// test for real name
		if ( $this->request->getPost('realname') == '' )
			{
				$session->set('message_2', 'You must enter your real name.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('identity/create_identity_step1/1') );
			}
			
		// test for email
		if ( $this->request->getPost('email') == '' )
			{
				$session->set('message_2', 'You must enter your email.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('identity/create_identity_step1/1') );
			}
			
		// All good so write to database
		$data = [
						'BMD_user' => $session->identity,
						'BMD_password' => $session->password,
						'BMD_realname' => $session->realname,
						'BMD_email' => $session->email,
						'BMD_total_records' => 0,
					];
		$model->insert($data);
		
		// go back to sign in
		$session->set('message_2', 'Your Identity has been created on this system.');
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('identity/signin_step1/2') );
	}
	
	public function change_password_step1($start_message)
	{
		// initialise
		$session = session();

		if ( $start_message == 0 )
			{
				$session->set('message_1', 'Change your password on this system. The new password must match your identity and password on FreeBMD.');
				$session->set('message_class_1', 'alert alert-primary');
				$session->set('message_2', '');
				$session->set('message_class_2', '');
				
				$session->set('identity', '');
				$session->set('newpassword', '');
			}
		
		// show view
		echo view('templates/header');
		echo view('linBMD2/change_password');
		echo view('templates/footer');
	}
	
	public function change_password_step2()
	{
		// initialise method
		$session = session();
		$model = new Identity_Model();
		
		// get user data
		$session->set('identity', $this->request->getPost('identity'));
		$session->set('newpassword', $this->request->getPost('newpassword'));
		
		// find identity entered by user
		$identity = $model->where('BMD_user', $session->identity)->find();
		// was it found?
		if ( ! $identity )
			{
				$session->set('message_2', 'This Identity is not registered on this system. => '.$session->identity);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('identity/change_password_step1/1') );
			}

		// test identity / password on FreeBMD by trying to upload a file
		// set curl handle and results file handle - need to get defaults from parameters table because the common_helper has not yet been run.
		// load autoupload url for curl
		$parameter = $parameter_model->where('Parameter_key', 'autouploadurl')->findAll();
		$session->set('autouploadurl', $parameter[0]['Parameter_value']);
		// load programme name
		$parameter = $parameter_model->where('Parameter_key', 'programname')->findAll();
		$session->set('programname', $parameter[0]['Parameter_value']);
		// load version
		$parameter = $parameter_model->where('Parameter_key', 'version')->findAll();
		$session->set('version', $parameter[0]['Parameter_value']);
		// load uploadagent
		$parameter = $parameter_model->where('Parameter_key', 'uploadagent')->findAll();
		$session->set('uploadagent', $parameter[0]['Parameter_value']);
		// set curl
		$curl_url = $session->autouploadurl;
		$ch = curl_init($curl_url);
		$fp = fopen(getcwd()."/curl_result.txt", "w");				
		// set up the fields to pass
		$postfields = array(
										"UploadAgent" => $session->uploadagent,
										"user" => $session->identity,
										"password" => $session->password,
										"file" => "TEST",
										"content2" => @getcwd()."/curl_result.txt",
										"data_version" => "districts.txt:??"
										);
		// set the curl options
		curl_setopt($ch, CURLOPT_USERAGENT, $session->programname.':'.$session->version);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		// execute curl
		if ( curl_exec($ch) === false )
			{
				// problem so send error message
				$session->set('message_2', 'A technical problem occurred. Send an email to '.$session->linbmd2_email.' describing what you were doing when the error occurred => Failed to fetch references in Identity::change_password_step2, around line 279 => '.$curl_url);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('identity/change_password_step1/1') );
			}
		// close the curl and file handles
		curl_close($ch);
		fclose($fp);
		// search the curl result for error
		$fp = fopen(getcwd()."/curl_result.txt", "r");
		while (!feof($fp))
		{
			$buffer = fgets($fp);
			if (strpos($buffer, "passwordfailed") !== FALSE)
				{
					fclose($fp);
					$session->set('message_2', 'This identity and password is not valid for the FreeBMD site. Do you have a FreeBMD account? => '.$session->identity);
					$session->set('message_class_2', 'alert alert-danger');
					return redirect()->to( base_url('identity/change_password_step1/1') );
				}
		}
			
		// All good so update to database
		$data = [
						'BMD_password' => $session->newpassword
					];
		$model->update($identity[0]['BMD_identity_index'], $data);
		
		// go back to signon
		$session->set('message_2', 'Your password has been changed on this system.');
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('identity/signin_step1/2') );
	}
	
	public function retrieve_password_step1($start_message)
	{
		// initialise
		$session = session();
		
		// if retrieve password step 1 = 0 it was called from signin view
		if ( $start_message == 0 )
			{
				$session->set('message_1', 'Retrieve your password by entering the following information. You will receive an email with your password.');
				$session->set('message_class_1', 'alert alert-primary');
				$session->set('message_2', '');
				$session->set('message_class_2', '');
				
				$session->set('identity', '');
				$session->set('email', '');
			}
		
		// show view
		echo view('templates/header');
		echo view('linBMD2/retrieve_password');
		echo view('templates/footer');
	}
	
	public function retrieve_password_step2()
	{
		// initialise method
		$session = session();
		$model = new Identity_Model();
		
		// get user data
		$session->set('identity', $this->request->getPost('identity'));
		$session->set('email', $this->request->getPost('email'));

		// find identity entered by user
		$session->set('user', $model->where('BMD_user', $session->identity)->find());
		// was it found? 
		if ( count($session->user) == 0 )
			{
				$session->set('message_2', 'This Identity is not registered on this system. => '.$session->identity);
				$session->set('message_class_2', 'alert alert-danger');
				// add 1 to redirect so that messages are not reset
				return redirect()->to( base_url('identity/retrieve_password_step1/1') );
			}
			
		// test email entered is same as that on the identity
		if ( $session->email != $session->user[0]['BMD_email'] )
			{
				$session->set('message_2', 'The email you entered is not valid for your account. => '.$session->email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('identity/retrieve_password_step1/1') );
			}
			
		// All good so send email to user
		$session->set('email_return_route', 'identity/signin_step1/1');
		return redirect()->to( base_url('transcribe/send_email/identity') );
	}
}
