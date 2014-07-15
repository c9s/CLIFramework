<?php
use CLIFramework\ValueGroupCollection;

class ValueGroupCollectionTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $groups = new ValueGroupCollection;
        $groups->add('extension-commands', [ 'install', 'enable', 'disable' ]);
        $groups->add('version-related', [ 'use', 'switch', 'off' ]);
        $json = $groups->toJson();

        $values = $groups->get('extension-commands');
        ok($json);
    }
}

