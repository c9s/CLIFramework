<?php
use CLIFramework\ValueGroupCollection;

class ValueGroupCollectionTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $groups = new ValueGroupCollection;
        $groups->add('extension-commands', array( 'install', 'enable', 'disable' ));
        $groups->add('version-related', array('use', 'switch', 'off' ));

        $values = $groups->get('extension-commands');
        ok($values);
        ok(is_array($values));

        $json = $groups->toJson();
        ok($json);
    }
}

