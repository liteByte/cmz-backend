<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_special_conditions_details extends CI_Migration{

    public function up(){
        $this->dbforge->add_field([
            'id_details_special_conditions' => [
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'auto_increment'=>  TRUE
            ],
            'type_unit'         =>  [
                'type'          =>  'VARCHAR',
                'constraint'    =>  15,
                'unsigned'      =>  TRUE,
                'null'          =>  TRUE,
            ],
            'honorary'          => [
                'type'          =>  'DECIMAL',
                'constraint'    =>  '5,2',
                'unsigned'      =>  TRUE,
                'null'          =>  TRUE,
            ],
            'expenses'          => [
                'type'          =>  'DECIMAL',
                'constraint'    =>  '5,2',
                'unsigned'      =>  TRUE,
                'null'          =>  TRUE,
            ],
            'unit'              => [
                'type'          =>  'VARCHAR',
                'constraint'    =>  2,
                'unsigned'      =>  TRUE,
                'null'          =>  TRUE,
            ],
            'quantity_units'    => [
                'type'          =>  'VARCHAR',
                'constraint'    =>  10,
                'unsigned'      =>  TRUE,
                'null'          =>  TRUE,
            ],
            'id_special_conditions' => [
                'type'          =>  'INT',
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ],
        ]);

        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_special_conditions)    REFERENCES special_conditions(id_special_conditions)');
        $this->dbforge->add_field("date_created  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        $this->dbforge->add_key('id_details_special_conditions', TRUE);
        $this->dbforge->create_table('special_conditions_details');

        /* Insert test data */
        $data = [
            [
                'type_unit'  => 'Ambulatorios',
                'honorary'   => 20,
                'expenses'   => 20,
                'id_special_conditions' => 1,
            ],
            [
                'type_unit'  => 'Internación',
                'honorary'   => 20,
                'expenses'   => 20,
                'id_special_conditions' => 1,
            ],
        ];
        $this->db->insert_batch('special_conditions_details', $data);

        $data = [
            [
                'unit'  => 'A',
                'quantity_units'   => '20',
                'id_special_conditions' => 2,
            ]
        ];


        $this->db->insert_batch('special_conditions_details', $data);

    }

    public function down(){
        $this->dbforge->drop_table('special_conditions_details');
    }



}
