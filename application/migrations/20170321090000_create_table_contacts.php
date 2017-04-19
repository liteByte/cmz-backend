<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_contacts extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
                'contact_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'auto_increment'=>  TRUE
                ),
                'medical_insurance_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'null'          =>  FALSE
                ),
                'sector' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  50,
                    'null'          =>  FALSE
                ),
                'phone_number' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  25,
                    'null'          =>  TRUE
                ),
                'email' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  30,
                    'null'          =>  TRUE
                ),
                'active' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  30,
                    'null'          =>  FALSE
                ),
                'update_date'  => array(
                    'type'          =>  'TIMESTAMP',
                    'null'          =>  TRUE,
                ),
                'modify_user_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'null'          =>  TRUE
                )
        ));
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (medical_insurance_id) REFERENCES medical_insurance(medical_insurance_id)');
        $this->dbforge->add_key('contact_id', TRUE);
        $this->dbforge->create_table('contacts');

        $data = array(
            array(
                'denomination'          => "37",
                'sector'                => "Gerencia",
                'phone_number'          => "4444-4444",
                'email'                 => "gerenciaOsde@osde.com",
                'active'                => 'active'
            )
        );

        $this->db->insert_batch('contacts', $data);
    }


    public function down(){

        $this->dbforge->drop_table('contacts');

    }









}
