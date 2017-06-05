<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_credit_debit extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
            'credit_debit_id' => array(
                'type'          =>  'INT',
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
            'id_bill' => array(
                'type'          =>  'INT',
                'constraint'    =>  10,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'type' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  1,
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
            'nomenclator_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'value_honorary' => array(
                'type'          =>  'DECIMAL',
                'constraint'    =>  '8,2',
                'null'          =>  FALSE
            ),
            'value_expenses' => array(
                'type'          =>  'DECIMAL',
                'constraint'    =>  '8,2',
                'null'          =>  FALSE
            ),
            'quantity' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'null'          =>  FALSE
            ),
            'concept_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'credit_debit_note_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  TRUE
        )


        ));

        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (medical_insurance_id)                REFERENCES medical_insurance(medical_insurance_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_bill)                             REFERENCES bill(id_bill)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_professional_data)                REFERENCES professionals(id_professional_data)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (nomenclator_id)                      REFERENCES nomenclators(nomenclator_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (concept_id)                          REFERENCES credit_debit_concepts(concept_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (credit_debit_note_id)                REFERENCES credit_debit_note(credit_debit_note_id)');
        $this->dbforge->add_key('credit_debit_id', TRUE);
        $this->dbforge->create_table('credit_debit');

    }


    public function down(){

        $this->dbforge->drop_table('credit_debit');

    }









}
