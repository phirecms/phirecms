<?php

namespace Phire\Controller\Phire;

use Pop\Controller\Controller;

class IndexController extends Controller
{

    public function index()
    {
        echo 'Hello Phire!';
    }

    public function login()
    {
        echo 'Login to Phire.';
    }

    public function logout()
    {
        echo 'Logout of Phire.';
    }

    public function error()
    {
        echo 'Whoops!';
    }

}