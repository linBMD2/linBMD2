<?php namespace App\Controllers;

use App\Models\Header_Model;
use App\Models\Syndicate_Model;
use App\Models\Allocation_Model;
use App\Models\Detail_Data_Model;
use App\Models\Surname_Model;
use App\Models\Firstname_Model;
use App\Models\Districts_Model;
use App\Models\Volumes_Model;

class Deaths extends BaseController
{
	function __construct() 
	{
        helper('common');
        helper('update_names');
        helper('backup');
        helper('transcribe');
    }
	
	public function transcribe_deaths_step1($start_message)
	{
		// initialise step1 = start message, controller, controller title
		transcribe_initialise_step1($start_message, 'deaths', 'Deaths');
		// show views
		transcribe_show_step1('deaths');
	}
	
	public function transcribe_deaths_step2()
	{
		// initialise method
		$session = session();
		
		// what data am I getting and validating?
		switch ($session->show_view_type) 
			{
				// standard data entry
				case 'transcribe':
					transcribe_get_transcribe_inputs('deaths');
					transcribe_validate_transcribe_inputs('deaths');
					break;
				// confirm district
				case 'confirm_district':
					transcribe_get_confirm_district_inputs('deaths');
					transcribe_validate_confirm_district_inputs('deaths');
					break;
				// confirm page
				case 'confirm_page':
					transcribe_get_confirm_page_inputs('deaths');
					transcribe_validate_confirm_page_inputs('deaths');
					break;
				// confirm volume
				case 'confirm_volume':
					transcribe_get_confirm_volume_inputs('deaths');
					transcribe_validate_confirm_volume_inputs('deaths');
					break;
			}
			
		// is there an error?
		if ( $session->message_error == 'error' )
			{
				return redirect()->to( base_url('deaths/transcribe_deaths_step1/1') );
			}
			
		// all good - write / update data
		transcribe_update('deaths');
		
		// go round again
		switch ($session->BMD_cycle_code) 
			{
				case 'VERIT': // verify transcription file
					return redirect()->to( base_url('transcribe/verify_BMD_trans_step1/'.$session->transcribe_header[0]['BMD_header_index']) );
					break;
				default:
					return redirect()->to( base_url('deaths/transcribe_deaths_step1/0') );
					break;
			}
	}
		
	public function select_line($line_index)
	{
		// select the line and load session fields
		select_trans_line($line_index);
		// go back to editor					
		return redirect()->to( base_url('deaths/transcribe_deaths_step1/1') );
	}	
	
	public function comment_step2()
	{
		// initialse
		$session = session();
		// add/edit comments
		comment_update();
		if ( $session->message_2 != '' )
			{
				return redirect()->to( base_url('deaths/select_comment/'.$session->line_index) );
			}
		else
			{
				return redirect()->to( base_url('deaths/transcribe_deaths_step1/2') );
			}
	}
	
	public function select_comment($line_index)
	{
		comment_select($line_index);
		// show comment page															
		echo view('templates/header');
		echo view('linBMD2/transcribe_deaths_comments');
		echo view('linBMD2/transcribe_deaths_show_details');
		echo view('templates/footer');
	}
	
	public function remove_comments($BMD_index, $BMD_line_sequence)
	{
		comment_remove($BMD_index, $BMD_line_sequence);
		return redirect()->to( base_url('deaths/transcribe_deaths_step1/2') );
	}
	
	public function delete_line_step1($line_index)
	{
		delete_line_confirm($line_index);
	}
	
	public function delete_line_step2()
	{
		delete_line_delete();
		// return
		return redirect()->to( base_url('deaths/transcribe_deaths_step1/0') );
	}
}
