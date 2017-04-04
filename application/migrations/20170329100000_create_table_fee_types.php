<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_fee_types extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
                'fee_type_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'auto_increment'=>  TRUE
                ),
                'description' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  50,
                    'null'          =>  FALSE
                )
        ));
        $this->dbforge->add_key('fee_type_id', TRUE);
        $this->dbforge->create_table('fee_types');

        $data = array(
            array(
                'description' => "Arancel CMZ"
            ),
            array(
                'description' => "Arancel FEMEBA",
            )
        );

        $this->db->insert_batch('fee_types', $data);
    }


    public function down(){

        $this->dbforge->drop_table('fee_types');

    }









}
