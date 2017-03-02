<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_banks extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
                'bank_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'auto_increment'=>  TRUE
                ),
                'bank_code' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  5,
                    'unique'        =>  TRUE,
                    'null'          =>  FALSE
                ),
                'corporate_name' => array(
                    'type'          => 'VARCHAR',
                    'constraint'    =>  35,
                    'null'          =>  FALSE
                ),
                'address' => array(
                    'type'          => 'VARCHAR',
                    'constraint'    => 40,
                    'null'          => TRUE
                ),
                'location' => array(
                    'type'          => 'VARCHAR',
                    'constraint'    => 30,
                    'null'          => TRUE
                ),
                'phone_number' => array(
                    'type'          => 'VARCHAR',
                    'constraint'    => 25,
                    'null'          => TRUE
                ),
                'active'        => array(
                    'type'      => 'VARCHAR',
                    'constraint'    =>  150,
                    'null'          => FALSE
                )
        ));
        $this->dbforge->add_key('bank_id', TRUE);
        $this->dbforge->create_table('banks');
    }


    public function down(){

        $this->dbforge->drop_table('banks');

    }









}
