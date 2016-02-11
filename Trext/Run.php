<?php

class Trext_Run {
    public function __construct(Trext $master) {
        $this->master = $master;
    }

    public function get_by_tree($treeId) {
        $_params = array(
            'treeId' => $treeId
        );
        return $this->master->call('run', $_params);
    }

}


