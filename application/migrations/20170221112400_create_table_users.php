<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_users extends CI_Migration{



    public function up(){

        $this->dbforge->add_field(array(
                'user_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'auto_increment'=>  TRUE
                ),
                'document_type' => array(
                    'type'          => 'VARCHAR',
                    'constraint'    =>  20,
                    'null'          => FALSE
                ),
                'document_number' => array(
                    'type'          => 'VARCHAR',
                    'constraint'    => 50,
                    'null'          => FALSE
                ),
                'name' => array(
                    'type'          => 'VARCHAR',
                    'constraint'    => 100,
                    'null'          => FALSE
                ),
                'email' => array(
                    'type'          => 'VARCHAR',
                    'constraint'    => 100,
                    'null'          => FALSE
                ),
                'last_name' => array(
                    'type'          => 'VARCHAR',
                    'constraint'    => 100,
                    'null'          => FALSE
                ),
                'password'      => array(
                    'type'          => 'VARCHAR',
                    'constraint'    =>  150,
                    'null'          => FALSE
                ),
                'active'        => array(
                    'type'          => 'VARCHAR',
                    'constraint'    =>  150,
                    'null'          => FALSE
                ),
                'date_update'  => array(
                    'type'          => 'TIMESTAMP',
                    'null'          => TRUE,
                ),
                'down_user_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'null'          => TRUE
                ),
        ));
        $this->dbforge->add_field("date_created  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        $this->dbforge->add_key('user_id', TRUE);
        $this->dbforge->add_field('UNIQUE KEY user_key (document_type,document_number)');
        $this->dbforge->create_table('users');



            $data = array(
                array(
                    'document_type' => "DNI",
                    'document_number' => "1234567",
                    'name' => "NameTest",
                    'email' => "email@gmail.com",
                    'last_name' => "LastNameTest",
                    'password' => $this->hash->encrypt('1234567'),
                    'active' => "active",
                ),
                array(
                    'document_type' => "DNI",
                    'document_number' => "12345678",
                    'name' => "NameTest 2",
                    'email' => "email2@gmail.com",
                    'last_name' => "LastNameTest 2",
                    'password' => $this->hash->encrypt('1234567'),
                    'active' => "active",
                )
                );

            $this->db->insert_batch('users', $data);


    }


    public function down(){

        $this->dbforge->drop_table('users');

    }









}
