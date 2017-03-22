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
                'constraint'    => 100,
                'null'          => FALSE
            ),
            'last_name' => array(
                'type'          => 'VARCHAR',
                'constraint'    => 100,
                'null'          => FALSE
            ),
            'document_type' => array(
                'type'          => 'VARCHAR',
                'constraint'    =>  20,
                'null'          => FALSE
            ),
            'document_number' => array(
                'type'          => 'VARCHAR',
                'constraint'    => 50,
                'unique'        => TRUE,
                'null'          => FALSE
            ),
            'date_birth' => array(
                'type'          => 'VARCHAR',
                'constraint'    => 50,
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
                'constraint'    => 10
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
                'constraint'    => 20,
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
                'null'          =>  FALSE
            ),
            'account_number' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  15,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'cbu_number' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  22,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
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

//        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_legal_locality)       REFERENCES locality(id_locality)');
//        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_office_locality)       REFERENCES locality(id_locality)');
        $this->dbforge->add_field("date_created  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_fiscal_data)          REFERENCES fiscal_data(id_fiscal_data)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (speciality_id)           REFERENCES specialities(speciality_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_category_femeba)      REFERENCES category_femeba(id_category_femeba)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_medical_career)       REFERENCES medical_career(id_medical_career)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_payment_type)         REFERENCES payment_type(id_payment_type)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (bank_id)                 REFERENCES banks(bank_id)');

        $this->dbforge->add_key('id_professional_data', TRUE);
        $this->dbforge->create_table('professionals');

        $data = array(
            array(
                'registration_number'  => '87451285',
                'name' => 'Jose',
                'last_name' => 'Ramirez',
                'document_type' => 'DNI',
                'document_number' => '61189982',
                'date_birth' => '20/02/1975',
                'legal_address' => 'valentin gomez 3355',
                'legal_locality' => 'ZARATE',
                'zip_code' => '4545',
                'phone_number' => '098672797',
                'email' => 'test@gmail.com',
                'office_address' => 'ayacucho 935',
                'office_locality' => 'ZARATE',
                'id_fiscal_data' => '1',
                'speciality_id' => '1',
                'type_partner' => 'SOCIO',
                'id_category_femeba' => '1',
                'id_medical_career' => '1',
                'id_payment_type' => '1',
                'bank_id' => '1',
                'active' => 'active'
            ),
            array(
                'registration_number'  => '96857412',
                'name' => 'Antonio',
                'last_name' => 'Ramirez',
                'document_type' => 'DNI',
                'document_number' => '6118998',
                'date_birth' => '20/02/1975',
                'legal_address' => 'valentin gomez 3355',
                'legal_locality' => 'ZARATE',
                'zip_code' => '4545',
                'phone_number' => '098672797',
                'email' => 'test@gmail.com',
                'office_address' => 'ayacucho 935',
                'office_locality' => 'ZARATE',
                'id_fiscal_data' => '2',
                'speciality_id' => '2',
                'type_partner' => 'NO SOCIO',
                'id_category_femeba' => '2',
                'id_medical_career' => '2',
                'id_payment_type' => '2',
                'bank_id' => '2',
                'active' => 'active'
            )
        );

        $this->db->insert_batch('professionals', $data);

    }

    public function down(){
        $this->dbforge->drop_table('professionals');        
    }


}