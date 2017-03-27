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
            'nomenclator_type'  => [
                'type'          =>  'VARCHAR',
                'constraint'    =>  50,
                'null'          =>  FALSE
            ],
            'provision'         => [
                'type'          =>  'VARCHAR',
                'constraint'    =>  50,
                'null'          =>  FALSE
            ],
            'type' => [
                'type'          =>  'VARCHAR',
                'constraint'    =>  50,
                'null'          =>  FALSE
            ],
            'period_of_validity'=> [
                'type'          =>  'VARCHAR',
                'constraint'    =>  50,
                'null'          =>  FALSE
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
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (plan_id)                       REFERENCES plans(plan_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (medical_insurance_id)          REFERENCES medical_insurance(medical_insurance_id)');
        $this->dbforge->add_field('UNIQUE KEY coverage_key (medical_insurance_id, plan_id,nomenclator_type, provision )');
        $this->dbforge->add_key('id_special_conditions', TRUE);
        $this->dbforge->create_table('special_conditions');


        $data = [
            [
                'medical_insurance_id'          => 1,
                'plan_id'                       => 1,
                'nomenclator_type'              => 'NN',
                'provision'                     => '12345678',
                'type'                          => 'Completo',
                'period_of_validity'            => '2017/12',
                'type_of_values'                => TRUE,
                'group_of_values'               => TRUE,
            ],
            [
                'medical_insurance_id'          => 2,
                'plan_id'                       => 3,
                'nomenclator_type'              => 'NN',
                'provision'                     => '12345678',
                'type'                          => 'Completo',
                'period_of_validity'            => '2017/12',
                'type_of_values'                => FALSE,
                'group_of_values'               => FALSe,
            ]
        ];
        $this->db->insert_batch('special_conditions', $data);
    }


    public function down(){
        $this->dbforge->drop_table('special_conditions');
    }

}