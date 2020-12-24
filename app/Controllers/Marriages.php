<?php namespace App\Controllers;

use App\Models\Header_Model;
use App\Models\Syndicate_Model;
use App\Models\Allocation_Model;
use App\Models\Detail_Data_Model;
use App\Models\Surname_Model;
use App\Models\Firstname_Model;
use App\Models\Districts_Model;
use App\Models\Volumes_Model;

class Marriages extends BaseController
{
	function __construct() 
	{
        helper('common');
        helper('update_names');
        helper('backup');
        helper('transcribe');
    }
	
	public function transcribe_marriages_step1($start_message)
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		$detail_data_model = new Detail_Data_Model();
		$surname_model = new Surname_Model();
		$firstname_model = new Firstname_Model();

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
					$session->set('return_route', 'marriages/transcribe_marriages_step2');
					$session->set('return_route_step1', 'marriages/transcribe_marriages_step1/0');
					if ( $session->database_backup_performed == 1 )
						{
							$session->set('table_title', 'Marriages - database backup performed');
						}
					else
						{
							$session->set('table_title', 'Marriages');
						}
					// set dup fields
					$session->set('dup_firstname', '');
					$session->set('dup_secondname', '');
					$session->set('dup_thirdname', '');
					$session->set('dup_partnername', '');
					$session->set('dup_district', '');
					$session->set('dup_registration', '');
					$session->set('dup_page', '');
					// set view defaults
					if ( $session->transcribe_detail_data )
						{
							$session->set('line', $session->transcribe_detail_data[0]['BMD_line_sequence'] + 10);
							$session->set('familyname', $session->transcribe_detail_data[0]['BMD_surname']);
							$session->set('dup_firstname', $session->transcribe_detail_data[0]['BMD_firstname']);
							$session->set('dup_secondname', $session->transcribe_detail_data[0]['BMD_secondname']);
							$session->set('dup_thirdname', $session->transcribe_detail_data[0]['BMD_thirdname']);
							$session->set('dup_partnername', $session->transcribe_detail_data[0]['BMD_partnername']);
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
					$session->set('district', '');
					$session->set('reverselookup', '');
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
			}
		
		// show transcription page																
		echo view('templates/header');
		switch ($session->show_view_type) 
			{
				case 'transcribe':
					echo view('linBMD2/transcribe_image');
					echo view('linBMD2/transcribe_marriages');
					echo view('linBMD2/transcribe_marriages_show_details');
					break;
				case 'confirm_page':
					echo view('linBMD2/transcribe_page_confirmation');
					echo view('linBMD2/transcribe_marriages_show_details');
					break;
				case 'confirm_district':
					echo view('linBMD2/transcribe_district_confirmation');
					echo view('linBMD2/transcribe_marriages_show_details');
					break;
				case 'confirm_volume':
					echo view('linBMD2/transcribe_volume_confirmation');
					echo view('linBMD2/transcribe_marriages_show_details');
					break;
			}
		echo view('templates/footer');
	}
	
	public function transcribe_marriages_step2()
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		$detail_data_model = new Detail_Data_Model();
		$districts_model = new Districts_Model();
		$volumes_model = new Volumes_Model();
		
		// get inputs
		switch ($session->show_view_type) 
			{
				case 'transcribe':
					$session->set('familyname', $this->request->getPost('familyname'));
					$session->set('line', $this->request->getPost('line'));
					$session->set('firstname', $this->request->getPost('firstname'));
					$session->set('secondname', $this->request->getPost('secondname'));
					$session->set('thirdname', $this->request->getPost('thirdname'));
					$session->set('partnername', $this->request->getPost('partnername'));
					$session->set('district', $this->request->getPost('district'));
					$session->set('reverselookup', $this->request->getPost('reverselookup'));
					$session->set('page', $this->request->getPost('page'));
					break;
				case 'confirm_district':
					$session->set('synonym_ok', $this->request->getPost('confirm_synonym'));
					$session->set('synonym', $this->request->getPost('synonym'));
					$session->set('district_ok', $this->request->getPost('confirm'));
					// are both yes?
					if ( $session->synonym_ok == 'Y' AND $session->district_ok == 'Y' )
						{
							$session->set('show_view_type', 'confirm_district');
							$session->set('message_2', 'You cannot confirm both synonym and district.');
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('marriages/transcribe_marriages_step1/1') );
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
									return redirect()->to( base_url('marriages/transcribe_marriages_step1/1') );
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
									return redirect()->to( base_url('marriages/transcribe_marriages_step1/1') );
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
					break;
				case 'confirm_page':
					$session->set('page_ok', $this->request->getPost('confirm'));
					if ( $session->page_ok == 'N' )
						{
							$session->set('show_view_type', 'transcribe');
							$session->set('message_2', 'You did not confirm this page number => '.$session->page.'. Please correct it.');
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('marriages/transcribe_marriages_step1/1') );
						}
					break;
				case 'confirm_volume':
					$session->set('volume', $this->request->getPost('volume'));
					$session->set('volume_ok', $this->request->getPost('confirm'));
					if ( $session->volume_ok == 'N' )
						{
							$session->set('show_view_type', 'transcribe');
							$session->set('message_2', 'You did not confirm this the volume => '.$session->volume.'. Please correct it or confirm the district.');
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('marriages/transcribe_marriages_step1/1') );
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
					break;
			}
			
		// do tests
		// familyname blank?
		if ( $session->familyname == '' )
			{
				$session->set('message_2', 'Family name cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('marriages/transcribe_marriages_step1/1') );
			}
		// line number blank?
		if ( $session->line == '' )
			{
				$session->set('message_2', 'Line number cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('marriages/transcribe_marriages_step1/1') );
			}
		// exists?
		$line_detail = $detail_data_model->where('BMD_line_sequence',  $session->line)
																->where('BMD_header_index',  $session->transcribe_header[0]['BMD_header_index'])
																->findAll();
		if ( $line_detail AND $session->line_edit_flag == 0 )
		{
			$session->set('message_2', 'Line number '.$session->line.' is already transcribed for this scan. If you want to change this line, select it in the table below. If you are adding a line, enter a line number that does not already exist.');
			$session->set('message_class_2', 'alert alert-danger');
			return redirect()->to( base_url('marriages/transcribe_marriages_step1/1') );
		}
		 // firstname blank?
		if ( $session->firstname == '' )
			{
				$session->set('message_2', 'First name cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('marriages/transcribe_marriages_step1/1') );
			}
		// partnername blank?
		if ( $session->partnername == '' )
			{
				$session->set('message_2', 'Partner name cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('marriages/transcribe_marriages_step1/1') );
			}
		// page blank, valid?
		if ( $session->page == '' )
			{
				$session->set('message_2', 'Page cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('marriages/transcribe_marriages_step1/1') );
			}
		if ( $session->page_ok == 'N' )
			{
				if ( strlen($session->page) != 4  OR is_numeric($session->page) === false )
					{
						$session->set('show_view_type', 'confirm_page');
						$session->set('message_2', 'Page number is usually 4 digits long. You entered => '.$session->page.'. Please confirm your entry or correct it by selecting No.');
						$session->set('message_class_2', 'alert alert-danger');
						return redirect()->to( base_url('marriages/transcribe_marriages_step1/1') );
					}
			}
		// district blank and valid?
		if ( $session->district != '' AND $session->reverselookup != '' )
			{
				$session->set('message_2', 'You cannot enter both District name and District lookup by volume. ');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('deaths/transcribe_deaths_step1/1') );
			}
		if ( $session->district == '' AND $session->reverselookup != '' )
			{
				$session->set('district', $session->reverselookup);
			}
		if ( $session->district == '' )
			{
				$session->set('message_2', 'District cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('marriages/transcribe_marriages_step1/1') );
			}
		$session->set('transcribe_district', $districts_model->where('District_name', $session->district)->findAll());
		if ( ! $session->transcribe_district )
			{
				$session->synonym = '';
				$session->set('show_view_type', 'confirm_district');
				$session->set('message_2', 'This district is unknown => '.$session->district.'. Please confirm your entry or correct it by selecting No.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('marriages/transcribe_marriages_step1/1') );
			}
		// get volume info
		$session->set('transcribe_volumes', $volumes_model	->where('district_index', $session->transcribe_district[0]['district_index'])->findAll());
		if ( ! $session->transcribe_volumes )
			{
				$session->set('show_view_type', 'confirm_volume');
				$session->set('volume', '');
				$session->set('message_2', 'No volume data found for this district => '.$session->district.', '.$session->transcribe_allocation[0]['BMD_year'].'. Please enter volume from scan and confirm.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('marriages/transcribe_marriages_step1/1') );
			}
		// set volume found flag 
		$volume_found = 0;
		// set values in order to find this registration in range
		$year = $session->transcribe_allocation[0]['BMD_year'];
		$quarter = str_pad($session->transcribe_allocation[0]['BMD_quarter'], 2, '0', STR_PAD_LEFT);
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
				$session->set('message_2', 'No volume data found for this district => '.$session->district.', '.$session->transcribe_allocation[0]['BMD_year'].', '.$quarter.'. Please enter volume from scan and confirm.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('marriages/transcribe_marriages_step1/1') );
			}
	
		// all good
		// convert to capitals
		$session->set('familyname', strtoupper($session->familyname));
		$session->set('firstname', strtoupper($session->firstname));
		$session->set('secondname', strtoupper($session->secondname));
		$session->set('thirdname', strtoupper($session->thirdname));
		$session->set('partnername', strtoupper($session->partnername));
		$session->set('district', strtoupper($session->district));
		$session->set('page', strtoupper($session->page));
		// set fields
		$data =	[
							'BMD_header_index' => $session->transcribe_header[0]['BMD_header_index'],
							'BMD_line_sequence' => $session->line,
							'BMD_surname' => $session->familyname,
							'BMD_firstname' => $session->firstname,
							'BMD_secondname' => $session->secondname,
							'BMD_thirdname' => $session->thirdname,
							'BMD_partnername' => $session->partnername,
							'BMD_district' => $session->district,
							'BMD_volume' => $session->volume,
							'BMD_registration' => '',
							'BMD_page' => $session->page,
							'BMD_status' => '0',
						];
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
		// load the header again
		$session->transcribe_header = $header_model->where('BMD_header_index',  $session->transcribe_header[0]['BMD_header_index'])->find();
		// add names to tables
		// familyname / partnername
		update_surnames($session->familyname);
		update_surnames($session->partnername);
		// first, second, third names
		update_firstnames($session->firstname);
		update_firstnames($session->secondname);
		update_firstnames($session->thirdname);
		// do backup on a regular basis if no remainder when number of records divided by 5  is 0
		$session->set('database_backup_performed', 0);
		if ( $session->transcribe_header[0]['BMD_records'] % 5 == 0 )
			{
				database_backup();
			}
		// go round again
		switch ($session->BMD_cycle_code) 
			{
				case 'VERIT': // verify transcription file
					return redirect()->to( base_url('transcribe/verify_BMD_trans_step1/'.$session->transcribe_header[0]['BMD_header_index']) );
					break;
				default:
					return redirect()->to( base_url('marriages/transcribe_marriages_step1/0') );
					break;
			}
	}
	
	public function select_line($line_index)
	{
		// select the line and load session fields
		select_trans_line($line_index);
		// go back to editor					
		return redirect()->to( base_url('marriages/transcribe_marriages_step1/1') );
	}	
	
	public function delete_line_step1($line_index)
	{
		delete_line_confirm($line_index);
	}
	
	public function delete_line_step2()
	{
		delete_line_delete();
		// return
		return redirect()->to( base_url('marriages/transcribe_marriages_step1/0') );
	}
	
	public function comment_step2()
	{
		// initialse
		$session = session();
		$session->set('comment_error', '');
		// add/edit comments
		comment_update();
		if ( $session->comment_error == 'error' )
			{
				return redirect()->to( base_url('marriages/select_comment/'.$session->line_index) );
			}
		else
			{
				return redirect()->to( base_url('marriages/transcribe_marriages_step1/2') );
			}
	}
	
	public function select_comment($line_index)
	{
		comment_select($line_index);
	}
	
	public function remove_comments($BMD_index, $BMD_line_sequence)
	{
		comment_remove($BMD_index, $BMD_line_sequence);
		return redirect()->to( base_url('marriages/transcribe_marriages_step1/2') );
	}
}
