<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\BaseTestCase;

final class ExampleTest extends BaseTestCase
{
    public function testExample()
    {
        $this->getApp()->find('sc:list');

        $this->assertTrue(true);
    }
}