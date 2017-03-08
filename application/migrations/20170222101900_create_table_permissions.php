<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_permissions extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
                'permission_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'auto_increment'=>  TRUE
                ),
                'name' => array(
                    'type'          => 'VARCHAR',
                    'constraint'    =>  100,
                    'null'          => FALSE
                )
        ));
        $this->dbforge->add_key('permission_id', TRUE);
        $this->dbforge->create_table('permissions');


        $data = array(
            array(
                'name' => "ABMusuarios"
            ),
            array(
                'name' => "ABMbancos"
            ),
            array(
                'name' => "ABMespecialidades"
            )
        );

        $this->db->insert_batch('permissions', $data);
    }


    public function down(){

        $this->dbforge->drop_table('permissions');

    }

}