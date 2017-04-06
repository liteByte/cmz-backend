<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_maternal_plan_options extends CI_Migration{

    public function up(){
        $this->dbforge->add_field(array(
            'maternal_plan_option_id' => array(
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
        $this->dbforge->add_key('maternal_plan_option_id', TRUE);
        $this->dbforge->create_table('maternal_plan_options');

        $data = array(
            array(
                'description' => "0 – No Aplica",
                'value'       => 0
            ),
            array(
                'description' => "1 – Aplica",
                'value'       => 1
            )
        );

        $this->db->insert_batch('maternal_plan_options', $data);
    }


    public function down(){
        $this->dbforge->drop_table('maternal_plan_options');
    }




}
