<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_specialities extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
                'speciality_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'auto_increment'=>  TRUE
                ),
                'speciality_code' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  5,
                    'unique'        =>  TRUE,
                    'null'          =>  FALSE
                ),
                'description' => array(
                    'type'          => 'VARCHAR',
                    'constraint'    =>  35,
                    'null'          =>  FALSE
                ),
                'active'        => array(
                    'type'      => 'VARCHAR',
                    'constraint'    =>  150,
                    'null'          => FALSE
                )
        ));
        $this->dbforge->add_key('speciality_id', TRUE);
        $this->dbforge->create_table('specialities');

        $data = array(
            array(
                'speciality_code' => "10",
                'description' => "Oftalmologia",
                'active' => "active"
            ),
            array(
                'speciality_code' => "11",
                'description' => "Cardiologia",
                'active' => "active"
            ),
            array(
                'speciality_code' => "12",
                'description' => "Urologia",
                'active' => "active"
            ),
        );

        $this->db->insert_batch('specialities', $data);
    }


    public function down(){

        $this->dbforge->drop_table('specialities');

    }









}
