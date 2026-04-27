<?php
namespace App\Models\Frontend;

use CodeIgniter\Model;




class Model_menus extends Model {

	public function get_list_menus($role, $level = null, $parent = null)
	{
		$this->db->select('m.*')
        			->from('frontend_menus as m')
        			->join('frontend_user_privileges as up','m.id = up.menu_id')
        			->where('up.role_id', $role)
        			->where('up.priv_read', 1)
        			->where('m.is_published', 1)
        			->order_by('m.menu_order', 'ASC');

        if($level !== null)
            $this->db->where('m.level', $level);

        if($parent !== null)
            $this->db->where('m.parent', $parent);
        
        $query = $this->db->get();

        if($query->num_rows() > 0)
            $result = $query->result_array();
        else
            $result = array();

        return $result;
	}

    public function get_menu($role_id, $link)
    {
        $this->db->select('m.id, m.menu, up.priv_read as access_module, is_published,
                    CONCAT(up.priv_create,",",up.priv_update,",",up.priv_delete) as privileges')
                    ->from('frontend_menus as m')
                    ->join('frontend_user_privileges as up','m.id = up.menu_id')
                    ->where('up.role_id',$role_id)
                    ->where('link', $link);

        $query = $this->db->get();
   
        if($query->num_rows() > 0)
            $result = $query->row_array();
        else
            $result = array();

        return $result;
    }
}

/* End of file Model_menus.php */
/* Location: ./application/models/Model_menus.php */