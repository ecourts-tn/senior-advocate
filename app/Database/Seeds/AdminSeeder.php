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
                'email'             => 'admin.mhc@tn.gov.in',
                'mobile'            => '9000000001',
                'password_hash'     => password_hash('Admin@123', PASSWORD_DEFAULT),
                'role'              => 'admin',
                'is_active'         => true,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'name'              => 'Review Officer',
                'email'             => 'reviewer.mhc@tn.gov.in',
                'mobile'            => '9000000002',
                'password_hash'     => password_hash('Review@123', PASSWORD_DEFAULT),
                'role'              => 'reviewer',
                'is_active'         => true,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'name'              => 'Approving Authority',
                'email'             => 'approver.mhc@tn.gov.in',
                'mobile'            => '9000000003',
                'password_hash'     => password_hash('Approve@123', PASSWORD_DEFAULT),
                'role'              => 'approver',
                'is_active'         => true,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            // [
            //     'name'              => 'Demo Advocate',
            //     'email'             => 'advocate@example.com',
            //     'mobile'            => '9876543210',
            //     'password_hash'     => password_hash('Advocate@123', PASSWORD_DEFAULT),
            //     'role'              => 'applicant',
            //     'is_active'         => true,
            //     'email_verified_at' => $now,
            //     'created_at'        => $now,
            //     'updated_at'        => $now,
            // ],
        ];

        foreach ($users as $user) {
            $exists = $this->db->table('users')->where('email', $user['email'])->countAllResults();
            if ($exists === 0) {
                $this->db->table('users')->insert($user);
            }
        }
    }
}
