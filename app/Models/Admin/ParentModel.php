<?php namespace App\Models;

use CodeIgniter\Model;

class ParentModel extends Model
{
    protected $table = 'parents';
    protected $primaryKey = 'parent_id';
    protected $allowedFields = [
        'father_cnic', 'f_name', 'father_contact', 'father_email',
        'father_occupation', 'father_office_address', 'm_name',
        'mother_contact', 'whatsapp', 'address_line1', 'city',
        'hear_source', 'emergency_contact_person', 'emergency_contact',
        'a_address', 'religion', 'campus_id'
    ];
    
    protected $returnType = 'object';
}