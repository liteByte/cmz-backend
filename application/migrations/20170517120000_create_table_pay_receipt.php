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
                'constraint'     => '8',
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
            'date_due'   => [
                'type'           => 'DATE',
                'null'           => FALSE,
            ],
            'id_medical_insurance'   => [
                'type'           => 'INT',
                'unsigned'       =>  TRUE,
                'null'           =>  FALSE
            ],
            'total'   => [
                'type'           => 'DECIMAL',
                'constraint'     => '20,2',
                'null'           =>  FALSE
            ],
            'state_billing'   => [
                'type'           => 'INT',
                'constraint'     =>  1,
                'null'           =>  FALSE,
                'comment'        => '1 -> Cargada, 2-> Cobrada parcial, 3->Cobrada y 4 -> Facturada'
            ],
            'amount_paid'   => [
                'type'           => 'DECIMAL',
                'constraint'     => '20,2',
                'null'           =>  TRUE,
            ],
            'annulled'   => [
                'type'           => 'BOOLEAN',
                'null'           =>  TRUE,
            ],
        ]);

        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_medical_insurance)    REFERENCES medical_insurance(medical_insurance_id)');
        $this->dbforge->add_key('id_bill', TRUE);
        $this->dbforge->add_field('UNIQUE KEY bill_number_key (number_bill,type_document,type_form,branch_office)');
        $this->dbforge->create_table('bill');

    }

    public function down(){
        $this->dbforge->drop_table('bill');
    }
}