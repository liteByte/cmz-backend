<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_delete_all extends CI_Migration{

    public function up(){
        $this->dbforge->drop_table('users');
    }

    public function down(){
        $this->dbforge->drop_table('users');
    }

}
