<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_medical_ins extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
            'medical_insurance_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'auto_increment'=>  TRUE
            ),
            'denomination' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  50,
                'null'          =>  FALSE
            ),
            'settlement_name' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  50,
                'null'          =>  FALSE
            ),
            'address' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  50,
                'null'          =>  FALSE
            ),
            'location' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  100,
                'null'          =>  FALSE
            ),
            'postal_code' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  15,
                'null'          =>  FALSE
            ),
            'website' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  100,
                'null'          =>  TRUE
            ),
            'cuit' => array(
                'type'          =>  'BIGINT',
                'constraint'    =>  11,
                'unique'        =>  TRUE,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'iva_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'gross_income' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  15,
                'null'          =>  FALSE
            ),
            'payment_deadline' => array(
                'type'          =>  'INT',
                'constraint'    =>  3,
                'null'          =>  FALSE
            ),
            'scope_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'maternal_plan' => array(
                'type'          =>  'INT',
                'constraint'    =>  1,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'admin_rights' => array(
                'type'          =>  'DECIMAL',
                'constraint'    =>  '5,2',
                'null'          =>  FALSE
            ),
            'femeba' => array(
                'type'          =>  'INT',
                'constraint'    =>  1,
                'null'          =>  FALSE
            ),
            'ret_jub_femeba' => array(
                'type'          =>  'INT',
                'constraint'    =>  1,
                'null'          =>  TRUE
            ),
            'federation_funds' => array(
                'type'          =>  'DECIMAL',
                'constraint'    =>  '5,2',
                'null'          =>  TRUE
            ),
            'ret_socios_honorarios' => array(
                'type'          =>  'DECIMAL',
                'constraint'    =>  '5,2',
                'null'          =>  FALSE
            ),
            'ret_socios_gastos' => array(
                'type'          =>  'DECIMAL',
                'constraint'    =>  '5,2',
                'null'          =>  FALSE
            ),
            'ret_nosocios_honorarios' => array(
                'type'          =>  'DECIMAL',
                'constraint'    =>  '5,2',
                'null'          =>  FALSE
            ),
            'ret_nosocios_gastos' => array(
                'type'          =>  'DECIMAL',
                'constraint'    =>  '5,2',
                'null'          =>  FALSE
            ),
            'ret_adherente_honorarios' => array(
                'type'          =>  'DECIMAL',
                'constraint'    =>  '5,2',
                'null'          =>  FALSE
            ),
            'ret_adherente_gastos' => array(
                'type'          =>  'DECIMAL',
                'constraint'    =>  '5,2',
                'null'          =>  FALSE
            ),
            'cobertura_fer_noct' => array(
                'type'          =>  'DECIMAL',
                'constraint'    =>  '5,2',
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
            ),
            'judicial' => array(
                'type'          =>  'BOOLEAN',
                'null'          =>  FALSE,
                'comment'       => '1 -> Jud & 0 -> No Jud',
                'default'       => 0
            ),
            'print' => array(
                'type'          =>  'BOOLEAN',
                'null'          =>  FALSE,
                'comment'       => '1 -> OS & 0 -> PLAN',
                'default'       => 1
            )
        ));
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (iva_id) REFERENCES iva(iva_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (scope_id) REFERENCES scopes(scope_id)');
        $this->dbforge->add_key('medical_insurance_id', TRUE);
        $this->dbforge->create_table('medical_insurance');

    }


    public function down(){

        $this->dbforge->drop_table('medical_insurance');

    }


}
