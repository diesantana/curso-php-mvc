<?php

namespace bng\Controllers;

class Main
{
    public function index($id = '')
    {
        echo "Estou dentro do controlador Main - index";
        echo '<br>';
        teste();
        if(!empty($id)) echo "O id indicado foi $id";
    }
}
