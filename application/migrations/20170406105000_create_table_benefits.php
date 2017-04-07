<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_fees extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
            'benefit_id' => array(
                'type'          =>  'INT',
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
            'id_professional_data' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'registration_number'   => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  8,
                'null'          =>  FALSE
            ),
            'period'  => array(
                'type'          => 'DATE',
                'null'          => FALSE,
            ),
            'remesa'   => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  1,
                'null'          =>  TRUE
            ),
            'nomenclator_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'benefit' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  50,
                'null'          =>  FALSE
            ),
            'quantity' => array(
                'type'          =>  'INT',
                'constraint'    =>  4,
                'null'          =>  FALSE
            ),
            'billing_code_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'multiple_operation_value' => array(
                'type'          =>  'INT',
                'constraint'    =>  3,
                'null'          =>  FALSE
            ),
            'holiday_option_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  TRUE
            ),
            'maternal_plan_option_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  TRUE
            ),
            'internment_ambulatory_option_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'unit_price' => array(
                'type'          =>  'DECIMAL',
                'constraint'    =>  '5,2',
                'null'          =>  TRUE
            ),
            'benefit_date'  => array(
                'type'          => 'DATE',
                'null'          => TRUE,
            ),
            'affiliate_number' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  30,
                'null'          =>  TRUE
            ),
            'affiliate_name' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  50,
                'null'          =>  TRUE
            ),
            'bill_number' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  20,
                'null'          =>  TRUE
            ),
            'modify_coverage' => array(
                'type'          =>  'TINYINT',
                'constraint'    =>  1,
                'unsigned'      =>  TRUE,
                'null'          =>  TRUE
            ),
            'new_honorary' => array(
                'type'          =>  'DECIMAL',
                'constraint'    =>  '5,2',
                'null'          =>  TRUE
            ),
            'new_expenses' => array(
                'type'          =>  'DECIMAL',
                'constraint'    =>  '5,2',
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
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (medical_insurance_id)                REFERENCES medical_insurance(medical_insurance_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (plan_id)                             REFERENCES plans(plan_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_professional_data)                REFERENCES professionals(id_professional_data)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (internment_ambulatory_option_id)     REFERENCES internment_ambulatory_options(internment_ambulatory_option_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (maternal_plan_option_id)             REFERENCES maternal_plan_options(maternal_plan_option_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (holiday_option_id)                   REFERENCES holiday_options(holiday_option_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (billing_code_id)                     REFERENCES billing_code_id(billing_codes)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (nomenclator_id)                      REFERENCES nomenclator_id(nomenclators)');
        $this->dbforge->add_field('UNIQUE KEY benefit_key (medical_insurance_id,plan_id,registration_number,period,benefit)');
        $this->dbforge->add_key('benefit_id', TRUE);
        $this->dbforge->create_table('benefits');

        /*$data = array(
            array(
                'medical_insurance_id'  => "1",
                'plan_id'               => "2",
                'fee_type_id'           => "1",
                'upload_date'           => $now,
                'period'                => "2017-10-10",
                'active'                => "active"
            )
        );

        $this->db->insert_batch('benefits', $data);*/
    }


    public function down(){

        $this->dbforge->drop_table('benefits');

    }









}
