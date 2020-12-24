<?php namespace App\Models;

use CodeIgniter\Model;

class Identity_Model extends Model
{
    protected $table = 'identity';
    protected $primaryKey = 'BMD_identity_index';
    protected $allowedFields = ['BMD_identity_index', 'BMD_user', 'BMD_password', 'BMD_realname', 'BMD_email', 'BMD_total_records'];
    protected $returnType = 'array';
}
