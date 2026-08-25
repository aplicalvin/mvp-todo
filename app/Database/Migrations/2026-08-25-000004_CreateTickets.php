<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: Create tickets table
 * A Ticket is a task within a Project, using Eisenhower priority matrix.
 */
class CreateTickets extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'project_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => false,
            ],
            'description' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
            ],
            'priority' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'urgent-important',
                    'important-not-urgent',
                    'urgent-not-important',
                    'not-urgent-not-important',
                ],
                'default' => 'not-urgent-not-important',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'ongoing', 'done', 'waiting-approval', 'cancelled'],
                'default'    => 'pending',
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tickets');
    }

    public function down(): void
    {
        $this->forge->dropTable('tickets', true);
    }
}
