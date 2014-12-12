<?php

namespace Phire\Controller\Phire;

use Phire\Controller\AbstractController;

class IndexController extends AbstractController
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