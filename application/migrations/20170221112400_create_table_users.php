<?php
/**
 * Created by PhpStorm.
 * User: Edgar
 * Date: 21/02/2017
 * Time: 11:24
 */


defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_users extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
                'id' => array(
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
                    'unique'        => TRUE,
                    'null'          => FALSE
                ),
                'name_user' => array(
                    'type'          => 'VARCHAR',
                    'constraint'    => 100,
                    'null'          => FALSE
                ),
                'email_user' => array(
                    'type'          => 'VARCHAR',
                    'constraint'    => 100,
                    'null'          => FALSE
                ),
                'last_name_user' => array(
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
                    'type'      => 'VARCHAR',
                    'constraint'    =>  150
                ),
                'date_update'  => array(
                    'type'          => 'TIMESTAMP',
                    'null'          => FALSE,
                ),
        ));
        $this->dbforge->add_field("date_created  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('users');
    }


    public function down(){

        $this->dbforge->drop_table('users');

    }








}