<?php namespace App\Controllers;

use App\Models\Syndicate_Model;

class Syndicate extends BaseController
{
	public function index()
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		$session->set('message', 'Please select the Current Action or the Next Action for the BMD file you wish to work with OR create a new one. Your current BMD file is highlighted.');
		$session->set('message_value', ' ');
		$session->set('message_class', 'alert alert-primary');
	}

	public function refresh_syndicates()
	{
		// initialise method
		$session = session();
		$syndicate_model = new Syndicate_Model();
		// need to load builder class since the primary key in syndicate is not auto increment
		$db = \Config\Database::connect();
		$builder = $db->table('syndicate');
		
		// scrape FeeBMD to get list of syndicates
		// set curl handle and results file handle
		$ch = curl_init("https://www.freebmd.org.uk/cgi/synd-info.pl");
		$fp = fopen(getcwd()."/curl_result.txt", "w");
		// set curl options
		curl_setopt($ch, CURLOPT_FILE, $fp);				
		// execute curl
		curl_exec($ch);
		// close the curl and file handles
		curl_close($ch);
		fclose($fp);	
		
		// Search for syndicates
		// search the curl result syndicates
		$lines = file(getcwd()."/curl_result.txt");
		foreach($lines as $line)
			{
				 // Check if the line is an option value line
				 if ( strpos($line, '<option value=') !== false )
					{
						// if here, line is an option value line, so parse it for index and name eg <option value="119">1851MarchMarriages</option>
						$index = $this->get_string_between($line, '<option value="', '">');
						$name = $this->get_string_between($line, '">', '</option>');
						// now get the coordinator name and email from FreeBMD for this syndicate
						// set curl handle and results file handle
						$url = "https://www.freebmd.org.uk/cgi/show-synd-info.pl?syndID=".$index;
						$ch = curl_init($url);
						$fp = fopen(getcwd()."/curl_result_2.txt", "w");
						curl_setopt($ch, CURLOPT_FILE, $fp);				
						curl_exec($ch);
						curl_close($ch);
						fclose($fp);
						// now search for coordinator line
						$details = file(getcwd()."/curl_result_2.txt");
						foreach($details as $key => $detail)
							{
								if ( strpos($detail, '<CAPTION ALIGN=TOP>Co-ordinators</CAPTION>') !== false )
									{
										// if here the search string was found, now get the details required from the next detail line
										$nextkey = $key + 1;
										$coordinator = $details[$nextkey];
										$leader = $this->get_string_between($coordinator, '<tr><td>', '</td> <td>');
										$email = $this->get_string_between($coordinator, '</td> <td>', '</td> <td>');
										break;
									}
							}
							
						// ok now I have everything to update or insert the syndicate table
						// clean the data
						$name = html_entity_decode($name, ENT_QUOTES);
						$leader = html_entity_decode($leader, ENT_QUOTES);
						$email = html_entity_decode($email, ENT_QUOTES);
						// does the record exist?
						$syndicate = $syndicate_model	->where('BMD_syndicate_index',  $index)
																			->find();
						// update or insert?
						if ( ! $syndicate )
							{
								$builder->set('BMD_syndicate_index', $index);
								$builder->set('BMD_syndicate_name', $name);
								$builder->set('BMD_syndicate_leader', $leader);
								$builder->set('BMD_syndicate_email', $email);
								$builder->insert();
							}
						else
							{
								$builder->where('BMD_syndicate_index', $index);
								$builder->set('BMD_syndicate_name', $name);
								$builder->set('BMD_syndicate_leader', $leader);
								$builder->set('BMD_syndicate_email', $email);
								$builder->update();
							}
					}
			}
		// go back to create BMD step 1
		$session->set('message_2', 'Syndicates have been refreshed.');
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('header/create_BMD_step1/2') );
	}
	
	public function get_string_between($string, $start, $end)
	{
		$string = ' ' . $string;
		$ini = strpos($string, $start);
		if ($ini == 0) return '';
		$ini += strlen($start);
		$len = strpos($string, $end, $ini) - $ini;
		return substr($string, $ini, $len);
	}
}
