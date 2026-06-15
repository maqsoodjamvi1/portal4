<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call('DefaultRolesSeeder');
        $this->call('DefaultPermissionsSeeder');
        $this->call('DefaultRolePermissionsSeeder');

        if ($this->db->tableExists('quran_ayahs')) {
            $count = $this->db->table('quran_ayahs')->countAllResults();
            if ($count === 0) {
                $this->call('QuranAyahSeeder');
            }
        }
        if ($this->db->tableExists('quran_surahs')) {
            $count = $this->db->table('quran_surahs')->countAllResults();
            if ($count === 0) {
                $this->call('QuranReferenceSeeder');
            }
        }
    }
}
