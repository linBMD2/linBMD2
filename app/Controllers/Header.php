<?php namespace App\Controllers;

use App\Models\Header_Model;
use App\Models\Syndicate_Model;
use App\Models\Allocation_Model;

class Header extends BaseController
{
	function __construct() 
	{
        helper('common');
    }

	public function create_BMD_step1($start_message)
	{
		// initialise method
		$session = session();
		$syndicate_model = new Syndicate_Model();
		$allocation_model = new Allocation_Model();
		$header_model = new Header_Model();
		// get headers in reverse order
		$headers = $header_model->orderby('BMD_header_index', 'DESC')->findAll();
		// set values
		switch ($start_message) 
			{
				case 0:
					// initialise values
					$session->set('scan_page', $headers[0]['BMD_next_page']);
					$session->set('autocreate', 'Y');
					$session->set('scan_name', '');
					$session->set('fetch_scan', 'Y');
					$session->set('make_current', 'Y');
					$session->set('reopen', 'Y');
					$session->set('view', 1);
					// message defaults
					$session->set('message_1', 'Start a new BMD transcription by selecting the Syndicate and Allocation it is attached to. Your last transcription was '.$headers[0]['BMD_file_name'].', '.$headers[0]['BMD_scan_name']);
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Start a new BMD transcription by selecting the Syndicate and Allocation it is attached to. Your last transcription was '.$headers[0]['BMD_file_name'].', '.$headers[0]['BMD_scan_name']);
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
			}
			
		// get syndicates
		$session->set('syndicates', $syndicate_model->orderby('BMD_syndicate_name', 'ASC')->findAll());
		// get allocations
		$session->set('allocations', $allocation_model	->where('BMD_status', 'Open')->orderby('BMD_allocation_name', 'ASC')->findAll());
		
		echo view('templates/header');
		switch ($session->view) 
			{
				case 1:
					echo view('linBMD2/create_BMD_step1');
					break;
				default:
			}
		echo view('templates/footer');
	}
	
	public function create_BMD_step2()
	{
		// initialise method
		$session = session();
		$syndicate_model = new Syndicate_Model();
		$allocation_model = new Allocation_Model();
		$header_model = new Header_Model();
		
		// get inputs
		$session->set('syndicate', $this->request->getPost('syndicate'));
		$session->set('allocation', $this->request->getPost('allocation'));
		$session->set('scan_page', $this->request->getPost('scan_page'));
		$session->set('autocreate', $this->request->getPost('autocreate'));
		$session->set('scan_name', $this->request->getPost('scan_name'));
		$session->set('fetch_scan', $this->request->getPost('fetch_scan'));
		$session->set('make_current', $this->request->getPost('make_current'));
		
		// get syndicate and allocation
		$input_syndicate = $syndicate_model->where('BMD_syndicate_index',  $session->syndicate)->find();
		if ( ! $input_syndicate )
			{
				$session->set('message_2', 'You must select a syndicate from the dropdown list.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('header/create_BMD_step1/1') );
			}															
		$input_allocation = $allocation_model->where('BMD_allocation_index',  $session->allocation)->find();
		if ( ! $input_allocation )
			{
				$session->set('message_2', 'You must select an allocation from the dropdown list.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('header/create_BMD_step1/1') );
			}													
		
		// do tests
		// is start page numeric?
		if ( ! is_numeric($session->scan_page) )
			{
				$session->set('message_2', 'Scan page number must be numeric.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('header/create_BMD_step1/1') );
			}
		// is scan page in allocation range?
		$page_found = 0;
		foreach  ( range($input_allocation[0]['BMD_start_page'], $input_allocation[0]['BMD_end_page']) as $page )
			{
				if ( $page == $session->scan_page )
					{
						$page_found = 1;
						break;
					}
			}
		if ( $page_found == 0 )
			{
				$session->set('message_2', 'Scan page number is not in the allocation page range => '.$input_allocation[0]['BMD_start_page']. ' to '.$input_allocation[0]['BMD_end_page'].'. Is your page number correct? Have you finished transcribing this allocation?');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('header/create_BMD_step1/1') );
			}
		// autocreate scan name
		if ( $session->autocreate == 'Y' AND $session->scan_name != '' )
			{
				$session->set('message_2', 'If auto create scan name is Yes, you must leave the scan name blank.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/create_BMD_step1/1') );
			}
		if ( $session->autocreate == 'N' AND $session->scan_name == '' )
			{
				$session->set('message_2', 'If auto create scan name is No, you must enter a scan name.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('header/create_BMD_step1/1') );
			}
			
		// ok checks complete
		
		// Create the scan name if autocreate = yes
		// format 1938B1-F-0337.jpg or 1988B-D-0425.jpg
		if ( $session->autocreate == 'Y' )
			{
				// construct part1
				$session->set('scan_name',	$input_allocation[0]['BMD_year']
																.$input_allocation[0]['BMD_type']);
				
				// add quarter number if quarter based
				$exploded_scan_path = explode('/', $input_allocation[0]['BMD_reference']);
				$quarter_number = array_search($exploded_scan_path[3], $session->quarters_short_long);
				if ( $quarter_number ) 
					{
						// quarter was found
						$session->set('scan_name', 	$session->scan_name.$quarter_number);											
					}
				// construct the page number, must be 4 long padded with zeros to left
				$scan_page = str_pad($session->scan_page, 4, "0", STR_PAD_LEFT);
				// construct part 2
				$session->set('scan_name', 	$session->scan_name.'-'.$input_allocation[0]['BMD_letter'].'-'.$scan_page.'.'.$input_allocation[0]['BMD_scan_type']);
			}
			
		// does this scan name already exist on a header?
		$session->set('header', $header_model->where('BMD_scan_name', $session->scan_name)->findAll());
		// found?
		if ( $session->header )
			{
				// is this header closed?
				if ( $session->header[0]['BMD_header_status'] == 1 )
					{
						// exist and closed
						$session->set('message_2', 'The scan '.$session->scan_name.' has already been processed and is closed. Do you wish to reopen it?');
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('view', 2);
						return redirect()->to( base_url('header/create_BMD_step1/1') );
					}
				else
					{
						// exists and open
						$session->set('message_2', 'The scan '.$session->scan_name.' already exists on BMD file '.$session->header[0]['BMD_file_name'].' which is in your open list of BMD files.');
						$session->set('message_class_2', 'alert alert-warning');
						return redirect()->to( base_url('transcribe/transcribe_step1/2') );
					}
			}
			
		// does the scan exist on FreeBMD?
		$curl_url = $session->autoimageurl.'/'.$input_allocation[0]['BMD_reference'].'/'.$session->scan_name;
		$ch = curl_init($curl_url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_USERPWD, $session->user[0]['BMD_user'].':'.$session->user[0]['BMD_password']);			
		if ( curl_exec($ch) === false )
			{
				// problem so send error message
				$session->set('message_2', 'A technical problem occurred. Send an email to '.$session->linbmd2_email.' describing what you were doing when the error occurred => Header::create_BMD_step2, around line 202 => '.$curl_url.' => '.curl_error($ch));
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('header/create_BMD_step1/1') );
			}
		if ( curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200 )
			{
				curl_close($ch);
				$session->set('message_2', 'The scan '.$session->scan_name.' does not exist on FreeBMD server => '.$session->autoimageurl.'/'.$input_allocation[0]['BMD_reference'] );
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('header/create_BMD_step1/1') );
			} 	
		
		// create BMD file name
		$session->set('BMD_file_name', str_replace("-","",$session->scan_name)); $first_token  = strtok('/something', '/');
		$session->set('BMD_file_name', strtok($session->BMD_file_name, '.'));
		
		// does this file name already exist on FreeBMD?
		BMD_file_exists_on_FreeBMD($session->BMD_file_name);
		if ( $session->BMD_file_exists_on_FreeBMD == '1' )
			{
				$session->set('message_2', 'An upload with this name already exists in the FreeBMD site. Verify your input data => '.$session->BMD_file_name.' or visit the FreeBMD site to fix matters.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('header/create_BMD_step1/1') );
			}
		
		// file does not exist on FreeBMD, so its OK to create it in linBMD2
		$data =	[
							'BMD_identity_index' => $session->BMD_identity_index,
							'BMD_allocation_index' => $session->allocation,
							'BMD_syndicate_index' => $session->syndicate,
							'BMD_file_name' => $session->BMD_file_name,
							'BMD_scan_name' => $session->scan_name,
							'BMD_start_date' => $session->current_date,
							'BMD_end_date' => '',
							'BMD_submit_date' => '',
							'BMD_submit_status' => '',
							'BMD_submit_fail_message' => '',
							'BMD_current_page' => $session->scan_page,
							'BMD_next_page' => $session->scan_page + 1,
							'BMD_records' => 0,
							'BMD_last_action' => 'BMD file created',
							'BMD_header_status' => '0',
							'BMD_image_zoom' => 100,
							'BMD_image_x' => 1100,
							'BMD_image_y' => 50,
							'BMD_image_rotate' => 0,
						];
		$id = $header_model->insert($data);
			
		// download the scan
		$curl_url = $session->autoimageurl.'/'.$input_allocation[0]['BMD_reference'].'/'.$session->scan_name;
		$fp = fopen(getcwd()."/Scans/".$session->scan_name, "wb");
		$ch = curl_init($curl_url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FILE, $fp); 
		curl_setopt($ch, CURLOPT_HEADER, 0);  
		curl_setopt($ch, CURLOPT_USERPWD, $session->user[0]['BMD_user'].':'.$session->user[0]['BMD_password']);			
		if ( curl_exec($ch) === false )
			{
				// problem so send error message
				$session->set('message_2', 'A technical problem occurred. Send an email to '.$session->linbmd2_email.' describing what you were doing when the error occurred => Header::create_BMD_step2, around line 307 => '.$curl_url.' => '.curl_error($ch));
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('header/create_BMD_step1/1') );
			}
		curl_close($ch);		
		fclose($fp);
		
		// return
		$session->set('message_2',  'Your new BMD file has been been created and its scan has been downloaded. Start transcribing!');
		$session->set('message_class_2', 'alert alert-success');
		$session->set('reference_extension_control', '0');
		return redirect()->to( base_url('transcribe/transcribe_step1/2') );
	}
	
	public function reopen_BMD_step1($start_message)
	{
		// initialise method
		$session = session();
		
		switch ($start_message) 
			{
				case 0:
					// initialise values
					$session->set('BMD_file', '');
					$session->set('BMD_reopen_confirm', 'N');
					// message defaults
					$session->set('message_1', 'Enter the name of the BMD file you wish to reopen and confirm.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Enter the name of the BMD file you wish to reopen and confirm.');
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
			}
		
		// show views
		echo view('templates/header');
		echo view('linBMD2/header_BMD_reopen');
		echo view('templates/footer');
		
	}
		
	public function reopen_BMD_step2()
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		
		// get user input
		$session->set('BMD_file', $this->request->getPost('BMD_file'));
		$session->set('BMD_reopen_confirm', $this->request->getPost('BMD_reopen_confirm'));
		
		// did user confirm?
		if ($session->BMD_reopen_confirm == 'N') 
			{
					$session->set('message_2', 'You did not confirm reopen.');
					$session->set('message_class_2', 'alert alert-danger');
					return redirect()->to( base_url('transcribe/transcribe_step1/1') );
			}
		
		// user confirmed
		// is the BMD file name blank?
		if ($session->BMD_file == '')
			{
				$session->set('message_2', 'BMD file name cannot be empty.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('header/reopen_BMD_step1/1') );
			}
		// does file exist in database?
		$reopen_header = $header_model->where('BMD_file_name', $session->BMD_file)->findAll();		
		// were any found?
		if ( ! $reopen_header )
			{
				$session->set('message_2', 'The BMD file name you entered does not exist in the database.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('header/reopen_BMD_step1/1') );
			}	
		// is it open?
		if ( $reopen_header[0]['BMD_header_status'] == '0' )
			{
				$session->set('message_2', 'The BMD file name, '.$session->BMD_file.', is already open. Select it from the list below.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/1') );
			}
			
		// all ok, so reopen the header
		$data =	[
							'BMD_header_status' => '0',
							'BMD_end_date' => '',
							'BMD_submit_date' => '',
							'BMD_submit_status' => '',
							'BMD_submit_message' => '',
							'BMD_last_action' => 'Reopen transcription',
						];
		$header_model->update($reopen_header[0]['BMD_header_index'], $data);		
			
		// return to create BMD
		$session->set('message_2', 'Scan '.$session->BMD_file.' was reopened. You can select it in the list below.');
		$session->set('message_class_2', 'alert alert-info');
		return redirect()->to( base_url('transcribe/transcribe_step1/1') );
	}
	
}
