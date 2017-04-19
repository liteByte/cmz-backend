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
                'medical_insurance_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'null'          =>  FALSE
                ),
                'active'            => array(
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
        $this->dbforge->add_key('plan_id', TRUE);
        $this->dbforge->add_field('UNIQUE KEY plan_key (medical_insurance_id,description)');
        $this->dbforge->create_table('plans');


    }


    public function down(){

        $this->dbforge->drop_table('plans');

    }









}
