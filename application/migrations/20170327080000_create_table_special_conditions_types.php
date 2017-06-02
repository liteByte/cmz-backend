<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_special_conditions_types extends CI_Migration{

    public function up(){

        $this->dbforge->add_field([
            'id_special_conditions_type' => [
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'auto_increment'=>  TRUE
            ] ,
            'id_type'           => [
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
            ],
            'description'       => [
                'type'          =>  'VARCHAR',
                'constraint'    =>  100,
                'null'          =>  FALSE
            ]
        ]);

        $this->dbforge->add_key('id_special_conditions_type', TRUE);
        $this->dbforge->create_table('special_conditions_type');

        $data = [
            [
                'id_type'    => 0,
                'description'=> 'Completo'
            ],
            [
                'id_type'    => 1,
                'description'=> 'Cirujano Especialista'
            ],
            [
                'id_type'    => 2,
                'description'=> 'Ayudante'
            ],
            [
                'id_type'    => 3,
                'description'=> 'Anestesista'
            ],
            [
                'id_type'    => 4,
                'description'=> 'Gasto'
            ]
        ];

        $this->db->insert_batch('special_conditions_type', $data);
    }


    public function down(){
        $this->dbforge->drop_table('special_conditions_type');
    }


}
