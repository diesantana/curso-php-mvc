<?php

namespace bng\Controllers;
use bng\Controllers\BaseController;

class Main extends BaseController
{
    public function index()
    {
        $data = [
            'nome' => 'Bob',
            'sobrenome' => 'Blue'
        ];

        $this->view('layouts/html_header');
        $this->view('home', $data);
        $this->view('layouts/html_footer');
    }
}
