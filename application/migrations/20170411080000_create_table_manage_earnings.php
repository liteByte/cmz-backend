<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_manage_earnings extends CI_Migration{

    public function up(){
        $this->dbforge->add_field(array(
            'id_manage_earnings' => array(
                'type'          => 'INT',
                'unsigned'      =>  TRUE,
                'auto_increment'=>  TRUE
            ),
            'since' => array(
                'type'          => 'DECIMAL',
                'constraint'    => '10,2',
                'null'          => FALSE,
            ),
            'until' => array(
                'type'          => 'DECIMAL',
                'constraint'    => '10,2',
                'null'          => FALSE,
            ),
            'fixed' => array(
                'type'          => 'DECIMAL',
                'constraint'    => '10,2',
                'null'          => FALSE,
            ),
            'percentage' => array(
                'type'          => 'DECIMAL',
                'constraint'    => '10,2',
                'null'          => FALSE,
            ),
            'minimun' => array(
                'type'          => 'DECIMAL',
                'constraint'    => '10,2',
                'null'          => FALSE,
            ),
            'impuni' => array(
                'type'          => 'DECIMAL',
                'constraint'    => '10,2',
                'null'          => FALSE,
            )
        ));

        $this->dbforge->add_key('id_manage_earnings', TRUE);
        $this->dbforge->create_table('manage_earnings');

        $data = array(
            array(
                'since'         => "10,4",
                'until'         => "20,2",
                'fixed'         => "10,2",
                'percentage'    => "10",
                'minimun'       => "5",
                'impuni'        => "10",
            ),
            array(
                'since'         => "15",
                'until'         => "20",
                'fixed'         => "25",
                'percentage'    => "5",
                'minimun'       => "2",
                'impuni'        => "10",
            ),
            array(
                'since'         => "25",
                'until'         => "15,5",
                'fixed'         => "10",
                'percentage'    => "12",
                'minimun'       => "23",
                'impuni'        => "10",
            ),
        );
        $this->db->insert_batch('manage_earnings', $data);
    }


    public function down(){
        $this->dbforge->drop_table('manage_earnings');
    }
}
