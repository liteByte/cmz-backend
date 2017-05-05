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
        $this->dbforge->add_field('UNIQUE KEY coverage_key (since, until)');
        $this->dbforge->create_table('manage_earnings');

    }


    public function down(){
        $this->dbforge->drop_table('manage_earnings');
    }
}
