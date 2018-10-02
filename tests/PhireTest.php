<?php

namespace Phire\Test;

use PHPUnit\Framework\TestCase;

class PhireTest extends TestCase
{

    public function testConstructor()
    {
        $phire = new \Phire\Module();
        $this->assertInstanceOf('Phire\Module', $phire);
    }

}