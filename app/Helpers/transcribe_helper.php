<?php namespace App\Controllers;
use App\Models\Detail_Data_Model;
	
	function comment_update()
	{
		// initialise
		$session = session();
		$detail_data_model = new Detail_Data_Model();
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
				$session->set('comment_error', 'error');
			}
		// comment span
		if ( ! is_numeric($session->comment_span) )
			{
				$session->set('message_2', 'Number of lines must be a number.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('comment_error', 'error');
			}
		if ( $session->comment_span <= 0 )
			{
				$session->set('message_2', 'Number of lines must be greater than 0');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('comment_error', 'error');
			}
		// update record
		if ( $session->comment_error == '' )
			{
				// need to load builder class 
				$db = \Config\Database::connect();
				$builder_detail_data = $db->table('detail_data');
				// update bmd header
				$builder_detail_data->where('BMD_line_sequence', $session->line_sequence);
				$builder_detail_data->where('BMD_header_index', $session->transcribe_header[0]['BMD_header_index']);
				$builder_detail_data->set('BMD_comment_type', $session->comment_type);
				$builder_detail_data->set('BMD_comment_span', $session->comment_span);
				$builder_detail_data->set('BMD_comment_text', $session->comment_text);
				$builder_detail_data->update();
				// redirect
				$session->set('message_2', 'Comment added/changed for line => '.$session->line_sequence);
				$session->set('message_class_2', 'alert alert-success');
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
		// redirect
		$session->set('message_2', 'Comments have been removed from line sequence => '.$BMD_line_sequence);
		$session->set('message_class_2', 'alert alert-success');				
	}
	
	function comment_select($line_index)
	{
		// initialse
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		// if no error get the data, otherwise just show error
		if ( $session->comment_error == '' )
			{
				// get the line and load fields
				$session->set('line_edit_data', $detail_data_model->where('BMD_index', $line_index)->find());
				// load session fields
				$session->set('line_index', $session->line_edit_data[0]['BMD_index']);
				$session->set('line_sequence', $session->line_edit_data[0]['BMD_line_sequence']);
				$session->set('comment_type', $session->line_edit_data[0]['BMD_comment_type']);
				$session->set('comment_span',$session->line_edit_data[0]['BMD_comment_span']);
				$session->set('comment_text', $session->line_edit_data[0]['BMD_comment_text']);
				// set line_edit flag
				$session->set('line_edit_flag', 1);
				// set message
				$session->set('message_2', 'You requested to add/edit comments for line sequence => '.$session->line_edit_data[0]['BMD_line_sequence'].' <= Comments will be anchored to this line sequence');
				$session->set('message_class_2', 'alert alert-warning');
			}
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
