<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_scopes extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
                'scope_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'auto_increment'=>  TRUE
                ),
                'description' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  30,
                    'null'          =>  FALSE
                )
        ));
        $this->dbforge->add_key('scope_id', TRUE);
        $this->dbforge->create_table('scopes');

        $data = array(
            array(
                'description' => "Nacional"
            ),
            array(
                'description' => "Provincial"
            )
        );

        $this->db->insert_batch('scopes', $data);
    }


    public function down(){

        $this->dbforge->drop_table('scopes');

    }









}
