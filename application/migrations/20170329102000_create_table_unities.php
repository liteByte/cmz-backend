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
                'fee_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE
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
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (fee_id)    REFERENCES fees(fee_id)');
        $this->dbforge->add_key('unity_id', TRUE);
        $this->dbforge->create_table('unities');

        $data = array(
            array(
                'fee_id'    => "1",
                'unity'     => "A",
                'movement'  => "F",
                'expenses'  => "50"
            ),
            array(
                'fee_id'    => "1",
                'unity'     => "B",
                'movement'  => "U",
                'expenses'  => "20"
            ),
            array(
                'fee_id'    => "2",
                'unity'     => "P",
                'movement'  => "F",
                'expenses'  => "80"
            ),
            array(
                'fee_id'    => "2",
                'unity'     => "Q",
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
