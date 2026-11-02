<?php

class Custoomer_Controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->require_authentication();
        $this->require_role('customer');
    }
}
