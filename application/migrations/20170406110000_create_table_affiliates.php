<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_affiliates extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
            'affiliate_id' => array(
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
            'plan_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'affiliate_number' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  30,
                'null'          =>  FALSE
            ),
            'affiliate_name'   => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  50,
                'null'          =>  FALSE
            ),
            'active' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  30,
                'null'          =>  FALSE
            ),
            'update_date'  => array(
                'type'          =>  'TIMESTAMP',
                'null'          =>  TRUE
            ),
            'modify_user_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  TRUE
            )
        ));
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (medical_insurance_id)                REFERENCES medical_insurance(medical_insurance_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (plan_id)                             REFERENCES plans(plan_id)');
        $this->dbforge->add_field('UNIQUE KEY affiliate_key (affiliate_number)');
        $this->dbforge->add_key('affiliate_id', TRUE);
        $this->dbforge->create_table('affiliates');

    }


    public function down(){

        $this->dbforge->drop_table('affiliates');

    }









}
