<?php
use PHPUnit\Framework\TestCase;
use CLIFramework\ValueCollection;

class ValueCollectionTest extends TestCase
{
    public function test()
    {
        $groups = new ValueCollection;
        $groups->group('extension-commands', 'Extension Commands', array( 'install', 'enable', 'disable' ));
        $groups->group('version-related', 'Version Related Commands', array('use', 'switch', 'off' ));

        foreach( $groups as $groupId => $values) {
            $this->assertNotNull($values);
        }

        $values = $groups->getGroup('extension-commands');
        $this->assertNotEmpty($values);
        $this->assertTrue(is_array($values));

        $this->assertTrue($groups->containsValue('disable'));

        $this->assertFalse($groups->containsValue('foobar'));
        $json = $groups->toJson();
    }
}

