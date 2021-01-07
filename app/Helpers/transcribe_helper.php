<?php namespace App\Controllers;
use App\Models\Header_Model;
use App\Models\Detail_Data_Model;
use App\Models\Districts_Model;
use App\Models\Volumes_Model;
	
	function comment_update()
	{
		// initialise
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		// get inputs
		$session->set('comment_type', $_POST['comment_type']);
		$session->set('comment_span', $_POST['comment_span']);
		$session->set('comment_text', $_POST['comment_text']);
		// do tests
		// comment text
		if ( $session->comment_text == '' )
			{
				$session->set('message_2', 'Please enter some text in order to create the comment.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// comment span
		if ( ! is_numeric($session->comment_span) )
			{
				$session->set('message_2', 'Number of lines must be a number.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		if ( $session->comment_span <= 0 )
			{
				$session->set('message_2', 'Number of lines must be greater than 0');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// update record
		if ( $session->comment_error == '' )
			{
				// need to load builder class 
				$db = \Config\Database::connect();
				$builder_detail_data = $db->table('detail_data');
				// update bmd detail
				$builder_detail_data->where('BMD_index', $session->line_index);
				$builder_detail_data->set('BMD_comment_type', $session->comment_type);
				$builder_detail_data->set('BMD_comment_span', $session->comment_span);
				$builder_detail_data->set('BMD_comment_text', $session->comment_text);
				$builder_detail_data->update();
				// return
				return;
			}
	}
	
	function comment_remove($BMD_index, $BMD_line_sequence)
	{
		// initialse
		$session = session();
		// update record
		// need to load builder class 
		$db = \Config\Database::connect();
		$builder_detail_data = $db->table('detail_data');
		// update bmd header
		$builder_detail_data->where('BMD_index', $BMD_index);
		$builder_detail_data->set('BMD_comment_type', '');
		$builder_detail_data->set('BMD_comment_span', '');
		$builder_detail_data->set('BMD_comment_text', '');
		$builder_detail_data->update();
		// return
		return;				
	}
	
	function comment_select($line_index)
	{
		// initialse
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		// if no error get the data, otherwise just show error
		if ( $session->message_2 == '' )
			{
				// get the line and load fields
				$session->set('line_edit_data', $detail_data_model->where('BMD_index', $line_index)->find());
				// load session fields
				$session->set('line_index', $line_index);
				$session->set('line_sequence', $session->line_edit_data[0]['BMD_line_sequence']);
				$session->set('comment_type', $session->line_edit_data[0]['BMD_comment_type']);
				$session->set('comment_span',$session->line_edit_data[0]['BMD_comment_span']);
				$session->set('comment_text', $session->line_edit_data[0]['BMD_comment_text']);
				// set message
				$session->set('message_2', 'You requested to add/edit comments for line sequence => '.$session->line_edit_data[0]['BMD_line_sequence'].' <= Comments will be anchored to this line sequence');
				$session->set('message_class_2', 'alert alert-warning');
			}
		return;
	}
	
	function delete_line_confirm($line_index)
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
	
	function delete_line_delete()
	{
		// initialse
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		$header_model = new Header_Model();
		// get input
		$session->set('delete_ok', $_POST['confirm']);
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
	}
	
	function select_trans_line($line_index)
	{
		// initialse
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		// get the line and load fields
		$session->set('line_edit_data', $detail_data_model->where('BMD_index', $line_index)->find());
		// load session fields
		$session->set('familyname', $session->line_edit_data[0]['BMD_surname']);
		$session->set('line', $session->line_edit_data[0]['BMD_line_sequence']);
		$session->set('firstname', $session->line_edit_data[0]['BMD_firstname']);
		$session->set('secondname', $session->line_edit_data[0]['BMD_secondname']);
		$session->set('thirdname', $session->line_edit_data[0]['BMD_thirdname']);
		$session->set('partnername', $session->line_edit_data[0]['BMD_partnername']);
		$session->set('district', $session->line_edit_data[0]['BMD_district']);
		$session->set('registration', $session->line_edit_data[0]['BMD_registration']);
		$session->set('page', $session->line_edit_data[0]['BMD_page']);
		$session->set('age', $session->line_edit_data[0]['BMD_age']);
		// set line_edit flag
		$session->set('line_edit_flag', 1);
		$session->set('show_view_type', 'transcribe');
		// set message
		$session->set('message_2', 'You requested to edit line number => '.$session->line_edit_data[0]['BMD_line_sequence']);
		$session->set('message_class_2', 'alert alert-warning');
	}
	
	function transcribe_initialise_step1($start_message, $controller, $controller_title)
	{
		$session = session();
		$detail_data_model = new Detail_Data_Model();

		// get all existing details for this header
		$session->transcribe_detail_data = $detail_data_model	->where('BMD_header_index',  $session->transcribe_header[0]['BMD_header_index'])
																									->orderby('BMD_line_sequence', 'DESC')
																									->findAll();
		
		// set defaults																									
		switch ($start_message) 
			{
				case 0:
					// message defaults
					$session->set('message_1', $session->transcribe_header[0]['BMD_scan_name'].' => '.$session->transcribe_header[0]['BMD_records'].' records transcribed from this scan. Enter your transcription data from scan image. *=required field **=contextual help available');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					$session->set('element', $session->transcribe_header[0]['BMD_scan_name']);
					// flow control
					$session->set('show_view_type', 'transcribe');
					$session->set('confirm', 'N');
					$session->set('district_ok', 'N');
					$session->set('page_ok', 'N');
					$session->set('volume_ok', 'N');
					$session->set('line_edit_flag', 0);
					// return routes depend on calling controller
					$session->set('return_route', $controller.'/transcribe_'.$controller.'_step2');
					$session->set('return_route_step1', $controller.'/transcribe_'.$controller.'_step1/0');
					// table title
					$session->set('table_title', $controller_title);
					if ( $session->database_backup_performed == 1 )
						{
							$session->set('table_title', $controller_title.' - database backup performed');
						}
					// set dup fields
					$session->set('dup_firstname', '');
					$session->set('dup_secondname', '');
					$session->set('dup_thirdname', '');
					$session->set('dup_partnername', '');
					$session->set('dup_age', '');
					$session->set('dup_district', '');
					$session->set('dup_registration', '');
					$session->set('dup_page', '');
					// set view defaults
					if ( $session->transcribe_detail_data )
						{
							$session->set('line', $session->transcribe_detail_data[0]['BMD_line_sequence'] + 10);
							$session->set('familyname', $session->transcribe_detail_data[0]['BMD_surname']);
							$session->set('dup_familyname', $session->transcribe_detail_data[0]['BMD_surname']);
							$session->set('dup_firstname', $session->transcribe_detail_data[0]['BMD_firstname']);
							$session->set('dup_secondname', $session->transcribe_detail_data[0]['BMD_secondname']);
							$session->set('dup_thirdname', $session->transcribe_detail_data[0]['BMD_thirdname']);
							$session->set('dup_partnername', $session->transcribe_detail_data[0]['BMD_partnername']);
							$session->set('dup_age', $session->transcribe_detail_data[0]['BMD_age']);
							$session->set('dup_district', $session->transcribe_detail_data[0]['BMD_district']);
							$session->set('dup_registration', $session->transcribe_detail_data[0]['BMD_registration']);
							$session->set('dup_page', $session->transcribe_detail_data[0]['BMD_page']);
						}
					else
						{
							$session->set('line', 10);
							$session->set('familyname', '');
						}
					$session->set('firstname', '');
					$session->set('secondname', '');
					$session->set('thirdname', '');
					$session->set('partnername', '');
					$session->set('age', '');
					$session->set('district', '');
					$session->set('reverselookup', '');
					$session->set('registration', '');
					$session->set('page', '');
					$session->set('synonym', '');
					// set parameters for feh image viewer
					$session->set('feh_command', 'feh --zoom '.$session->transcribe_header[0]['BMD_image_zoom'].' --geometry '.$session->transcribe_header[0]['BMD_image_x'].'x'.$session->transcribe_header[0]['BMD_image_y'].'+400+370 --scroll-step 10 '.getcwd()."/Scans/".$session->transcribe_header[0]['BMD_scan_name'].' > /dev/null &');
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', $session->transcribe_header[0]['BMD_scan_name'].' => Approximately '.$session->transcribe_header[0]['BMD_records'].' records transcribed from this scan. Enter your transcription data from scan image. *=required field **=contextual help available');
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
					break;
			}
	}
	
	function transcribe_show_step1($controller)
	{	
		// initialise
		$session = session();
		// show header																
		echo view('templates/header');
		// show views depending on view type
		switch ($session->show_view_type) 
			{
				// normal transcription
				case 'transcribe':
					echo view('linBMD2/transcribe_image');
					echo view('linBMD2/transcribe_'.$controller);
					echo view('linBMD2/transcribe_buttons');
					echo view('linBMD2/transcribe_script');
					echo view('linBMD2/transcribe_'.$controller.'_show_details');
					break;
				// confirm page if not standard
				case 'confirm_page':
					echo view('linBMD2/transcribe_page_confirmation');
					echo view('linBMD2/transcribe_'.$controller.'_show_details');
					break;
				// confirm district if not standard
				case 'confirm_district':
					echo view('linBMD2/transcribe_district_confirmation');
					echo view('linBMD2/transcribe_'.$controller.'_show_details');
					break;
				// confirm volumeif not standard
				case 'confirm_volume':
					echo view('linBMD2/transcribe_volume_confirmation');
					echo view('linBMD2/transcribe_'.$controller.'_show_details');
					break;
			}
		// show footer
		echo view('templates/footer');
	}
	
	function transcribe_get_transcribe_inputs($controller)
	{
		// initialise method
		$session = session();
		
		// get common entries for all types
		$session->set('familyname', $_POST['familyname']);
		$session->set('line', $_POST['line']);
		$session->set('firstname', $_POST['firstname']);
		$session->set('secondname', $_POST['secondname']);
		$session->set('thirdname', $_POST['thirdname']);
		$session->set('district', $_POST['district']);
		$session->set('reverselookup', $_POST['reverselookup']);
		$session->set('page', $_POST['page']);
		// get per type entries
		switch ($controller)
			{
				case 'births':
					$session->set('partnername', $_POST['partnername']);
					$session->set('registration', $_POST['registration']);
					break;
				case 'marriages':
					$session->set('partnername', $_POST['partnername']);
					break;
				case 'deaths':
					$session->set('age', $_POST['age']);
					break;
			}
	}
				
	function transcribe_get_confirm_district_inputs($controller)
	{
		// initialise method
		$session = session();	
		// get inputs
		$session->set('synonym_ok', $_POST['confirm_synonym']);
		$session->set('synonym', $_POST['synonym']);
		$session->set('district_ok', $_POST['confirm']);
	}
	
	function transcribe_get_confirm_page_inputs($controller)
	{			
		// initialise method
		$session = session();	
		// get inputs
		$session->set('page_ok', $_POST['confirm']);
	}
	
	function transcribe_get_confirm_volume_inputs($controller)
	{
		// initialise method
		$session = session();	
		// get inputs
		$session->set('volume', $_POST['volume']);
		$session->set('volume_ok', $_POST['confirm']);
	}
	
	function transcribe_validate_transcribe_inputs($controller)
	{
		// initialise method
		$session = session();	
		$detail_data_model = new Detail_Data_Model();
		$districts_model = new Districts_Model();
		$volumes_model = new Volumes_Model();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		// do tests
		// standard tests for all types
		// familyname blank?
		if ( $session->familyname == '' )
			{
				$session->set('message_2', 'Family name cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// line number blank?
		if ( $session->line == '' )
			{
				$session->set('message_2', 'Line number cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// exists?
		$line_detail = $detail_data_model	->where('BMD_line_sequence',  $session->line)
																					->where('BMD_header_index',  $session->transcribe_header[0]['BMD_header_index'])
																					->findAll();
		if ( $line_detail AND $session->line_edit_flag == 0 )
		{
			$session->set('message_2', 'Line number '.$session->line.' is already transcribed for this scan. If you want to change this line, select it in the table below. If you are adding a line, enter a line number that does not already exist.');
			$session->set('message_class_2', 'alert alert-danger');
			$session->set('message_error', 'error');
			return;
		}
		 // firstname blank?
		if ( $session->firstname == '' )
			{
				$session->set('message_2', 'First name cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// per type tests - must be done here, ie before district test
		switch ($controller)
			{
				case 'births':
					// partnername blank?
					if ( $session->partnername == '' )
						{
							$session->set('message_2', 'Partner name cannot be blank.');
							$session->set('message_class_2', 'alert alert-danger');
							$session->set('message_error', 'error');
							return;
						}
					// registration blank, month valid, year valid?
					if ( $session->registration == '' )
						{
							$session->set('message_2', 'Registration cannot be blank.');
							$session->set('message_class_2', 'alert alert-danger');
							$session->set('message_error', 'error');
							return;
						}
					if ( strlen($session->registration) == 2 )
						{
							$session->registration = $session->registration.'.'.substr($session->transcribe_allocation[0]['BMD_year'], 2);
						}
					$registration = explode('.', $session->registration);
					if ( count($registration) != 2 )
						{
							$session->set('message_2', 'Registration format not valid. Is the month.year separator valid? Must be .');
							$session->set('message_class_2', 'alert alert-danger');
							$session->set('message_error', 'error');
							return;
						}
					if ( is_numeric($registration[0]) === false )
						{
							$session->set('message_2', 'Registration month number must be numeric.');
							$session->set('message_class_2', 'alert alert-danger');
							$session->set('message_error', 'error');
							return;
						}
					if ( $registration[0] < '01' OR $registration[0] > '12' )
						{
							$session->set('message_2', 'Registration month number must be in range 01:12.');
							$session->set('message_class_2', 'alert alert-danger');
							$session->set('message_error', 'error');
							return;
						}
					if ( $registration[1] != substr($session->transcribe_allocation[0]['BMD_year'], 2) )
						{
							$session->set('message_2', 'Registration year must be equal to scan year.');
							$session->set('message_class_2', 'alert alert-danger');
							$session->set('message_error', 'error');
							return;
						}
					break;
				case 'marriages':
					// partnername blank?
					if ( $session->partnername == '' )
						{
							$session->set('message_2', 'Partner name cannot be blank.');
							$session->set('message_class_2', 'alert alert-danger');
							$session->set('message_error', 'error');
							return;
						}
					// set registration for volume check
					$registration = $session->transcribe_allocation[0]['BMD_year'].'.'.str_pad($session->transcribe_allocation[0]['BMD_quarter'], 2, '0', STR_PAD_LEFT);
					break;
				case 'deaths':
					// age blank?
					if ( $session->age != '' )
						{
							if ( is_numeric($session->age) === false )
								{
									$session->set('message_2', 'If entered, age must be numeric.');
									$session->set('message_class_2', 'alert alert-danger');
									$session->set('message_error', 'error');
									return;
								}
						}
					// set registration for volume check
					$registration = $session->transcribe_allocation[0]['BMD_year'].'.'.str_pad($session->transcribe_allocation[0]['BMD_quarter'], 2, '0', STR_PAD_LEFT);
					break;
			}
		// district blank and valid?
		if ( $session->district != '' AND $session->reverselookup != '' )
			{
				$session->set('message_2', 'You cannot enter both District name and District lookup by volume. ');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		if ( $session->district == '' AND $session->reverselookup != '' )
			{
				$session->set('district', $session->reverselookup);
			}
		if ( $session->district == '' )
			{
				$session->set('message_2', 'District cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		$session->set('transcribe_district', $districts_model->where('District_name', $session->district)->findAll());
		if ( ! $session->transcribe_district )
			{
				$session->synonym = '';
				$session->set('show_view_type', 'confirm_district');
				$session->set('message_2', 'This district is unknown => '.$session->district.'. Please confirm your entry or correct it by selecting No.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// get volume info
		$session->set('transcribe_volumes', $volumes_model	->where('district_index', $session->transcribe_district[0]['district_index'])->findAll());
		if ( ! $session->transcribe_volumes )
			{
				$session->set('show_view_type', 'confirm_volume');
				$session->set('volume', '');
				$session->set('message_2', 'No volume data found for this district => '.$session->district.', '.$session->transcribe_allocation[0]['BMD_year'].', '.$registration[0].'. Please enter volume from scan and confirm.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// set volume found flag 
		$volume_found = 0;
		// set values in order to find this registration in range
		$year = $session->transcribe_allocation[0]['BMD_year'];
		// per type quarter
		switch ($controller)
			{
				case 'births':
					$quarter = $session->month_to_quarter[$registration[0]];
					break;
				case 'marriages':
					$quarter = str_pad($session->transcribe_allocation[0]['BMD_quarter'], 2, '0', STR_PAD_LEFT);
					break;
				case 'deaths':
					$quarter = str_pad($session->transcribe_allocation[0]['BMD_quarter'], 2, '0', STR_PAD_LEFT);
					break;
			}
		// find range
		foreach ( $session->transcribe_volumes as $volume_range )
			{
				if ( $year.$quarter >= $volume_range['volume_from'] AND $year.$quarter <= $volume_range['volume_to'])
					{
						$session->set('volume', $volume_range['volume']);
						$volume_found = 1;
						break;
					}	
			}
		// was a volume found?
		if ( $volume_found == 0 OR $session->volume == '' )
			{
				$session->set('show_view_type', 'confirm_volume');
				$session->set('volume', '');
				$session->set('message_2', 'No volume data found for this district => '.$session->district.', '.$session->transcribe_allocation[0]['BMD_year'].', '.$registration[0].'. Please enter volume from scan and confirm.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// page blank, valid?
		if ( $session->page == '' )
			{
				$session->set('message_2', 'Page cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		if ( $session->page_ok == 'N' )
			{
				if ( strlen($session->page) != 4  OR is_numeric($session->page) === false )
					{
						$session->set('show_view_type', 'confirm_page');
						$session->set('message_2', 'Page number is usually 4 digits long. You entered => '.$session->page.'. Please confirm your entry or correct it by selecting No.');
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('message_error', 'error');
						return;
					}
			}
	}
	
	function transcribe_validate_confirm_district_inputs($controller)
	{
		// initialise method
		$session = session();	
		$session->set('message_error', '');
		$districts_model = new Districts_Model();
		$volumes_model = new Volumes_Model();
		// has user confirmed both synonym and district?
		if ( $session->synonym_ok == 'Y' AND $session->district_ok == 'Y' )
		{
			$session->set('show_view_type', 'confirm_district');
			$session->set('message_2', 'You cannot confirm both synonym and district.');
			$session->set('message_class_2', 'alert alert-danger');
			$session->set('message_error', 'error');
			return;
		}
		// did user confirm synonym
		if ( $session->synonym_ok == 'Y' )
			{
				// is synonym a valid district?
				$session->set('transcribe_synonym', $districts_model->where('District_name', $session->synonym)->findAll());
				$synonym_volumes = $volumes_model->where('district_index', $session->transcribe_synonym[0]['district_index'])->findAll();
				if ( ! $session->transcribe_synonym OR ! $synonym_volumes )
					{
						$session->set('show_view_type', 'confirm_district');
						$session->set('message_2', 'You must enter a valid district for the synonym OR no volume data was found for the synonym.');
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('message_error', 'error');
						return;
					}
				// a valid synonym was confirmed by user
				// add district to table
				$data =	[
									'District_name' => strtoupper($session->district),
								];
				$id = $districts_model->insert($data);
				// now read all volume info for synonym and create volume records for the new district
				foreach ( $synonym_volumes as $synonym )
					{
						$data =	[
											'district_index' => $id,
											'volume_from' => $synonym['volume_from'],
											'volume_to' => $synonym['volume_to'],
											'volume' => $synonym['volume']
										];
						$volumes_model->insert($data);
					}
			}
		else
			{
				// if synonym not confirmed, did user confirm district?							
				if ( $session->district_ok == 'N' )
					{
						$session->set('show_view_type', 'transcribe');
						$session->set('message_2', 'You did not confirm this district => '.$session->district.'. Please correct it.');
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('message_error', 'error');
						return;
					}
				else
					{
						// user confirmed district so add it to districts file
						$data =	[
											'District_name' => strtoupper($session->district),
										];
						$districts_model->insert($data);
					}
			}
	}
				
	function transcribe_validate_confirm_page_inputs($controller)
	{			
		// initialise method
		$session = session();
		$session->set('message_error', '');	
		// test confirm
		if ( $session->page_ok == 'N' )
			{
				$session->set('show_view_type', 'transcribe');
				$session->set('message_2', 'You did not confirm this page number => '.$session->page.'. Please correct it.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
	}
	
	function transcribe_validate_confirm_volume_inputs($controller)
	{
		// initialise method
		$session = session();
		$session->set('message_error', '');
		$volumes_model = new Volumes_Model();
		// did user confirm?
		if ( $session->volume_ok == 'N' )
			{
				$session->set('show_view_type', 'transcribe');
				$session->set('message_2', 'You did not confirm this the volume => '.$session->volume.'. Please correct it or confirm the district.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		else
			{
				// user confirmed volume so add it
				$registration = explode('.', $session->registration);
				$data =	[
									'district_index' => $session->transcribe_district[0]['district_index'],
									'volume_from' => $session->transcribe_allocation[0]['BMD_year'].$session->month_to_quarter[$registration[0]],
									'volume_to' => $session->transcribe_allocation[0]['BMD_year'].$session->month_to_quarter[$registration[0]],
									'volume' => $session->volume,
								];
				$volumes_model->insert($data);
			}			
	}
	
	function transcribe_update($controller)
	{
		// initialise method
		$session = session();	
		$header_model = new Header_Model();
		$detail_data_model = new Detail_Data_Model();
		// convert to capitals - standard fields
		$session->set('familyname', strtoupper($session->familyname));
		$session->set('firstname', strtoupper($session->firstname));
		$session->set('secondname', strtoupper($session->secondname));
		$session->set('thirdname', strtoupper($session->thirdname));
		$session->set('district', strtoupper($session->district));
		$session->set('page', strtoupper($session->page));
		// per type fields
		switch ($controller)
			{
				case 'births':
					$session->set('partnername', strtoupper($session->partnername));
					break;
				case 'marriages':
					$session->set('partnername', strtoupper($session->partnername));
					break;
				case 'deaths':
					break;
			}
		// set standard fields for update
		$data =	[
								'BMD_header_index' => $session->transcribe_header[0]['BMD_header_index'],
								'BMD_line_sequence' => $session->line,
								'BMD_surname' => $session->familyname,
								'BMD_firstname' => $session->firstname,
								'BMD_secondname' => $session->secondname,
								'BMD_thirdname' => $session->thirdname,
								'BMD_district' => $session->district,
								'BMD_volume' => $session->volume,
								'BMD_page' => $session->page,
								'BMD_status' => '0',
							];
		// set per type fields for update
		switch ($controller)
			{
				case 'births':
					$data['BMD_partnername'] =	$session->partnername;
					$data['BMD_registration'] =	$session->registration;
					break;
				case 'marriages':
					$data['BMD_partnername'] =	$session->partnername;
					break;
				case 'deaths':
					$data['BMD_age'] =	$session->age;
					break;
			}
		// add if line edit = 0 / update if line edit = 1
		if ( $session->line_edit_flag == 0 )
			{
				// insert record
				$detail_data_model->insert($data);
				// update record count on header
				$data =	[
									'BMD_records' => $session->transcribe_header[0]['BMD_records'] + 1,
								];
				$header_model->update($session->transcribe_header[0]['BMD_header_index'], $data);
			}
		else
			{
				$detail_data_model->update($session->line_edit_data[0]['BMD_index'], $data);
			}
		// add names to tables; update_surnames and update_firstnames are function in the update_names_helper
		// familyname / partnername
		update_surnames($session->familyname);
		update_surnames($session->partnername);
		// first, second, third names
		update_firstnames($session->firstname);
		update_firstnames($session->secondname);
		update_firstnames($session->thirdname);
		// load the header again
		$session->transcribe_header = $header_model->where('BMD_header_index',  $session->transcribe_header[0]['BMD_header_index'])->find();
		// do backup on a regular basis if no remainder when number of records divided by 5  is 0
		$session->set('database_backup_performed', 0);
		if ( $session->transcribe_header[0]['BMD_records'] % 5 == 0 )
			{
				database_backup();
			}
	}
