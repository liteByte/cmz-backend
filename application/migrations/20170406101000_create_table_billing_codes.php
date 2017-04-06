<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_billing_codes extends CI_Migration{

    public function up(){
        $this->dbforge->add_field(array(
            'billing_code_id' => array(
                'type'          =>  'INT',
                'constraint'    =>  5,
                'unsigned'      =>  TRUE,
                'auto_increment'=>  TRUE
            ),
            'description'   => array(
                'type'          =>  'VARCHAR',
                'constraint'    =>  50,
                'null'          =>  FALSE
            ),
            'value'   => array(
                'type'          =>  'INT',
                'constraint'    =>  2,
                'null'          =>  FALSE
            )
        ));
        $this->dbforge->add_key('billing_code_id', TRUE);
        $this->dbforge->create_table('billing_codes');

        $data = array(
            array(
                'description' => "1 – Solo Honorarios",
                'value'       => 1
            ),
            array(
                'description' => "2 – Solo Gastos",
                'value'       => 2
            ),
            array(
                'description' => "3 – Honorarios y Gastos",
                'value'       => 3
            )
        );

        $this->db->insert_batch('billing_codes', $data);
    }


    public function down(){
        $this->dbforge->drop_table('billing_codes');
    }




}
