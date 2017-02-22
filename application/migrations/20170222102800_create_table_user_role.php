<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_users extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
                'user_role_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'auto_increment'=>  TRUE
                ),
                'user_id' => array(
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
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (user_id) REFERENCES users(id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (role_id) REFERENCES roles(role_id)');
        $this->dbforge->add_key('user_role_id', TRUE);
        $this->dbforge->create_table('user_role');
    }


    public function down(){

        $this->dbforge->drop_table('user_role');

    }

}
