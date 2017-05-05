<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_role_permission extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
                'role_permission_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'auto_increment'=>  TRUE
                ),
                'permission_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'null'          => FALSE
                ),
                'role_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'null'          => FALSE
                )
        ));
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (role_id)       REFERENCES roles(role_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (permission_id) REFERENCES permissions(permission_id)');
        $this->dbforge->add_key('role_permission_id', TRUE);
        $this->dbforge->create_table('role_permissions');

        $data = array(
            array(
                'permission_id' => "1",
                'role_id' => "1"
            ),
            array(
                'permission_id' => "2",
                'role_id' => "1"
            ),
            array(
                'permission_id' => "3",
                'role_id' => "1"
            ),
            array(
                'permission_id' => "4",
                'role_id' => "1"
            ),
            array(
                'permission_id' => "5",
                'role_id' => "1"
            ),
            array(
                'permission_id' => "6",
                'role_id' => "1"
            ),
            array(
                'permission_id' => "7",
                'role_id' => "1"
            ),
            array(
                'permission_id' => "1",
                'role_id' => "2"
            ),
            array(
                'permission_id' => "2",
                'role_id' => "2"
            ),
            array(
                'permission_id' => "8",
                'role_id' => "1"
            ),
            array(
                'permission_id' => "9",
                'role_id' => "1"
            ),
            array(
                'permission_id' => "10",
                'role_id' => "1"
            ),
            array(
                'permission_id' => "11",
                'role_id' => "1"
            ),
            array(
                'permission_id' => "12",
                'role_id' => "1"
            ),
            array(
                'permission_id' => "13",
                'role_id' => "1"
            ),
            array(
                'permission_id' => "14",
                'role_id' => "1"
            ),
            array(
                'permission_id' => "15",
                'role_id' => "1"
            )

        );

        $this->db->insert_batch('role_permissions', $data);
    }


    public function down(){

        $this->dbforge->drop_table('role_permissions');

    }

}
