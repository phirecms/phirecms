<?php

namespace Phire\Controller;

class IndexController extends AbstractController
{

    public function index()
    {
        echo 'Hello Phire!';
        print_r($this);
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