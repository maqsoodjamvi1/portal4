<?php
namespace App\Models;

use CodeIgniter\Model;

class CampusModel extends Model
{
    protected $table = 'campus';
    protected $primaryKey = 'campus_id';
    protected $allowedFields = ['campus_name', 'short_name', 'system_id', 'location'];
}