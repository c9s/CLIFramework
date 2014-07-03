<?php
use CLIFramework\ArgumentInfo;
use CLIFramework\ArgumentInfoList;

class ArgumentInfoListTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $arguments = new ArgumentInfoList;
        ok($arguments);

        $a1 = $arguments->add('x');
        ok( $a1 instanceof ArgumentInfo ); 
        is('x' , $a1->name );

        $a2 = $arguments->add('y');
        ok( $a2 instanceof ArgumentInfo ); 
        is('y' , $a2->name );

        ok( $arguments[0] );
        ok( $arguments[1] );
    }
}

