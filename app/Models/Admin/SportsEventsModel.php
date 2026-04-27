<?php

namespace App\Models\Admin;

use CodeIgniter\Model;

class SportsEventsModel extends Model
{
    protected $table      = 'sports_events';
    protected $primaryKey = 'event_id';

    protected $allowedFields = [
        'event_name',
        'gender',
        'min_age',
        'max_age',
        'event_time',
        'order',
    ];
}
