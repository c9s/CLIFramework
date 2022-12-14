<?php
use CLIFramework\ValueGroup;
use PHPUnit\Framework\TestCase;

class ValueGroupTest extends TestCase
{
    public function testValueGroup()
    {
        $group = new ValueGroup;
        ok($group);

        $group->add('aaa')
            ->add('bbb')
            ->add('bar')
            ->add('zoo');

        $keys = $group->keys();
        ok($keys);

        ok($group);
        is(4, $group->count());
    }
}

