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
                    'constraint'    =>  34,
                    'null'          =>  TRUE
                ),
                'cuit' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  13,
                    'unique'        =>  TRUE,
                    'null'          =>  FALSE
                ),
                'iva_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'null'          => FALSE
                ),
                'gross_income' => array(
                    'type'          =>  'VARCHAR',
                    'constraint'    =>  15,
                    'null'          =>  FALSE
                ),
                'payment_deadline' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  3,
                    'null'          => FALSE
                ),
                'scope_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'null'          => FALSE
                ),
                'femeba' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  1,
                    'null'          => FALSE
                ),
                'ret_jub_femeba' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  1,
                    'null'          => TRUE
                ),
                'federation_funds' => array(
                    'type'          =>  'DECIMAL',
                    'constraint'    =>  '5,2',
                    'null'          => TRUE
                ),
                'admin_rights' => array(
                    'type'          =>  'DECIMAL',
                    'constraint'    =>  '5,2',
                    'null'          => TRUE
                ),
                'ret_socios_honorarios' => array(
                    'type'          =>  'DECIMAL',
                    'constraint'    =>  '5,2',
                    'null'          => FALSE
                ),
                'ret_socios_gastos' => array(
                    'type'          =>  'DECIMAL',
                    'constraint'    =>  '5,2',
                    'null'          => FALSE
                ),
                'ret_nosocios_honorarios' => array(
                    'type'          =>  'DECIMAL',
                    'constraint'    =>  '5,2',
                    'null'          => FALSE
                ),
                'ret_nosocios_gastos' => array(
                    'type'          =>  'DECIMAL',
                    'constraint'    =>  '5,2',
                    'null'          => FALSE
                ),
                'ret_adherente_honorarios' => array(
                    'type'          =>  'DECIMAL',
                    'constraint'    =>  '5,2',
                    'null'          => FALSE
                ),
                'ret_adherente_gastos' => array(
                    'type'          =>  'DECIMAL',
                    'constraint'    =>  '5,2',
                    'null'          => FALSE
                ),
                'cobertura_fer_noct' => array(
                    'type'          =>  'DECIMAL',
                    'constraint'    =>  '5,2',
                    'null'          => FALSE
                ),
                'active'            => array(
                    'type'          => 'VARCHAR',
                    'constraint'    =>  30,
                    'null'          => FALSE
                ),
                'update_date'  => array(
                    'type'          => 'TIMESTAMP',
                    'null'          => TRUE,
                ),
                'modify_user_id' => array(
                    'type'          =>  'INT',
                    'constraint'    =>  5,
                    'unsigned'      =>  TRUE,
                    'null'          => TRUE
                ),
        ));
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (iva_id) REFERENCES iva(iva_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (scope_id) REFERENCES scopes(scope_id)');
        $this->dbforge->add_key('medical_insurance_id', TRUE);
        $this->dbforge->create_table('medical_insurance');

        $data = array(
            array(
                  'denomination'                => "OSDE",
                  'settlement_name'             => "Osde S.A",
                  'address'                     => "Rivadavia 500",
                  'location'                    => "CABA",
                  'postal_code'                 => "1612",
                  'website'                     => "www.osde.com",
                  'cuit'                        => "123456789",
                  'iva_id'                      => 2,
                  'gross_income'                => 1234,
                  'payment_deadline'            => 15,
                  'scope_id'                    => 1,
                  'femeba'                      => 1,
                  'ret_jub_femeba'              => 1,
                  'federation_funds'            => '5.5',
                  'admin_rights'                => '12.6',
                  'ret_socios_honorarios'       => '12.6',
                  'ret_socios_gastos'           => '12.6',
                  'ret_nosocios_honorarios'     => '12.6',
                  'ret_nosocios_gastos'         => '12.6',
                  'ret_adherente_honorarios'    => '12.6',
                  'ret_adherente_gastos'        => '12.6',
                  'cobertura_fer_noct'          => '12.6',
                  'active'                      => 'active'
              )
        );

        $this->db->insert_batch('medical_insurance', $data);
    }


    public function down(){

        $this->dbforge->drop_table('medical_insurance');

    }


}
