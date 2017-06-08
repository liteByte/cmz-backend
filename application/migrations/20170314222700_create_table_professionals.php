<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_professionals extends CI_Migration{


    public function up(){

        $this->dbforge->add_field(array(
            'id_professional_data' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'auto_increment'=>  TRUE
            ),
            'registration_number'   => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  8,
                'unique'        =>  TRUE,
                'null'          => FALSE
            ),
            'name' => array(
                'type'          => 'VARCHAR',
                'constraint'    => 50,
                'null'          => FALSE
            ),
            'document_type' => array(
                'type'          => 'VARCHAR',
                'constraint'    =>  10,
                'null'          => FALSE
            ),
            'document_number' => array(
                'type'          => 'VARCHAR',
                'constraint'    => 9,
                'null'          => FALSE
            ),
            'date_birth' => array(
                'type'          => 'DATE',
                'unique'        => FALSE,
                'null'          => FALSE
            ),
            'legal_address' => array(
                'type'          => 'VARCHAR',
                'constraint'    => 100,
                'null'          => FALSE
            ),
            'legal_locality' => array(
                'type'          => 'VARCHAR',
                'constraint'    => 50
            ),
            'zip_code' => array(
                'type'          => 'VARCHAR',
                'constraint'    => 15
            ),
            'phone_number' => array(
                'type'          => 'VARCHAR',
                'constraint'    => 50
            ),
            'email' => array(
                'type'          => 'VARCHAR',
                'constraint'    => 100,
                'null'          => FALSE
            ),
            'office_address' => array(
                'type'          => 'VARCHAR',
                'constraint'    => 100,
                'null'          => FALSE
            ),
            'office_locality' => array(
                'type'          => 'VARCHAR',
                'constraint'    => 50
            ),
            'id_fiscal_data' => array(
                'type'          => 'INT',
                'constraint'    => 5,
                'unsigned'      => TRUE,
                'null'          => FALSE
            ),
            'speciality_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'type_partner' => array(
                'type'          => 'VARCHAR',
                'constraint'    => 10,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'id_category_femeba' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'id_medical_career' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'id_payment_type' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'bank_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  TRUE,
                'default'       => NULL
            ),
            'account_number' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  15,
                'unsigned'      =>  TRUE,
                'null'          =>  TRUE
            ),
            'cbu_number' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  22,
                'unsigned'      =>  TRUE,
                'null'          =>  TRUE
            ),
            'active'        => array(
                'type'          => 'VARCHAR',
                'constraint'    =>  150,
                'null'          => FALSE
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
            ),
        ));

        $this->dbforge->add_field("date_created  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_fiscal_data)          REFERENCES fiscal_data(id_fiscal_data)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (speciality_id)           REFERENCES specialities(speciality_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_category_femeba)      REFERENCES category_femeba(id_category_femeba)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_medical_career)       REFERENCES medical_career(id_medical_career)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_payment_type)         REFERENCES payment_type(id_payment_type)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (bank_id)                 REFERENCES banks(bank_id)');

        $this->dbforge->add_key('id_professional_data', TRUE);
        $this->dbforge->create_table('professionals');


    }

    public function down(){
        $this->dbforge->drop_table('professionals');        
    }


}