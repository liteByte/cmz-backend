<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_credit_debit_note extends CI_Migration{

    public function up(){

        $this->dbforge->add_field(array(
            'credit_debit_note_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'auto_increment'=>  TRUE
            ),
            'id_bill' => array(
                'type'          =>  'INT',
                'constraint'    =>  10,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'medical_insurance_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'null'          =>  FALSE
            ),
            'document_type' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  1,
                'null'          =>  FALSE,
                'comments'      => 'C-> nota credito y D-> nota debito'
            ),
            'branch_office'   => array(
                'type'           => 'INT',
                'constraint'     => '4',
                'null'           => FALSE
            ),
            'type_form'   => array(
                'type'           => 'VARCHAR',
                'constraint'     => '4',
                'null'           => FALSE
            ),
            'creation_date'  => array(
                'type'          => 'DATE',
                'null'          => FALSE
            ),
            'expiration_date'  => array(
                'type'          => 'DATE',
                'null'          => FALSE
            ),
            'credit_debit_note_number'   => array(
                'type'           => 'INT',
                'constraint'     => 8,
                'unsigned'       => TRUE,
                'null'           => FALSE
            ),
            'state'   => array(
                'type'           => 'INT',
                'constraint'     =>  1,
                'null'           =>  FALSE,
                'comment'        => '1 -> Generada/Cargada , 2-> Pendiente de liquidacion, 3->Liquidada'
            ),
            'total_expenses' => array(
                'type'          =>  'DECIMAL',
                'constraint'    =>  '8,2',
                'null'          =>  FALSE
            ),
            'total_honoraries' => array(
                'type'          =>  'DECIMAL',
                'constraint'    =>  '20,2',
                'null'          =>  FALSE
            ),
            'total_note' => array(
                'type'          =>  'DECIMAL',
                'constraint'    =>  '20,2',
                'null'          =>  FALSE
            ),
            'annulled'   => array(
                'type'           => 'BOOLEAN',
                'null'           =>  TRUE,
            ),
            'pay_receipt_id'   => array(
                'type'           => 'INT',
                'constraint'     =>  10,
                'unsigned'       => TRUE,
                'null'           => TRUE,
                'default'        => null
            ),
        ));

        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_bill) REFERENCES bill(id_bill)');
        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (pay_receipt_id) REFERENCES pay_receipt(pay_receipt_id)');
        $this->dbforge->add_key('credit_debit_note_id', TRUE);
        $this->dbforge->create_table('credit_debit_note');

    }


    public function down(){

        $this->dbforge->drop_table('credit_debit_note');

    }









}
