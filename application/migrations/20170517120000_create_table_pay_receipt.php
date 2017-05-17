<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_pay_receipt extends CI_Migration{

    public function up(){
        $this->dbforge->add_field([
            'pay_receipt_id'   => [
                'type'           => 'INT',
                'constraint'    =>  10,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE
            ],
            'pay_receipt_number'   => [
                'type'           => 'INT',
                'constraint'     => '10',
                'unsigned'       => TRUE,
                'null'           => FALSE
            ],
            'type_bill'   => [
                'type'           => 'BOOLEAN',
                'null'          =>  FALSE,
                'comment'       => '1 -> Obra | 0 -> Plan'
            ],
            'branch_office'   => [
                'type'           => 'INT',
                'constraint'     => '4',
                'null'           => FALSE,
            ],
            'type_document'   => [
                'type'           => 'VARCHAR',
                'constraint'     => '4',
                'null'           => FALSE,
            ],
            'type_form'   => [
                'type'           => 'VARCHAR',
                'constraint'     => '4',
                'null'           => FALSE,
            ],
            'pay_date'   => [
                'type'           => 'DATE',
                'null'           => FALSE,
            ],
            'date_created'   => [
                'type'           => 'DATE',
                'null'           => FALSE,
            ],
            'id_medical_insurance'   => [
                'type'           => 'INT',
                'unsigned'       =>  TRUE,
                'null'           =>  FALSE
            ],
            'id_bill'   => [
                'type'           => 'DECIMAL',
                'constraint'     => '20,2',
                'null'           =>  FALSE
            ],
            'amount_paid'   => [
                'type'           => 'DECIMAL',
                'constraint'     => '20,2',
                'null'           =>  FALSE
            ],
            'letter_amount_paid'   => [
                'type'           => 'VARCHAR',
                'constraint'     => '300',
                'null'           =>  FALSE
            ],
            'annulled'   => [
                'type'           => 'BOOLEAN',
                'null'           =>  TRUE,
            ],
        ]);

        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_medical_insurance)    REFERENCES medical_insurance(medical_insurance_id)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_bill)                 REFERENCES bill(id_bill)');
        $this->dbforge->add_key('pay_receipt_id', TRUE);
        $this->dbforge->add_field('UNIQUE KEY pay_receipt_number_key (pay_receipt_number,type_document,type_form,branch_office)');
        $this->dbforge->create_table('pay_receipt');

    }

    public function down(){
        $this->dbforge->drop_table('pay_receipt');
    }
}