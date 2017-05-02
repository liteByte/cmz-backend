<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_special_conditions extends CI_Migration{


    public function up(){

        $this->dbforge->add_field([
            'id_special_conditions' => [
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'auto_increment'=>  TRUE
            ],
            'medical_insurance_id' => [
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ],
            'plan_id'           => [
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ],
            'provision'  => [
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ],
            'type' => [
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ],
            'period_since'  => [
                'type'          => 'DATE',
                'null'          => FALSE,
            ],
            'period_until'  => [
                'type'          => 'DATE',
                'null'          => TRUE,
            ],
            'type_of_values'    => [
                'type'          =>  'BOOLEAN',
                'null'          =>  FALSE,
                'comment'       => '1 -> fixed an 0 -> %'
            ],
            'group_of_values'   => [
                'type'          =>  'BOOLEAN',
                'null'          =>  FALSE,
                'comment'       => '1 -> spec an 0 -> units'
            ],

        ]);

        $this->dbforge->add_field("date_created  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (plan_id)                 REFERENCES plans(plan_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (medical_insurance_id)    REFERENCES medical_insurance(medical_insurance_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (type)                    REFERENCES special_conditions_type(id_special_conditions_type)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (provision)               REFERENCES nomenclators(nomenclator_id)');
        $this->dbforge->add_field('UNIQUE KEY coverage_key (medical_insurance_id, plan_id, provision, period_since)');
        $this->dbforge->add_key('id_special_conditions', TRUE);
        $this->dbforge->create_table('special_conditions');


    }


    public function down(){
        $this->dbforge->drop_table('special_conditions');
    }

}