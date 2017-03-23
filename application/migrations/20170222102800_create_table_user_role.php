<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_user_role extends CI_Migration{

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
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (user_id) REFERENCES users(user_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (role_id) REFERENCES roles(role_id)');
        $this->dbforge->add_key('user_role_id', TRUE);

        $attributes = array('ENGINE' => 'InnoDB');
//        $this->dbforge->create_table('table_name', FALSE, );
        $this->dbforge->create_table('user_role' , FALSE,  $attributes);


        $data = array(
            array(
                'user_id' => "1",
                'role_id' => "1"
            ),
            array(
                'user_id' => "2",
                'role_id' => "2"
            )
        );

        $this->db->insert_batch('user_role', $data);
    }

    


    public function down(){

        $this->dbforge->drop_table('user_role');

    }

}
