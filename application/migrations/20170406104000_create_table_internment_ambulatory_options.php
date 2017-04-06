<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_internment_ambulatory_options extends CI_Migration{

    public function up(){
        $this->dbforge->add_field(array(
            'internment_ambulatory_option_id' => array(
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
        $this->dbforge->add_key('internment_ambulatory_option_id', TRUE);
        $this->dbforge->create_table('internment_ambulatory_options');

        $data = array(
            array(
                'description' => "0 – H y G Ambulatorio",
                'value'       => 0
            ),
            array(
                'description' => "1 – H y G Internación",
                'value'       => 1
            )
        );

        $this->db->insert_batch('internment_ambulatory_options', $data);
    }


    public function down(){
        $this->dbforge->drop_table('internment_ambulatory_options');
    }




}
