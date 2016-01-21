<?php
use CLIFramework\Component\Progress\ETACalculator;

class ETACalculatorTest extends PHPUnit_Framework_TestCase
{

    public function testCalculateRemainingSeconds()
    {
        $seconds = ETACalculator::calculateRemainingSeconds(10, 100, 0, 100);
        $this->assertTrue(is_double($seconds));
    }

}

