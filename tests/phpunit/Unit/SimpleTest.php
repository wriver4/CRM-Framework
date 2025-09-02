<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SimpleTest extends TestCase
{
    public function testSimpleAssertion()
    {
        $this->assertTrue(true);
    }

    public function testBasicMath()
    {
        $this->assertEquals(4, 2 + 2);
    }
}