<?php

namespace bng\Controllers;
use bng\Controllers\BaseController;
use bng\Models\Agents;

class Main extends BaseController
{
    public function index()
    {

        $modelAgents = new Agents();
        $result = $modelAgents->getTotalAgents();

        printData($result);
        
        $data = [
            'nome' => 'Bob',
            'sobrenome' => 'Blue'
        ];

        $this->view('layouts/html_header');
        $this->view('home', $data);
        $this->view('layouts/html_footer');
    }
}
