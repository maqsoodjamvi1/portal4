<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ImportStudentsJob extends BaseCommand
{
    protected $group       = 'Import';
    protected $name        = 'import:students';
    protected $description = 'Process pending student import jobs';

    public function run(array $params)
    {
        CLI::write('? ImportStudentsJob command executed.');
    }
}
