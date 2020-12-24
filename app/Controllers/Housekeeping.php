<?php namespace App\Controllers;

use App\Models\Districts_Model;
use App\Models\Parameter_Model;
use App\Models\Volumes_Model;
use App\Models\Volume_Ranges_Model;
use App\Models\Firstname_Model;
use App\Models\Surname_Model;

class Housekeeping extends BaseController
{
	function __construct() 
	{
        helper('common');
        helper('backup');
    }
	
	public function index($start_message)
	{
		// intialise
		$session = session();
		
		switch ($start_message) 
			{
				case 0:
					// message defaults
					$session->set('message_1', 'Choose the Housekeeping action you wish to perform.' );
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Choose Housekeeping action you want to perform.' );
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
			}
		
		// show views
		echo view('templates/header');
		echo view('linBMD2/housekeeping_menu');
		echo view('templates/footer');
	}
	
	public function districts_staleness()
	{
		// initialise
		$session = session();
		$districts_model = new Districts_Model();
		$parameter_model = new Parameter_Model();
		// get districts file from FreeBMD
		$curl_url = "https://www.freebmd.org.uk/addons/winbmd/Districts.txt";
		$fp = fopen(getcwd()."/Districts.latest", "wb");
		$ch = curl_init($curl_url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FILE, $fp); 
		curl_setopt($ch, CURLOPT_HEADER, 0);  
		if ( curl_exec($ch) === false )
			{
				// problem so send error message
				$session->set('message_2', 'A technical problem occurred. Send an email to '.$session->linbmd2_email.' describing what you were doing when the error occurred => Housekeeping::districts_refresh, around line 42 => '.$curl_url.' => '.curl_error($ch));
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('housekeeping/index/2') );
			}
		curl_close($ch);		
		fclose($fp);
		// get file size
		$districts_latest_filesize = filesize(getcwd()."/Districts.latest");
		// test file size against last updated file size
		$parameter = $parameter_model->where('Parameter_key', 'sizeoflastrefresheddistrictfile')->findAll();
		$districts_last_filesize = $parameter[0]['Parameter_value'];
		// test staleness
		if ( $districts_latest_filesize == $districts_last_filesize )
			{
				$session->set('message_2', 'Your local Districts database is up-to-date. No need to refresh Districts!');
				$session->set('message_class_2', 'alert alert-success');
				return redirect()->to( base_url('housekeeping/index/2') );
			}
		else
			{
				$session->set('message_2', 'Your local Districts database is stale. You should refresh districts.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('housekeeping/index/2') );
			}
	}
	
	public function districts_refresh()
	{
		// initialise
		$session = session();
		$districts_model = new Districts_Model();
		$parameter_model = new Parameter_Model();
		$volumes_model = new Volumes_Model();
		$volume_ranges_model = new Volume_Ranges_Model();
		// backup database
		database_backup();
		// get the disricts file from FreeBMD
		$curl_url = "https://www.freebmd.org.uk/addons/winbmd/Districts.txt";
		$fp = fopen(getcwd()."/Districts.latest", "wb");
		$ch = curl_init($curl_url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FILE, $fp); 
		curl_setopt($ch, CURLOPT_HEADER, 0);  
		if ( curl_exec($ch) === false )
			{
				// problem so send error message
				$session->set('message_2', 'A technical problem occurred. Send an email to '.$session->linbmd2_email.' describing what you were doing when the error occurred => Housekeeping::districts_refresh, around line 42 => '.$curl_url.' => '.curl_error($ch));
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('housekeeping/index/2') );
			}
		curl_close($ch);		
		fclose($fp);
		// read district and update districts master and create volumes
		// open file
		$fp = fopen(getcwd()."/Districts.latest", "r");
		if ( ! $fp )
			{
				$session->set('message_2', 'The latest districts file could not be opened. Cannot refresh districts. Send email to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('housekeeping/index/2') );
			}
		// read file and split to array
		$insert_count = 0;
		while (($line = fgetcsv($fp, 100, "|")) !== FALSE)
			{
				// does the district exist in districts table?
				$district = $districts_model->where('District_name', $line[0])->find();
				if ( ! $district )
					{
						$insert_count = $insert_count + 1;
						$district = strtoupper($line[0]);
						// if not found insert it
						$data =	[
											'District_name' => $district,
										];
						$district_index = $districts_model->insert($data);
					}
				else
					{
						$district_index = $district[0]['district_index'];
					}
				// pad quarters with zeros, this is required for the transcription programs
				$line[2] = str_pad($line[2], 2, "0", STR_PAD_LEFT);
				$line[4] = str_pad($line[4], 2, "0", STR_PAD_LEFT);
				// get ranges
				$ranges = $volume_ranges_model->findAll();
				
				// Add volume records
				// line array definitions
				// line[0] = district name
				// line[1] = start year
				// line[2] = start quarter
				// line[3] = last year
				// line[4] = last quarter
				// line[5] = volume code if year/quarter in range 183701:185104
				// line[6] = volume code if year/quarter in range 185201:194504
				// line[7] = volume code if year/quarter in range 194601:196504
				// line[8] = volume code if year/quarter in range 196601:197304
				// line[9] = volume code if year/quarter in range 197401:99999
				// starting with the start year/quarter from line[], loop through all year/quarter, picking up the appropriate volume from range
				// insert record, district, year, quarter. 
				// initialise depending on whether there is a volume for the range
				// now read through the line ranges to see if there is a volume in that range
				// is there a volume in this range?
						
				$id = 5;
				while ( $id <= 9 )
					{
						if ( $line[$id] != 0 )
							{
								// set start of range
								if ( $line[1].$line[2] >= $ranges[$id-5]['BMD_range_from'] )
									{
										$range_start = $line[1].$line[2];
									}
								else
									{
										$range_start = $ranges[$id-5]['BMD_range_from'];
									}
								// set end of range
								if ( $line[3].$line[4] <= $ranges[$id-5]['BMD_range_to'] )
										{
											$range_end = $line[3].$line[4];
										}
									else
										{
											$range_end = $ranges[$id-5]['BMD_range_to'];
										}
								// does this range already exist
								$volume = $volumes_model	->where('district_index', $district_index)
																				->where('volume_from', $range_start)
																				->where('volume_to', $range_end)
																				->where('volume', $line[$id])
																				->find();
								// insert record if not found
								if ( ! $volume )
								{
									$data =	[
														'district_index' => $district_index,
														'volume_from' => $range_start,
														'volume_to' => $range_end,
														'volume' => $line[$id],
													];
									$volumes_model->insert($data);
								}
							}
						// increment ID
						$id = $id + 1;
					}			
			}
		// all records read so close input file	
		fclose($fp);
		// update last file size
		$data =	[
							'Parameter_value' => filesize(getcwd()."/Districts.latest")
						];
		$parameter_model->update('sizeoflastrefresheddistrictfile', $data);
		// set return message		
		$session->set('message_2', 'Districts database has been refreshed. '.$insert_count.' records added');
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('housekeeping/index/2') );
	}
	
	public function firstnames()
	{
		// initialise
		$session = session();
		$firstname_model = new Firstname_Model();
		// get firstnames
		$session->set('names', $firstname_model->select('Firstname AS name')
																			->select('Firstname_popularity AS popularity')
																			->orderby('popularity', 'DESC')
																			->findAll());
		// show views
		$session->set('message_1', 'First names listed in descending order by popularity');
		$session->set('message_class_1', 'alert alert-primary');
		echo view('templates/header');
		echo view('linBMD2/show_names');
		echo view('templates/footer');
	}
	
	public function surnames()
	{
		// initialise
		$session = session();
		$surname_model = new Surname_Model();
		// get surnames
		$session->set('names', $surname_model->select('Surname AS name')
																			->select('Surname_popularity AS popularity')
																			->orderby('popularity', 'DESC')
																			->findAll());
		// show views
		$session->set('message_1', 'Family names listed in descending order by popularity');
		$session->set('message_class_1', 'alert alert-primary');
		echo view('templates/header');
		echo view('linBMD2/show_names');
		echo view('templates/footer');
	}
	
	public function database_backup()
	{
		// initialise
		$session = session();
		// do the backup
		database_backup();
		
		$session->set('message_2', 'The linBMD2 database has been backed up to '.getcwd()."/Backups/linBMD2.sql");
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('housekeeping/index/2') );
	}
	
	public function phpinfo()
	{
		// initialise
		$session = session();
		
		phpinfo();
	}
	
}		
		
		
