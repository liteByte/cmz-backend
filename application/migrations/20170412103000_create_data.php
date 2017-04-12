<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_table_fee_types extends CI_Migration{

    public function up(){

        $dump_file = dirname(__FILE__) . '\backup.sql';
        $sql = file_get_contents($dump_file);
        $sqls = explode(';', $sql);
        $test = array_pop($sqls);
        foreach($sqls as $statement){
            $statment = $statement . ";";
            $this->db->query($statment);
        }

    }


    public function down(){


    }









}
