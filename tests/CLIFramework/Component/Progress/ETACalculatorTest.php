<?php
use PHPUnit\Framework\TestCase;

use CLIFramework\Component\Progress\ETACalculator;

class ETACalculatorTest extends TestCase
{

    public function testCalculateRemainingSeconds()
    {
        $seconds = ETACalculator::calculateRemainingSeconds(10, 100, 0, 100);
        $this->assertTrue(is_double($seconds));
    }

}

