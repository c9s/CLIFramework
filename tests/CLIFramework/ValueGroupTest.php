<?php
use PHPUnit\Framework\TestCase;

use CLIFramework\ValueGroup;

class ValueGroupTest extends TestCase
{
    public function testValueGroup()
    {
        $group = new ValueGroup;
        $group->add('aaa')
            ->add('bbb')
            ->add('bar')
            ->add('zoo');

        $keys = $group->keys();
        $this->assertNotEmpty($keys);
        $this->assertCount(4, $group);
    }
}

