<?php namespace App\Models;

use CodeIgniter\Model;

class Detail_Data_Model extends Model
{
    protected $table = 'detail_data';
    protected $primaryKey = 'BMD_index';
    protected $allowedFields = ['BMD_index', 'BMD_header_index', 'BMD_line_sequence', 'BMD_surname', 'BMD_firstname', 'BMD_secondname', 'BMD_thirdname',
													'BMD_partnername', 'BMD_district', 'BMD_volume', 'BMD_registration', 'BMD_page', 'BMD_status', 'BMD_age'];
    protected $returnType = 'array';
}
