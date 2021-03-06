<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_benefits extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
            'benefit_id' => array(
                'type'          =>  'BIGINT',
                'constraint'    =>  10,
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
            'period'  => array(
                'type'          => 'DATE',
                'null'          => FALSE
            ),
            'remesa'   => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  1,
                'null'          =>  TRUE
            ),
            'additional' => array(
                'type'          =>  'INT',
                'constraint'    =>  1,
                'null'          =>  TRUE,
                'comment'       => '1 -> specialist, 2 -> helper, 3-> anesthetic'
            ),
            'nomenclator_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
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
                'constraint'    =>  '8,2',
                'null'          =>  TRUE
            ),
            'benefit_date'  => array(
                'type'          => 'DATE',
                'null'          => TRUE
            ),
            'affiliate_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  TRUE
            ),
            'bill_number' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  20,
                'null'          =>  TRUE
            ),
            'id_bill'   => array(
                'type'          => 'INT',
                'constraint'    => 10,
                'unsigned'      => TRUE,
                'null'          => TRUE
            ),
            'modify_coverage' => array(
                'type'          =>  'TINYINT',
                'constraint'    =>  1,
                'unsigned'      =>  TRUE,
                'null'          =>  TRUE
            ),
            'new_honorary' => array(
                'type'          =>  'DECIMAL',
                'constraint'    =>  '8,2',
                'null'          =>  TRUE
            ),
            'new_expenses' => array(
                'type'          =>  'DECIMAL',
                'constraint'    =>  '8,2',
                'null'          =>  TRUE
            ),
            'value_honorary' => array(
                'type'          =>  'DECIMAL',
                'constraint'    =>  '8,2',
                'null'          =>  TRUE
            ),
            'value_expenses' => array(
                'type'          =>  'DECIMAL',
                'constraint'    =>  '8,2',
                'null'          =>  TRUE
            ),
            'value_unit'   => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  1,
                'null'          =>  TRUE
            ),
            'state' => array(
                'type'          =>  'INT',
                'null'          =>  FALSE,
                'constraint'    =>  3,
                'default'       =>  1,
                'comment'       => '1 -> Valorizada, 2-> Facturada, 3 -> Cobrada y 4-> Pagada'
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
                'null'          =>  TRUE,
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE
            )
        ));

        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_bill)                             REFERENCES bill(id_bill)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (medical_insurance_id)                REFERENCES medical_insurance(medical_insurance_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (plan_id)                             REFERENCES plans(plan_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_professional_data)                REFERENCES professionals(id_professional_data)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (internment_ambulatory_option_id)     REFERENCES internment_ambulatory_options(internment_ambulatory_option_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (maternal_plan_option_id)             REFERENCES maternal_plan_options(maternal_plan_option_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (holiday_option_id)                   REFERENCES holiday_options(holiday_option_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (billing_code_id)                     REFERENCES billing_codes(billing_code_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (nomenclator_id)                      REFERENCES nomenclators(nomenclator_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (affiliate_id)                        REFERENCES affiliates(affiliate_id)');
        $this->dbforge->add_key('benefit_id', TRUE);
        $this->dbforge->create_table('benefits');

    }


    public function down(){

        $this->dbforge->drop_table('benefits');

    }









}
