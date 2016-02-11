<?php

class Trext_Tree {
    public function __construct(Trext $master) {
        $this->master = $master;
    }

    public function get_list() {
        $_params = array();
        return $this->master->call('tree', $_params);
    }

}


