<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * MainSeeder — Seeds test data for the Personal Task Management MVP.
 *
 * Creates:
 *  - 1 demo user
 *  - 2 Spaces: "LabKom", "Kantor"
 *  - 2 Projects per Space
 *  - 5 Tickets per Project (varied priorities and statuses)
 */
class MainSeeder extends Seeder
{
    public function run(): void
    {
        // ----------------------------------------------------------------
        // 1. Demo User
        // ----------------------------------------------------------------
        $this->db->table('users')->insert([
            'name'       => 'Demo User',
            'email'      => 'demo@example.com',
            'password'   => password_hash('password123', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $userId = $this->db->insertID();

        // ----------------------------------------------------------------
        // 2. Spaces
        // ----------------------------------------------------------------
        $spaces = [
            ['name' => 'LabKom',  'description' => 'Laboratorium Komputer'],
            ['name' => 'Kantor',  'description' => 'Pekerjaan Kantor'],
        ];

        $spaceIds = [];
        foreach ($spaces as $space) {
            $this->db->table('spaces')->insert([
                'name'        => $space['name'],
                'description' => $space['description'],
                'user_id'     => $userId,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
            $spaceIds[$space['name']] = $this->db->insertID();
        }

        // ----------------------------------------------------------------
        // 3. Projects (2 per space)
        // ----------------------------------------------------------------
        $projectsData = [
            // LabKom
            [
                'space_id'    => $spaceIds['LabKom'],
                'name'        => 'Absensi Aslab',
                'description' => 'Manajemen absensi asisten laboratorium',
                'type'        => 'routine',
            ],
            [
                'space_id'    => $spaceIds['LabKom'],
                'name'        => 'Pengembangan Sistem',
                'description' => 'Pengembangan sistem informasi LabKom',
                'type'        => 'seasonal',
            ],
            // Kantor
            [
                'space_id'    => $spaceIds['Kantor'],
                'name'        => 'Project A',
                'description' => 'Proyek utama klien A',
                'type'        => 'seasonal',
            ],
            [
                'space_id'    => $spaceIds['Kantor'],
                'name'        => 'Operasional Harian',
                'description' => 'Tugas operasional sehari-hari',
                'type'        => 'routine',
            ],
        ];

        $projectIds = [];
        foreach ($projectsData as $project) {
            $this->db->table('projects')->insert(array_merge($project, [
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]));
            $projectIds[$project['name']] = $this->db->insertID();
        }

        // ----------------------------------------------------------------
        // 4. Tickets (5 per project, varied priorities & statuses)
        // ----------------------------------------------------------------
        $ticketsData = [
            // --- Absensi Aslab ---
            'Absensi Aslab' => [
                [
                    'title'       => 'Buat jadwal shift aslab',
                    'description' => 'Susun jadwal shift asisten laboratorium untuk bulan ini.',
                    'priority'    => 'urgent-important',
                    'status'      => 'pending',
                ],
                [
                    'title'       => 'Review laporan mingguan',
                    'description' => 'Tinjau dan verifikasi laporan kehadiran mingguan.',
                    'priority'    => 'important-not-urgent',
                    'status'      => 'ongoing',
                ],
                [
                    'title'       => 'Update database aslab',
                    'description' => 'Perbarui data asisten laboratorium baru.',
                    'priority'    => 'urgent-not-important',
                    'status'      => 'done',
                ],
                [
                    'title'       => 'Rekap absensi bulanan',
                    'description' => 'Kompilasi data absensi untuk laporan bulanan.',
                    'priority'    => 'important-not-urgent',
                    'status'      => 'pending',
                ],
                [
                    'title'       => 'Koordinasi jadwal ujian',
                    'description' => 'Koordinasi jadwal aslab saat periode ujian.',
                    'priority'    => 'not-urgent-not-important',
                    'status'      => 'waiting-approval',
                ],
            ],
            // --- Pengembangan Sistem ---
            'Pengembangan Sistem' => [
                [
                    'title'       => 'Desain database sistem absensi',
                    'description' => 'Rancang skema database untuk sistem absensi baru.',
                    'priority'    => 'urgent-important',
                    'status'      => 'done',
                ],
                [
                    'title'       => 'Buat API endpoint absensi',
                    'description' => 'Implementasi REST API untuk input absensi.',
                    'priority'    => 'urgent-important',
                    'status'      => 'ongoing',
                ],
                [
                    'title'       => 'Integrasi sistem lama',
                    'description' => 'Migrasi data dari sistem lama ke sistem baru.',
                    'priority'    => 'important-not-urgent',
                    'status'      => 'pending',
                ],
                [
                    'title'       => 'Testing modul laporan',
                    'description' => 'Uji coba modul pembuatan laporan otomatis.',
                    'priority'    => 'important-not-urgent',
                    'status'      => 'pending',
                ],
                [
                    'title'       => 'Dokumentasi teknis',
                    'description' => 'Buat dokumentasi teknis untuk developer.',
                    'priority'    => 'not-urgent-not-important',
                    'status'      => 'pending',
                ],
            ],
            // --- Project A ---
            'Project A' => [
                [
                    'title'       => 'Analisis kebutuhan klien',
                    'description' => 'Analisis dan dokumentasi kebutuhan bisnis klien A.',
                    'priority'    => 'urgent-important',
                    'status'      => 'done',
                ],
                [
                    'title'       => 'Buat presentasi proposal',
                    'description' => 'Siapkan materi presentasi untuk klien.',
                    'priority'    => 'urgent-important',
                    'status'      => 'ongoing',
                ],
                [
                    'title'       => 'Kirim proposal ke klien',
                    'description' => 'Kirim dokumen proposal via email resmi.',
                    'priority'    => 'urgent-not-important',
                    'status'      => 'waiting-approval',
                ],
                [
                    'title'       => 'Riset kompetitor',
                    'description' => 'Lakukan analisis kompetitor di industri klien.',
                    'priority'    => 'important-not-urgent',
                    'status'      => 'pending',
                ],
                [
                    'title'       => 'Siapkan kontrak kerja',
                    'description' => 'Draft kontrak kerjasama dengan klien A.',
                    'priority'    => 'important-not-urgent',
                    'status'      => 'pending',
                ],
            ],
            // --- Operasional Harian ---
            'Operasional Harian' => [
                [
                    'title'       => 'Cek email dan balas pesan',
                    'description' => 'Review dan balas semua email masuk.',
                    'priority'    => 'urgent-not-important',
                    'status'      => 'done',
                ],
                [
                    'title'       => 'Update laporan harian',
                    'description' => 'Perbarui laporan progress harian ke atasan.',
                    'priority'    => 'urgent-important',
                    'status'      => 'pending',
                ],
                [
                    'title'       => 'Meeting tim mingguan',
                    'description' => 'Ikut rapat koordinasi tim setiap Senin.',
                    'priority'    => 'important-not-urgent',
                    'status'      => 'pending',
                ],
                [
                    'title'       => 'Backup data server',
                    'description' => 'Pastikan backup data server berjalan rutin.',
                    'priority'    => 'important-not-urgent',
                    'status'      => 'ongoing',
                ],
                [
                    'title'       => 'Optimasi workflow dokumen',
                    'description' => 'Tingkatkan efisiensi alur kerja dokumen.',
                    'priority'    => 'not-urgent-not-important',
                    'status'      => 'pending',
                ],
            ],
        ];

        foreach ($ticketsData as $projectName => $tickets) {
            $projectId = $projectIds[$projectName];
            foreach ($tickets as $ticket) {
                $this->db->table('tickets')->insert(array_merge($ticket, [
                    'project_id' => $projectId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]));
            }
        }

        echo "✅ Seed selesai! Demo user: demo@example.com / password123\n";
    }
}
