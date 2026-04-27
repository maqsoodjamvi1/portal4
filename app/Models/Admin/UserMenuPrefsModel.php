 <?php
namespace App\Models;

use CodeIgniter\Model;

class UserMenuPrefsModel extends Model
{
    protected $table         = 'user_menu_prefs';
    protected $primaryKey    = 'user_id';
    protected $allowedFields = ['user_id','prefs','updated_at'];
    protected $returnType    = 'array';
}

?>