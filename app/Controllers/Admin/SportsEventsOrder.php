<?php namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\SportsEventsModel; 

class SportsEventsOrder extends BaseController
{
    public function index()
{
    $model = new SportsEventsModel();

    // DEBUG BLOCK
    $db = $model->db;
    $builder = $db->table($model->table)
      ->where('status', 1)  
                  ->orderBy('order', 'ASC');

    $query = $builder->get();

    if ($query === false) {
        // Dump DB error and stop
        dd($db->error());
    }

    $data['events'] = $query->getResultArray();

    return view('admin/sports/index', $data);
}
    // Update order through AJAX
    public function updateOrder()
    {
        $model = new SportsEventsModel();
        $orderData = $this->request->getPost('order');

        foreach ($orderData as $order => $id) {
            $model->update($id, ['order' => $order + 1]); 
        }

        return $this->response->setJSON(['status' => 'success']);
    }

    // Update event time
    public function updateTime()
    {
        $model = new SportsEventsModel();

        $id   = $this->request->getPost('id');
        $time = $this->request->getPost('event_time');

        $model->update($id, ['event_time' => $time]);

        return $this->response->setJSON(['status' => 'updated']);
    }
}
