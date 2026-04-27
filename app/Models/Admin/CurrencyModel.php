<?php 
namespace App\Models\Admin;

use CodeIgniter\Model;

class CurrencyModel extends Model
{
    protected $table         = 'currencies';
    protected $primaryKey    = 'code';
    protected $returnType    = 'array';
    protected $allowedFields = ['code','name','symbol','decimal_places'];

    protected $useAutoIncrement = false; // primary key is code, not id

    public function activeList(): array
    {
        // If you later add is_active, filter here; for now return all
        return $this->orderBy('code')->findAll();
    }
}
