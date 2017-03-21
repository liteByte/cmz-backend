<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_coverage extends CI_Migration{
    public function up(){

        $this->dbforge->add_field(array(
            'id_coverage' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'auto_increment'=>  TRUE
            ),
            'plan_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'medical_insurance_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'status' => array(
                'type'          =>  'BOOLEAN',
                'null'          =>  FALSE,
                'unsigned'      =>  TRUE,
                'DEFAULT'       => TRUE
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
            )
        ));

        $this->dbforge->add_field("date_created  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (plan_id)              REFERENCES plans(plan_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (medical_insurance_id) REFERENCES medical_insurance(medical_insurance_id)');
        $this->dbforge->add_field('UNIQUE KEY coverage_key (plan_id,medical_insurance_id)');
        $this->dbforge->add_key('id_coverage', TRUE);
        $this->dbforge->create_table('coverages');

        $data = array(
            array(
                'plan_id'               => 1,
                'medical_insurance_id'  => 1,
            ),
            array(
                'plan_id'               => 2,
                'medical_insurance_id'  => 1,
            )
        );

        $this->db->insert_batch('coverages', $data);


    }
    public function down(){
        $this->dbforge->drop_table('coverages');
    }
}
