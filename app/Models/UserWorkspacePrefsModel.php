<?php

namespace App\Models;

use CodeIgniter\Model;

class UserWorkspacePrefsModel extends Model
{
    protected $table         = 'user_workspace_prefs';
    protected $primaryKey    = 'user_id';
    protected $allowedFields = ['user_id', 'campus_id', 'session_id', 'updated_at'];
    protected $returnType      = 'array';
    protected $useTimestamps   = false;
}
