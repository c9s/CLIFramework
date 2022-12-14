<?php
use CLIFramework\ArgInfo;
use CLIFramework\ArgInfoList;
use PHPUnit\Framework\TestCase;

class ArgInfoListTest extends TestCase
{
    public function test()
    {
        $arguments = new ArgInfoList;
        ok($arguments);

        $a1 = $arguments->add('x');
        ok( $a1 instanceof ArgInfo ); 
        is('x' , $a1->name );

        $a2 = $arguments->add('y');
        ok( $a2 instanceof ArgInfo ); 
        is('y' , $a2->name );

        ok( $arguments[0] );
        ok( $arguments[1] );
    }
}

