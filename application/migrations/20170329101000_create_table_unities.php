<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_unities extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
                'unity_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'auto_increment'=>  TRUE
                ),
                'unity' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  1,
                    'null'          =>  FALSE
                ),
                'movement' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  1,
                    'null'          =>  FALSE
                ),
                'expenses' => array(
                    'type'          =>  'DECIMAL',
                    'constraint'    =>  '7,4',
                    'null'          =>  FALSE
                )
        ));
        $this->dbforge->add_key('unity_id', TRUE);
        $this->dbforge->create_table('unities');

        $data = array(
            array(
                'unity'     => "A",
                'movement'  => "F",
                'expenses'  => "50"
            ),
            array(
                'unity'     => "B",
                'movement'  => "U",
                'expenses'  => "20"
            ),
            array(
                'unity'     => "E",
                'movement'  => "U",
                'expenses'  => "20"
            ),
            array(
                'unity'     => "G",
                'movement'  => "U",
                'expenses'  => "20"
            ),
            array(
                'unity'     => "P",
                'movement'  => "F",
                'expenses'  => "80"
            ),
            array(
                'unity'     => "Q",
                'movement'  => "U",
                'expenses'  => "90"
            ),
            array(
                'unity'     => "R",
                'movement'  => "U",
                'expenses'  => "90"
            ),
            array(
                'unity'     => "V",
                'movement'  => "U",
                'expenses'  => "90"
            )
        );

        $this->db->insert_batch('unities', $data);
    }


    public function down(){

        $this->dbforge->drop_table('unities');

    }









}
