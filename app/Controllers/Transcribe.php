<?php namespace App\Controllers;

use App\Models\Header_Model;
use App\Models\Detail_Data_Model;
use App\Models\Syndicate_Model;
use App\Models\Allocation_Model;
use App\Models\Identity_Model;
use App\Models\Transcription_Cycle_Model;
use App\Models\Parameter_Model;
use App\Models\Districts_Model;
use App\Models\Volumes_Model;
use App\Models\Firstname_Model;
use App\Models\Surname_Model;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Transcribe extends BaseController
{
	function __construct() 
	{
        helper('common');
        helper('image');
    }
	
	public function transcribe_step1($start_message)
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		$allocation_model = new Allocation_Model();
		$syndicate_model = new Syndicate_Model();
		$transcription_cycle = new Transcription_Cycle_Model();
		
		switch ($start_message) 
			{
				case 0:
					// load variables from common_helper.php
					load_variables();
					// message defaults
					$session->set('message_1', 'Please select the action you wish to perform on the BMD file and click GO. Or create a new BMD file.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					// flow control
					$session->set('show_view_type', 'transcribe');
					// set defaults
					$session->set('close_header', 'N');
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Please select the action you wish to perform on the BMD file and click GO. Or create a new BMD file.');
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
			}
			
		// get uncompleted headers
		$session->headers = $header_model	->where('BMD_identity_index', $session->BMD_identity_index)
																	->where('header.BMD_header_status', '0')
																	->join('allocation', 'header.BMD_allocation_index = allocation.BMD_allocation_index')
																	->join('syndicate', 'header.BMD_syndicate_index = syndicate.BMD_syndicate_index')
																	->select('header.BMD_header_index, header.BMD_file_name, header.BMD_scan_name, header.BMD_records, header.BMD_start_date, 
																					header.BMD_submit_date, header.BMD_submit_status, header.BMD_last_action, allocation.BMD_allocation_name, syndicate.BMD_syndicate_name')
																	->findAll();
				
		// were any found?
		if ( ! $session->headers )
			{
				$session->set('message_2', 'You have no open BMD files to work on. Please create a new one.');
				$session->set('message_class_2', 'alert alert-danger');
			}
		
		// show open headers for this user for view user_home																		
		echo view('templates/header');
		switch ($session->show_view_type) 
			{
				case 'transcribe':
					echo view('linBMD2/user_home');
					break;
				case 'close_header':
					echo view('linBMD2/transcribe_close_header');
					break;
				case 'verify_BMD':
					echo view('linBMD2/transcribe_verify_BMD');
					break;
				case 'verify_trans':
					echo view('linBMD2/transcribe_verify_trans');
					switch ($session->transcribe_allocation[0]['BMD_type']) 
						{
							case 'B':
								echo view('linBMD2/transcribe_births_show_details');
								break;
							case 'M':
								echo view('linBMD2/transcribe_marriages_show_details');
								break;
							case 'D':
								echo view('linBMD2/transcribe_deaths_show_details');
								break;
						}	
					break;
				case 'image_parameters':
					echo view('linBMD2/transcribe_image_parameters');
					break;
			}
		echo view('templates/footer');
	}	
	
	public function transcribe_next_action()
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		$allocation_model = new Allocation_Model();
		$syndicate_model = new Syndicate_Model();
		$identity_model = new Identity_Model();
		$transcription_cycle_model = new Transcription_Cycle_Model();
		// destroy any feh windows
		shell_exec('pkill feh');
		$session->remove('feh_show');
		// get inputs
		$BMD_header_index = $this->request->getPost('BMD_header_index');
		$session->set('BMD_cycle_code', $this->request->getPost('BMD_next_action'));
		$session->set('BMD_cycle_text', $transcription_cycle_model	->where('BMD_cycle_code', $session->BMD_cycle_code)
																												->where('BMD_cycle_type', 'TRANS')
																												->find());
		// get header 
		$session->transcribe_header = $header_model->where('BMD_header_index',  $BMD_header_index)->find();
		if ( ! $session->transcribe_header )
			{
				$session->set('message_2', 'Invalid header, please select again.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// update header current action with selected next action
		$data =	[
							'BMD_last_action' => $session->BMD_cycle_text[0]['BMD_cycle_name'],
						];
		$header_model->update($BMD_header_index, $data);
		// perform action selected
		switch ($session->BMD_cycle_code) 
			{
				case 'NONE': // nothing was selected
					$session->set('message_2', 'Please select an action to perform from the dropdown.');
					$session->set('message_class_2', 'alert alert-danger');
					return redirect()->to( base_url('transcribe/transcribe_step1/2') );
					break;
				case 'INPRO': // in progress
					// get allocation
					$session->transcribe_allocation = $allocation_model->where('BMD_allocation_index',  $session->transcribe_header[0]['BMD_allocation_index'])->find();
					$session->remove('feh_show');
					if ( ! $session->transcribe_allocation )
						{
							$session->set('message_2', 'Invalid allocation, please select again in transcribe/transcribe_next_action. Send email to '.$session->linbmd2_email);
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('transcribe/transcribe_step1/2') );
						}
					// redirect to controller for the type
					switch ($session->transcribe_allocation[0]['BMD_type']) 
						{
							case 'B':
								return redirect()->to( base_url('births/transcribe_births_step1/0') );
								break;
							case 'M':
								return redirect()->to( base_url('marriages/transcribe_marriages_step1/0') );
								break;
							case 'D':
								return redirect()->to( base_url('deaths/transcribe_deaths_step1/0') );
								break;
							default:
						}
				case 'UPBMD': // upload BMD file
					return redirect()->to( base_url('transcribe/upload_BMD_file/'.$BMD_header_index) );
					break;
				case 'UPDET': // show upload return message
					return redirect()->to( base_url('transcribe/submit_details/'.$BMD_header_index) );
					break;
				case 'CLOST': // close BMD file
					$session->set('close_header', 'N');
					return redirect()->to( base_url('transcribe/close_header_step1/'.$BMD_header_index) );
					break;
				case 'VERIT': // verify transcription file
					return redirect()->to( base_url('transcribe/verify_BMD_trans_step1/'.$BMD_header_index) );
					break;
			}							
	}
	
	public function create_BMD_file($BMD_header_index)
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		$allocation_model = new Allocation_Model();
		$syndicate_model = new Syndicate_Model();
		$identity_model = new Identity_Model();
		$transcription_cycle_model = new Transcription_Cycle_Model();
		$detail_data_model = new Detail_Data_Model();
		// session trancribe header already exists, now get other stuff
		// get allocation
		$session->transcribe_allocation = $allocation_model->where('BMD_allocation_index',  $session->transcribe_header[0]['BMD_allocation_index'])->find();
		if ( ! $session->transcribe_allocation )
			{
				$session->set('message_2', 'Invalid allocation in Transcribe/create_BMD_file. Send an email to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// get syndicate
		$session->transcribe_syndicate = $syndicate_model->where('BMD_syndicate_index',  $session->transcribe_header[0]['BMD_syndicate_index'])->find();
		if ( ! $session->transcribe_syndicate )
			{
				$session->set('message_2', 'Invalid syndicate inTranscribe/create_BMD_file. Send an email to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// get identity
		$session->transcribe_identity = $identity_model->where('BMD_identity_index',  $session->transcribe_header[0]['BMD_identity_index'])->find();
		if ( ! $session->transcribe_identity )
			{
				$session->set('message_2', 'Invalid identity in Transcribe/create_BMD_file. Send an email to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// get detail data
		$session->detail_data = $detail_data_model->where('BMD_header_index',  $session->transcribe_header[0]['BMD_header_index'])->findAll();
		if ( ! $session->detail_data )
			{
				$session->set('message_2', 'No detail data found for this header. Have you completed transcribing the scan?');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
			
		// build BMD file header lines
		// build file path/name
		$BMD_file = getcwd().'/BMD_files/'.$session->transcribe_header[0]['BMD_file_name'].'.BMD';
		// test BMD file exists, delete it if so, else open it.
		if ( file_exists($BMD_file) === true )
			{
				unlink($BMD_file);
			}
		// create and open file in append mode
		$fp = fopen($BMD_file, 'a');
		if ( $fp === false )
			{
				$session->set('message_2', 'Cannot create BMD file in Transcribe/create_BMD_file. Send an email to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// write header lines to file
		// first header line, eg +INFO,dreamstogo@gmail.com,Password,SEQUENCED,BIRTHS
		$write_line = "+INFO,".$session->transcribe_identity[0]['BMD_email'].",Password,SEQUENCED,".$session->types_upper[$session->transcribe_allocation[0]['BMD_type']]."\r\n";
		fwrite($fp, $write_line);
		// second header line, eg #,99,dreamstogo,Richard Oliver,1988BD0430.BMD,04-Aug-2020,Y,N,N,D,0,8.2
		$write_line = "#,99,".$session->transcribe_identity[0]['BMD_user'].",".$session->transcribe_syndicate[0]['BMD_syndicate_name'].",".$session->transcribe_header[0]['BMD_file_name'].".BMD,".$session->transcribe_header[0]['BMD_start_date'].",Y,N,N,D,0,8.2\r\n";
		fwrite($fp, $write_line);
		// third header line, eg #,
		$write_line = "#,\r\n";
		fwrite($fp, $write_line);
		// fourth header line, eg +S,1988,,GUS/1988/Births/OFHS-03,05-Aug-2020 or
		// fourth header line, eg +S,1988,Sep,GUS/1988/Births/OFHS-03,05-Aug-2020 if quarter based
		// look for quarter
		$exploded_scan_path = explode('/', $session->transcribe_allocation[0]['BMD_reference']);
		$quarter_number = array_search($exploded_scan_path[3], $session->quarters_short_long);
		if ( $quarter_number ) 
			{
				$write_line = "+S,".$session->transcribe_allocation[0]['BMD_year'].",".$session->quarters[$quarter_number].",".$session->transcribe_allocation[0]['BMD_reference'].",".$session->current_date."\r\n";
			}
		else
			{
				$write_line = "+S,".$session->transcribe_allocation[0]['BMD_year'].",,".$session->transcribe_allocation[0]['BMD_reference'].",".$session->current_date."\r\n";
			}
		fwrite($fp, $write_line);
		// fifth header line, eg +CREDIT,Hilary Wright,dreamstogo@gmail.com,dreamstogo
		$write_line = "+CREDIT,".$session->transcribe_identity[0]['BMD_realname'].",".$session->transcribe_identity[0]['BMD_email'].",".$session->transcribe_identity[0]['BMD_user']."\r\n";
		fwrite($fp, $write_line);
		// current page line, eg +PAGE,0430
		$write_line = "+PAGE,".str_pad($session->transcribe_header[0]['BMD_current_page'],4,"0",STR_PAD_LEFT)."\r\n";
		fwrite($fp, $write_line);
		// detail lines eg  for BIRTHS  => DUNN,CHRISTOPHER DAVID,BROWN,S SHIELDS,09.88,2,1403
		// detail lines eg  for MARRIAGES  => DUNN,CHRISTOPHER DAVID,BROWN,S SHIELDS,2,1403
		foreach ( $session->detail_data as $dd )
			{
				$given_names = $dd['BMD_firstname'];
				if ( $dd['BMD_secondname'] != '' )
					{
						$given_names = $given_names." ".$dd['BMD_secondname'];
					}
				if ( $dd['BMD_thirdname'] != '' )
					{
						$given_names = $given_names." ".$dd['BMD_thirdname'];
					}
				switch ($session->transcribe_allocation[0]['BMD_type']) 
					{
						case "B":
							$write_line = $dd['BMD_surname'].",".$given_names.",".$dd['BMD_partnername'].",".$dd['BMD_district'].",".$dd['BMD_registration'].",".$dd['BMD_volume'].",".$dd['BMD_page']."\r\n";
							break;
						case "M":
							$write_line = $dd['BMD_surname'].",".$given_names.",".$dd['BMD_partnername'].",".$dd['BMD_district'].",".$dd['BMD_volume'].",".$dd['BMD_page']."\r\n";
							break;
						case "D":
							if ( $dd['BMD_age'] == 999 )
								{
									$write_line = $dd['BMD_surname'].",".$given_names.",,".$dd['BMD_district'].",".$dd['BMD_volume'].",".$dd['BMD_page']."\r\n";
								}
							else
								{
									$write_line = $dd['BMD_surname'].",".$given_names.",".$dd['BMD_age'].",".$dd['BMD_district'].",".$dd['BMD_volume'].",".$dd['BMD_page']."\r\n";
								}
							break;
					}
				fwrite($fp, $write_line);
				// any comments?
				switch ($dd['BMD_comment_type'])
					{
						case 'C':
							// eg #COMMENT(5) reads DUNKLEY or HART for mother's name
							$write_line = "#COMMENT(".$dd['BMD_comment_span'].") ".$dd['BMD_comment_text']."\r\n";
							fwrite($fp, $write_line);
							break;
						case 'T':
							$write_line = "#THEORY(".$dd['BMD_comment_span'].") ".$dd['BMD_comment_text']."\r\n";
							fwrite($fp, $write_line);
							break;
						case 'N':
							$write_line = "#(".$dd['BMD_comment_span'].") ".$dd['BMD_comment_text']."\r\n";
							fwrite($fp, $write_line);
							break;
					}			
			}
		// next page line, eg +PAGE,0430
		$write_line = "+PAGE,".str_pad($session->transcribe_header[0]['BMD_next_page'],4,"0",STR_PAD_LEFT)."\r\n";
		fwrite($fp, $write_line);
		// close the file
		fclose($fp);	

	}
		
	public function upload_BMD_file($BMD_header_index)
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		$identity_model = new Identity_Model();
		$allocation_model = new Allocation_Model();
		// create the BMD upload file
		$this->create_BMD_file($BMD_header_index);
		// does BMD file already exist on FreeBMD?
		BMD_file_exists_on_FreeBMD($session->transcribe_header[0]['BMD_file_name']);
		// create the curl file
		$cfile = curl_file_create(getcwd().'/BMD_files/'.$session->transcribe_header[0]['BMD_file_name'].'.BMD', $session->transcribe_header[0]['BMD_file_name']);
		// set up the fields to pass
		switch ($session->BMD_file_exists_on_FreeBMD)
		{
			case '0': // file does not already exist on FreeBMD
				$postfields = array(
												"UploadAgent" => $session->uploadagent,
												"user" => $session->user[0]['BMD_user'],
												"password" => $session->user[0]['BMD_password'],
												"file" => $session->transcribe_header[0]['BMD_file_name'],
												"content2" => $cfile,
												"data_version" => "districts.txt:??"
												);
				break;
			case '1': // file already exists on FreeBMD
				$postfields = array(
												"UploadAgent" => $session->uploadagent,
												"user" => $session->user[0]['BMD_user'],
												"password" => $session->user[0]['BMD_password'],
												"file_update" => $session->transcribe_header[0]['BMD_file_name'],
												"content2" => $cfile,
												"data_version" => "districts.txt:??"
												);
				break;
		}
		
		// set up the curl
		$curl_url = $session->autouploadurl;
		$ch = curl_init($curl_url);
		$fp = fopen(getcwd()."/curl_result.txt", "w");				
		curl_setopt($ch, CURLOPT_USERAGENT, $session->programname.':'.$session->version);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		// execute curl
		if ( curl_exec($ch) === false )
			{
				// problem so send error message
				$session->set('message_2', 'A technical problem occurred. Send an email to '.$session->linbmd2_email.' describing what you were doing when the error occurred => Transcribe::upload_BMD_file, around line 381 => '.$curl_url.' => '.curl_error($ch));
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('view', 1);
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// close the curl handle and file handle
		curl_close($ch);
		fclose($fp);
		// check results
		$fp = fopen(getcwd()."/curl_result.txt", "r");
		while (!feof($fp))
		{
			$buffer = fgets($fp);
			if (strpos($buffer, "fileupload result") !== FALSE)
				{
					$upload_status = explode("=", $buffer);
					// test status
					$upload_status[1] = rtrim($upload_status[1]);
					switch ($upload_status[1]) 
					{
						case "OK":
							// update header
							$data =	[
												'BMD_submit_date' => $session->current_date,
												'BMD_submit_status' => $upload_status[1],
												'BMD_submit_message' => file_get_contents(getcwd()."/curl_result.txt"),
											];
							$header_model->update($BMD_header_index, $data);
							// update total number ever transcribed by this user
							$header = $header_model->where('BMD_header_index', $BMD_header_index)->find();
							$data =	[
												'BMD_total_records' => $session->user[0]['BMD_total_records'] + $header[0]['BMD_records']
											];
							$identity_model->update($session->user[0]['BMD_identity_index'], $data);
							// update allocation with last page uploaded
							$data =	[
												'BMD_last_uploaded' => $session->transcribe_header[0]['BMD_current_page']
											];
							$allocation_model->update($session->transcribe_header[0]['BMD_allocation_index'], $data);
							//
							switch ($session->BMD_file_exists_on_FreeBMD)
								{
									case '0': // file did not already exist on FreeBMD
										$session->set('message_2', 'BMD file successfully UPLOADED to FreeBMD.');
										break;
									case '1': // file already existed on FreeBMD
										$session->set('message_2', 'BMD file successfully REPLACED on FreeBMD.');
										break;
								}
							$session->set('message_class_2', 'alert alert-success');
							break;
						case "failed":
							$data =	[
												'BMD_submit_date' => $session->current_date,
												'BMD_submit_status' => $upload_status[1],
												'BMD_submit_message' => file_get_contents(getcwd()."/curl_result.txt"),
											];
							$header_model->update($BMD_header_index, $data);
							//
							$session->set('message_2', 'BMD file upload failed. See errors message by clicking on the status of the file concerned.');
							$session->set('message_class_2', 'alert alert-danger');
							break;
						case "warnings":
							$data =	[
												'BMD_submit_date' => $session->current_date,
												'BMD_submit_status' => $upload_status[1],
												'BMD_submit_message' => file_get_contents(getcwd()."/curl_result.txt"),
											];
							$header_model->update($BMD_header_index, $data);
							// update total number ever transcribed by this user
							$header = $header_model->where('BMD_header_index', $BMD_header_index)->find();
							$data =	[
												'BMD_total_records' => $session->user[0]['BMD_total_records'] + $header[0]['BMD_records']
											];
							$identity_model->update($session->user[0]['BMD_identity_index'], $data);
							// update allocation with last page uploaded
							$data =	[
												'BMD_last_uploaded' => $session->transcribe_header[0]['BMD_current_page']
											];
							$allocation_model->update($session->transcribe_header[0]['BMD_allocation_index'], $data);
							//
							$session->set('message_2', 'BMD file uploaded to FreeBMD but with warnings. See warnings by clicking on the status of the file concerned.');
							$session->set('message_class_2', 'alert alert-warning');
							break;
					}
				}
		}		
				
		// all done
		// close the file handle
		fclose($fp);
		return redirect()->to( base_url('transcribe/transcribe_step1/2') );
	}
	
	public function submit_details($BMD_header_index)
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		// show upload details for this header																				
		echo view('templates/header');
		echo view('linBMD2/transcribe_submit_details');
		echo view('templates/footer');
	}
	
	public function close_header_step1($BMD_header_index)
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		// can I close this file = if not uploaded successfully
		if ( $session->transcribe_header[0]['BMD_submit_date'] == '' OR $session->transcribe_header[0]['BMD_submit_status'] == 'failed' )
			{
				$session->set('message_2', 'This file has not been uploaded or it was not uploaded successfully. Normally you should not close it.');
				$session->set('message_class_2', 'alert alert-danger');
			}
		else
			{
				$session->set('message_2', 'Please confirm close of this BMD file.');
				$session->set('message_class_2', 'alert alert-primary');
			}
		// ask for confirmation
		$session->set('show_view_type', 'close_header');
		return redirect()->to( base_url('transcribe/transcribe_step1/2') );
	}
	
	public function close_header_step2()
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		// get inputs
		$session->set('close_header', $this->request->getPost('close_header'));
		// test for close
		if ( $session->close_header == 'N' )
			{
				$session->set('show_view_type', 'transcribe');
				$session->set('message_2', 'You did not confirm close. This file is still open.');
				$session->set('message_class_2', 'alert alert-warning');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		else
			{
				$data =	[
									'BMD_end_date' => $session->current_date,
									'BMD_header_status' => '1',
									'BMD_last_action' => $session->BMD_cycle_text[0]['BMD_cycle_name'],
								];
				$header_model->update($session->transcribe_header[0]['BMD_header_index'], $data);
				$session->set('show_view_type', 'transcribe');
				$session->set('message_2', 'BMD file has been closed successfully.');
				$session->set('message_class_2', 'alert alert-success');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
	}
	
	public function verify_BMD_file_step1($BMD_header_index)
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		// get header
		$header = $header_model->where ('BMD_header_index', $BMD_header_index)->find();
		if ( ! $header )
			{
				$session->set('show_view_type', 'transcribe');
				$session->set('message_2', 'A problem occurred in Transribe::verify_BMD_file_step1. Send email to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// load bmd file to array
		$session->set('verify_BMD_file', file(getcwd().'/BMD_files/'.$header[0]['BMD_file_name'].'.BMD'));
		// show file
		$session->set('show_view_type', 'verify_BMD');
		$session->set('message_2', 'Verify BMD that will be uploaded to FreeBMD for this scan. To change data go to transcribe from scan option.');
		$session->set('message_class_2', 'alert alert-warning');
		return redirect()->to( base_url('transcribe/transcribe_step1/2') );	
	}
	
	public function verify_BMD_trans_step1($BMD_header_index)
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		$detail_data_model = new Detail_Data_Model();
		$allocation_model = new Allocation_Model();
		// get header
		$session->transcribe_header = $header_model->where ('BMD_header_index', $BMD_header_index)->find();
		if ( ! $session->transcribe_header )
			{
				$session->set('show_view_type', 'transcribe');
				$session->set('message_2', 'A problem occurred in Transribe::verify_BMD_trans_step1. Send email to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// get allocation
		$session->transcribe_allocation = $allocation_model->where('BMD_allocation_index',  $session->transcribe_header[0]['BMD_allocation_index'])->find();
		if ( ! $session->transcribe_allocation )
			{
				$session->set('message_2', 'Invalid allocation, please select again in transcribe/verify_BMD_trans_step1. Send email to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// get detail data
		$session->transcribe_detail_data = $detail_data_model	->where('BMD_header_index',  $session->transcribe_header[0]['BMD_header_index'])
																									->orderby('BMD_line_sequence', 'ASC')
																									->findAll();
		if ( ! $session->transcribe_detail_data )
			{
				$session->set('message_2', 'No detail found to verify!');
				$session->set('message_class_2', 'alert alert-warning');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// set feh command
		$session->set('feh_command', 'feh --zoom '.$session->transcribe_header[0]['BMD_image_zoom'].' --geometry '.$session->transcribe_header[0]['BMD_image_x'].'x'.$session->transcribe_header[0]['BMD_image_y'].'+50+350 --scroll-step 10 '.getcwd()."/Scans/".$session->transcribe_header[0]['BMD_scan_name'].' /dev/null >/dev/null &');
		// show file
		$session->set('show_view_type', 'verify_trans');
		$session->set('message_2', 'Verify the transcription data before creating the BMD file that will be uploaded to FreeBMD for this scan. To change data go to transcribe from scan option.');
		$session->set('message_class_2', 'alert alert-warning');
		return redirect()->to( base_url('transcribe/transcribe_step1/2') );	
	}
	
	public function search_synonyms()
	{
		// initialise
		$session = session();
		$districts_model = new Districts_Model();
		$volumes_model = new Volumes_Model();
		// get search term
		$request = \Config\Services::request();
		$search_term = $request->getPostGet('term');
		// get matching synonym
		$results = $districts_model	->like('District_name', $search_term, 'after')
														->findAll();
		// now read all results to find only those with a volume matching this registration
		// set values in order to find this registration in range
		switch ($session->transcribe_allocation[0]['BMD_type']) 
			{
				case 'B':
					$registration = explode('.', $session->registration);
					$year = $session->transcribe_allocation[0]['BMD_year'];
					$quarter = $session->month_to_quarter[$registration[0]];
					break;
				case 'M':
					$year = $session->transcribe_allocation[0]['BMD_year'];
					$quarter = str_pad($session->transcribe_allocation[0]['BMD_quarter'], 2, '0', STR_PAD_LEFT);
					break;
				case 'D':
					$year = $session->transcribe_allocation[0]['BMD_year'];
					$quarter = str_pad($session->transcribe_allocation[0]['BMD_quarter'], 2, '0', STR_PAD_LEFT);
					break;
			}
		// find volume range
		foreach ( $results as $result )
			{
				$volumes =  $volumes_model->where('district_index', $result['district_index'])->findAll();
				if ( $volumes )
					{
						foreach ( $volumes as $volume )
							{	
								if ( $year.$quarter >= $volume['volume_from'] AND $year.$quarter <= $volume['volume_to'])
									{
										$search_result[] = $result['District_name'];
									}
							}
					}
			}
		// return result
		echo json_encode($search_result);
	}
	
	public function search_districts()
	{
		// initialise
		$session = session();
		$districts_model = new Districts_Model();
		// get search term
		$request = \Config\Services::request();
		$search_term = $request->getPostGet('term');
		// get matching district
		$results = $districts_model	->like('District_name', $search_term, 'after')
														->findAll();
		// prepare return array
		$search_result = array();
		foreach($results as $result)
			{
				$search_result[] = $result['District_name'];
			}
		// return result
		echo json_encode($search_result);
	}
	
	public function search_volumes()
	{
		// initialise
		$session = session();
		$districts_model = new Districts_Model();
		$volumes_model = new Volumes_Model();
		// get search term
		$request = \Config\Services::request();
		$search_term = $request->getPostGet('term');
		// get matching volumes join to districts master
		$results = $volumes_model	->like('volume', $search_term, 'after')
														->join('districts_master', 'volumes.district_index = districts_master.district_index')
														->select('District_name')
														->findAll();
		// prepare return array
		$search_result = array();
		foreach($results as $result)
			{
				if ( array_search($result['District_name'], $search_result) === false )
					{
						$search_result[] = $result['District_name'];
					}
			}
		// return result
		echo json_encode($search_result);
	}
	
	 public function search_firstnames()
	{
		// initialise
		$session = session();
		$firstname_model = new Firstname_Model();
		// get search term
		$request = \Config\Services::request();
		$search_term = $request->getPostGet('term');
		// get matching firstname
		$results = $firstname_model		->like('Firstname', $search_term, 'after')
															->orderby('Firstname_popularity', 'DESC')
															->findAll();
		// prepare return array
		$search_result = array();
		foreach($results as $result)
			{
				$search_result[] = $result['Firstname'];
			}
		// return result
		echo json_encode($search_result);
	}
	
	public function search_surnames()
	{
		// initialise
		$session = session();
		$surname_model = new Surname_Model();
		// get search term
		$request = \Config\Services::request();
		$search_term = $request->getPostGet('term');
		// get matching surname
		$results = $surname_model	->like('Surname', $search_term, 'after')
														->orderby('Surname_popularity', 'DESC')
														->findAll();
		// prepare return array
		$search_result = array();
		foreach($results as $result)
			{
				$search_result[] = $result['Surname'];
			}
		// return result
		echo json_encode($search_result);
	}
	
	public function image_parameters_step1($start_message)
	{
		// initialise
		$session = session();
		//set defaults
		switch ($start_message) 
			{
				case 0:
					// message defaults
					$session->set('message_1', 'Set the zoom percentage, horizontal and vertical image size to suit your requirements.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					$session->set('image_zoom', 0);
					$session->set('image_width', 0);
					$session->set('image_height', 0);
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Set the zoom percentage, horizontal and vertical image size to suit your requirements.');
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
			}
		// show current settings and allow chnage to them
		$session->set('show_view_type', 'image_parameters');
		$session->set('message_2', 'Current image parameters are shown.');
		$session->set('message_class_2', 'alert alert-warning');
		return redirect()->to( base_url('transcribe/transcribe_step1/2') );
	}
	
	public function image_parameters_step2($BMD_header_index)
	{
		// initialise
		$session = session();
		$header_model = new Header_Model();
		// get inputs
		$session->set('image_zoom', $this->request->getPost('image_zoom'));
		$session->set('image_width', $this->request->getPost('image_width'));
		$session->set('image_height', $this->request->getPost('image_height'));
		// do tests
		// zoom
		if ( $session->image_zoom == '' OR $session->image_zoom == '0' OR is_numeric($session->image_zoom) === false OR  $session->image_zoom < 0 )
			{
				$session->set('show_view_type', 'image_parameters');
				$session->set('message_2', 'Image ZOOM cannot be blank, zero, non_numeric or less than zero.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// width
		if ( $session->image_width == '' OR $session->image_width == '0' OR is_numeric($session->image_width) === false OR  $session->image_width < 0 )
			{
				$session->set('show_view_type', 'image_parameters');
				$session->set('message_2', 'Image WIDTH cannot be blank, zero, non_numeric or less than zero.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// height
		if ( $session->image_height == '' OR $session->image_height == '0' OR is_numeric($session->image_height) === false OR  $session->image_height < 0 )
			{
				$session->set('show_view_type', 'image_parameters');
				$session->set('message_2', 'Image HEIGHT cannot be blank, zero, non_numeric or less than zero.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
			
		// all good
		// update header
		$data =	[
							'BMD_image_zoom' => $session->image_zoom,
							'BMD_image_x' => $session->image_width,
							'BMD_image_y' => $session->image_height,
						];
		$header_model->update($session->transcribe_header[0]['BMD_header_index'], $data);
		// destroy any feh windows
		shell_exec('pkill feh');
		$session->remove('feh_show');
		// reload header 
		$session->transcribe_header = $header_model->where('BMD_header_index',  $session->transcribe_header[0]['BMD_header_index'])->find();
		// reset image
		return redirect()->to( base_url($session->return_route_step1) );
	}
	
	public function delete_line_step1($line_index)
	{
		// initialse
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		// get the line and load fields
		$session->set('line_edit_data', $detail_data_model->where('BMD_index', $line_index)->find());
		// set message
		$session->set('message_2', 'You requested to delete line number => '.$session->line_edit_data[0]['BMD_line_sequence']);
		$session->set('message_class_2', 'alert alert-danger');
		// show view
		echo view('templates/header');
		echo view('linBMD2/delete_line_confirmation');
		echo view('templates/footer');
	}
	
	public function delete_line_step2()
	{
		// initialse
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		$header_model = new Header_Model();
		// get input
		$session->set('delete_ok', $this->request->getPost('confirm'));
		// if confirmed delete the line
		if ( $session->delete_ok == 'Y' )
			{
				// delete detail line
				$detail_data_model->delete($session->line_edit_data[0]['BMD_index']);
				// reduce header count
				$data =	[
									'BMD_records' => $session->transcribe_header[0]['BMD_records'] - 1,
								];
				$header_model->update($session->transcribe_header[0]['BMD_header_index'], $data);
				// load the header again
				$session->transcribe_header = $header_model->where('BMD_header_index',  $session->transcribe_header[0]['BMD_header_index'])->find();
			}
		// return
		return redirect()->to( base_url($session->return_route_step1) );
	}
	
	public function send_email($email_type)
	{
		// initialise
		$session = session();
		require '../vendor/autoload.php';
		$mail = new PHPMailer();
		$result = '';
		$result_dump = '';
		$mail->isSMTP();
		$mail->SMTPDebug = SMTP::DEBUG_SERVER;
		$mail->Host = 'mail.mailo.com';
		$mail->SMTPAuth = true;
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
		$mail->Username = 'linBMD2@mailo.com';
		$mail->Password = '467RsBYs7PQMZ_tBp48W';
		$mail->Port = 587;
		$mail->isHTML(true);
		$mail->addBCC('linBMD2@mailo.com');
		
		// set up message specific parameters
		switch ($email_type) 
			{
				case 'allocation':
					$mail->addBCC('linBMD2@mailo.com');
					$leader = explode(' ', $session->transcribe_syndicate[0]['BMD_syndicate_leader']);
					$mail->setFrom($session->user[0]['BMD_email']);
					$mail->addAddress($session->transcribe_syndicate[0]['BMD_syndicate_email']);
					$mail->addReplyTo($session->user[0]['BMD_email']);
					$mail->Subject = 'Message from linBMD2 - Allocation '.$session->transcribe_allocation[0]['BMD_allocation_name'].' completed';
					$mail->Body = 	'<html>Hello '
												.$leader[0]
												.','
												.'<br><br>I completed the allocation '
												.'<b>'
												.$session->transcribe_allocation[0]['BMD_allocation_name']
												.'</b>'
												.' on '
												.$session->transcribe_allocation[0]['BMD_end_date']
												.'.'
												.'<br><br>Please provide me with another allocation.'
												.'<br><br>Thank you.'
												.'<br><br>Best wishes,'
												.'<br><br>'
												.$session->user[0]['BMD_realname']
												.'<br><br>'
												.$session->user[0]['BMD_user'];
					$mail->AltBody = 'Allocation '.$session->transcribe_allocation[0]['BMD_allocation_name'].'completed';
					// set return message
					$session->set('message_2', 'An email was sent to the syndicate owner informing him/her that this allocation is complete and asking for another allocation.');
					break;
				case 'identity':
					$mail->setFrom('linBMD2@mailo.com', 'linBMD2 admin');
					$mail->addAddress($session->email);
					$mail->Subject = 'linBMD2 Email recovery.';
					$mail->Body 	= 	'<html>Hello '
												.$session->user[0]['BMD_realname']
												.'<br><br>Here is your linBMD2 password => <strong>'
												.$session->user[0]['BMD_password'].
												'</strong>.<br><br>Best Regards<br>linBMD2 Admin</html>';
					$mail->AltBody = 'Here is your linBMD2 password => '.$session->user[0]['BMD_password'];
					// set return message
					$session->set('message_2', 'An email has been sent to your email address with your password.');
					break;
			}
		// send the mail.
		if ( ! $mail->send() )
			{
				$result = 'Internal error ending email, contact '.$session->linbmd2_email;
				$result_dump =  $mail->ErrorInfo;
				$session->set('message_2', $result.' '.$result_dump.' => '. 'for message type '.$email_type);
				$session->set('message_class_2', 'alert alert-warning');
				// show view
				echo view('templates/header');
				echo view('linBMD2/error');
				echo view('templates/footer');
			}
		else
			{	   
				// go back to signon
				$session->set('message_class_2', 'alert alert-success');
				return redirect()->to( base_url($session->email_return_route) );
			}		
	}
}
