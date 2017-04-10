<?php
use CLIFramework\ArgInfo;
use CLIFramework\ArgInfoList;
use PHPUnit\Framework\TestCase;

class ArgInfoListTest extends TestCase
{
    public function test()
    {
        $arguments = new ArgInfoList;

        $a1 = $arguments->add('x');
        $this->assertInstanceOf('CLIFramework\ArgInfo', $a1);
        $this->assertEquals('x' , $a1->name );

        $a2 = $arguments->add('y');
        $this->assertInstanceOf('CLIFramework\ArgInfo', $a2);
        $this->assertEquals('y' , $a2->name );

        $this->assertNotNull( $arguments[0] );
        $this->assertNotNull( $arguments[1] );
    }
}

