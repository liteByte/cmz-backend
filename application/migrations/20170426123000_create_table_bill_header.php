<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_bill_header extends CI_Migration{

    public function up(){
        $this->dbforge->add_field([
            'bill_header_id'   => [
                'type'           => 'INT',
                'constraint'    =>  10,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE
            ],
            'id_bill'   => [
                'type'          => 'INT',
                'constraint'    =>  10,
                'unsigned'      => TRUE,
                'null'          =>  FALSE
            ],
            'settlement_name' => [
                'type'          =>  'VARCHAR',
                'constraint'    =>  50,
                'null'          =>  FALSE
            ],
            'address' => [
                'type'          =>  'VARCHAR',
                'constraint'    =>  50,
                'null'          =>  FALSE
            ],
            'location' => [
                'type'          =>  'VARCHAR',
                'constraint'    =>  100,
                'null'          =>  FALSE
            ],
            'postal_code' => [
                'type'          =>  'VARCHAR',
                'constraint'    =>  15,
                'null'          =>  FALSE
            ],
            'iva_description' => [
                'type'          =>  'VARCHAR',
                'constraint'    =>  40,
                'null'          =>  FALSE
            ],
            'cuit' => [
                'type'          =>  'BIGINT',
                'constraint'    =>  11,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ],
            'due_date'   => [
                'type'           => 'DATE',
                'null'           => FALSE
            ],
            'billing_date'   => [
                'type'           => 'DATE',
                'null'           => FALSE
            ],
            'payment_deadline' => [
                'type'          =>  'INT',
                'constraint'    =>  3,
                'null'          =>  FALSE
            ],
            'print_type' => [
                'type'          => 'BOOLEAN',
                'null'          => FALSE,
                'comment'       => '1 -> OS & 0 -> PLAN',
                'default'       => 1
            ]
        ]);

        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_bill) REFERENCES bill(id_bill)');
        $this->dbforge->add_key('bill_header_id', TRUE);
        $this->dbforge->create_table('bill_header');

    }

    public function down(){
        $this->dbforge->drop_table('bill_header');
    }
}