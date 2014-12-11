<?php

namespace Phire\Controller;

use Pop\Controller\Controller;

class IndexController extends Controller
{

    public function index()
    {
        echo 'Hello Phire!';
    }

    public function error()
    {
        echo 'Whoops!';
    }
}