<?php
use CLIFramework\ValueGroup;

class ValueGroupTest extends PHPUnit_Framework_TestCase
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

