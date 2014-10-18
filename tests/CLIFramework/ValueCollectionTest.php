<?php
use CLIFramework\ValueCollection;

class ValueCollectionTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $groups = new ValueCollection;
        $groups->group('extension-commands', 'Extension Commands', array( 'install', 'enable', 'disable' ));
        $groups->group('version-related', 'Version Related Commands', array('use', 'switch', 'off' ));

        foreach( $groups as $groupId => $values) {
            ok($values);
        }

        $values = $groups->getGroup('extension-commands');
        ok($values);
        ok(is_array($values));

        ok($groups->containsValue('disable'));

        ok(! $groups->containsValue('foobar'));

        $json = $groups->toJson();
        ok($json);
    }
}

