<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_bill_details_grouped extends  CI_Migration{

    public function up(){

        $this->dbforge->add_field([
            'id_group_details'   => [
                'type'           => 'INT',
                'constraint'     =>  10,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE
            ],
            'id_bill'   => [
                'type'           => 'INT',
                'constraint'     =>  10,
                'unsigned'       => TRUE,
                'null'           => FALSE,
            ],
            'billing_period'   => [
                'type'           => 'VARCHAR',
                'constraint'     =>  10,
                'null'           => FALSE,
            ],
            'total_honorary_period'   => [
                'type'           => 'DECIMAL',
                'constraint'     =>  10,2,
                'null'           => FALSE,
            ],
            'total_expenses_period'   => [
                'type'           => 'DECIMAL',
                'constraint'     =>  10,2,
                'null'           => FALSE,
            ],
            'total_honorary_plan'   => [
                'type'           => 'DECIMAL',
                'constraint'     =>  10,2,
                'null'           => FALSE,
            ],
            'total_expenses_plan'   => [
                'type'           => 'DECIMAL',
                'constraint'     =>  10,2,
                'null'           => FALSE,
            ],
        ]);

        $this->dbforge->add_field('CONSTRAINT FOREIGN KEY (id_bill)    REFERENCES bill(id_bill)');
        $this->dbforge->add_key('id_group_details', TRUE);
        $this->dbforge->create_table('bill_details_grouped');
    }

    public function down(){
        $this->dbforge->drop_table('bill_details_grouped');
    }

}