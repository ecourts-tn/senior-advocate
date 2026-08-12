<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $users = [
            [
                'name'              => 'Administrator',
                'email'             => 'techsupport.mhc@indiancourts.nic.in',
                'mobile'            => '04425301250',
                'password_hash'     => password_hash('@dMin@321#', PASSWORD_DEFAULT),
                'role'              => 'admin',
                'is_active'         => true,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'name'              => 'SSA Admin',
                'email'             => 'admin.mhc@tn.gov.in',
                'mobile'            => '04425301330',
                'password_hash'     => password_hash('S$aHcm@321#', PASSWORD_DEFAULT),
                'role'              => 'admin',
                'is_active'         => true,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
        ];

        foreach ($users as $user) {
            $exists = $this->db->table('users')->where('email', $user['email'])->countAllResults();
            if ($exists === 0) {
                $this->db->table('users')->insert($user);
            }
        }
    }
}
