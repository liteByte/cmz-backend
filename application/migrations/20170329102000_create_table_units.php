<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_units extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
                'unit_id' => array(
                    'type'          =>  'INT',
                    'unsigned'      =>  TRUE,
                    'auto_increment'=>  TRUE
                ),
                'fee_id' => array(
                    'type'          =>  'INT',
                    'unsigned'      =>  TRUE
                ),
                'unit' => array(
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
        $this->dbforge->add_key('unit_id', TRUE);
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (fee_id) REFERENCES fees(fee_id)');
        $this->dbforge->create_table('units');

        $data = array(
            array(
                'fee_id'    => "1",
                'unit'     => "A",
                'movement'  => "F",
                'expenses'  => "50"
            ),
            array(
                'fee_id'    => "1",
                'unit'     => "B",
                'movement'  => "U",
                'expenses'  => "20"
            ),
            array(
                'fee_id'    => "1",
                'unit'     => "E",
                'movement'  => "U",
                'expenses'  => "20"
            ),
            array(
                'fee_id'    => "1",
                'unit'     => "G",
                'movement'  => "U",
                'expenses'  => "20"
            ),
            array(
                'fee_id'    => "1",
                'unit'     => "P",
                'movement'  => "F",
                'expenses'  => "80"
            ),
            array(
                'fee_id'    => "1",
                'unit'     => "Q",
                'movement'  => "U",
                'expenses'  => "90"
            ),
            array(
                'fee_id'    => "1",
                'unit'     => "R",
                'movement'  => "U",
                'expenses'  => "90"
            ),
            array(
                'fee_id'    => "1",
                'unit'     => "V",
                'movement'  => "U",
                'expenses'  => "90"
            )
        );

        $this->db->insert_batch('units', $data);
    }


    public function down(){

        $this->dbforge->drop_table('units');

    }









}
