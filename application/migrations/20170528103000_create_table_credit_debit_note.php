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
            'document_type' => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  1,
                'null'          =>  FALSE,
                'comments'      => 'C-> nota credito y D-> nota debito'
            ),
            'creation_date'  => array(
                'type'          => 'DATE',
                'null'          => FALSE
            ),
            'credit_debit_note_number'   => array(
                'type'           => 'INT',
                'constraint'     => 8,
                'unsigned'       => TRUE,
                'null'           => FALSE
            ),
            'annulled'   => array(
                'type'           => 'BOOLEAN',
                'null'           =>  TRUE,
            )
        ));

        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_bill)                             REFERENCES bill(id_bill)');
        $this->dbforge->add_key('credit_debit_note_id', TRUE);
        $this->dbforge->create_table('credit_debit_note');

    }


    public function down(){

        $this->dbforge->drop_table('credit_debit_note');

    }









}
