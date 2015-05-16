<?php
/*
 * This file is part of the CLIFramework package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace tests\CLIFramework\Hook;

use CLIFramework\Hook\HookHolder;

class HookHolderTest extends \PHPUnit_Framework_TestCase 
{
    private $holder;

    function setUp()
    {
        $this->holder = new HookHolder();
    }

    function testAddHook()
    {
        $this->assertCount(0, $this->holder->getHookPoints());

        $this->holder->addHook('test.before', function() {});
        $points = $this->holder->getHookPoints();

        $this->assertCount(1, $points);
        $this->assertSame('test.before', $points[0]);
    }

    function testAddHookByArray()
    {
        $this->assertCount(0, $this->holder->getHookPoints());

        $this->holder->addHookByArray(array(
            'name' => 'test.after',
            'callback' => function() {}
        ));
        $points = $this->holder->getHookPoints();

        $this->assertCount(1, $points);
        $this->assertSame('test.after', $points[0]);
    }

    function testCallHook()
    {
        $tester = $this;
        $isSuccess = false;

        $this->holder->addHook('test.success', function() use (&$isSuccess) {
            $isSuccess = true;
        });
        $this->holder->addHook('test.fail', function() use ($tester) {
            $tester->fail();
        });
        $this->holder->callHook('test.success');

        $this->assertTrue($isSuccess);
    }
}
