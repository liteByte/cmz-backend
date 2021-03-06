<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_fees extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
                'fee_id' => array(
                    'type'          =>  'INT',
                    'unsigned'      =>  TRUE,
                    'auto_increment'=>  TRUE
                ),
                'medical_insurance_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE
                ),
                'plan_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE
                ),
                'fee_type_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE
                ),
                'upload_date'  => array(
                    'type'          => 'DATE',
                    'null'          => FALSE,
                ),
                'period_since'  => array(
                    'type'          => 'DATE',
                    'null'          => FALSE,
                ),
                'period_until'  => array(
                    'type'          => 'DATE',
                    'null'          => TRUE,
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
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (medical_insurance_id)    REFERENCES medical_insurance(medical_insurance_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (plan_id)                 REFERENCES plans(plan_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (fee_type_id)             REFERENCES fee_types(fee_type_id)');
        $this->dbforge->add_field('UNIQUE KEY fee_key (medical_insurance_id,plan_id,fee_type_id,period_since)');
        $this->dbforge->add_key('fee_id', TRUE);
        $this->dbforge->create_table('fees');

        $now = date('Y-m-d H:i:s');

        $data = array(
            array(
                'medical_insurance_id'  => "1",
                'plan_id'               => "2",
                'fee_type_id'           => "1",
                'upload_date'           => $now,
                'period_since'          => $now,
                'period_until'          => null,
                'active'                => "active"
            )
        );

        $this->db->insert_batch('fees', $data);
    }


    public function down(){

        $this->dbforge->drop_table('fees');

    }









}
