?php
namespace App\Models;

use CodeIgniter\Model;

class SLCModel extends Model
{
    protected $table = 'school_leaving_certificates';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'student_id', 'slc_no', 'full_name', 'father_name', 'mother_name',
        'dob', 'religion', 'nationality', 'admission_date', 'class_name',
        'section_name', 'leaving_date', 'leaving_reason', 'conduct',
        'generated_by', 'generated_date'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    public function getSlcWithDetails($slcId)
    {
        return $this->where('id', $slcId)->first();
    }
    
    public function getStudentSlcs($studentId)
    {
        return $this->where('student_id', $studentId)
                    ->orderBy('generated_date', 'DESC')
                    ->findAll();
    }
}