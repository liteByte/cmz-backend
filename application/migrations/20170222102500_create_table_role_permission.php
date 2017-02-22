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
                    'null'          => FALSE
                ),
                'role_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'null'          => FALSE
                )
        ));
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (role_id)       REFERENCES roles(role_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (permission_id) REFERENCES permissions(permission_id)');
        $this->dbforge->add_key('role_permission_id', TRUE);
        $this->dbforge->create_table('role_permissions');
    }


    public function down(){

        $this->dbforge->drop_table('role_permissions');

    }

}
