<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_plans extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
                'plan_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'auto_increment'=>  TRUE
                ),
                'description' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  50,
                    'null'          =>  FALSE
                ),
                'medical_insurance_denom' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  50,
                    'null'          =>  FALSE
                ),
                'medical_insurance_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'null'          => FALSE
                ),
                'active'            => array(
                    'type'          => 'VARCHAR',
                    'constraint'    =>  30,
                    'null'          => FALSE
                ),
                'update_date'  => array(
                    'type'          => 'TIMESTAMP',
                    'null'          => TRUE,
                ),
                'modify_user_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'null'          => TRUE
                )
        ));
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (medical_insurance_id) REFERENCES medical_insurance(medical_insurance_id)');
        $this->dbforge->add_key('plan_id', TRUE);
        $this->dbforge->create_table('plans');

        $data = array(
            array(
                'description'                   => "210",
                'medical_insurance_denom'       => "OSDE",
                'medical_insurance_id'          => "1",
                'active'                        => 'active'
            ),
            array(
                'description'                   => "310",
                'medical_insurance_denom'       => "OSDE",
                'medical_insurance_id'          => "1",
                'active'                        => 'active'
            )
        );

        $this->db->insert_batch('plans', $data);
    }


    public function down(){

        $this->dbforge->drop_table('plans');

    }









}
