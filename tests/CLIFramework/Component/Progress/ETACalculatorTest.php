<?php
use CLIFramework\Component\Progress\ETACalculator;

class ETACalculatorTest extends PHPUnit_Framework_TestCase
{

    public function testCalculate()
    {
        $calc = new ETACalculator;
        $calc->start();
        usleep(20 * 10000);
        $seconds = $calc->calculate(10, 100);
        $this->assertTrue(is_double($seconds));
        $datetime = new DateTime('@' . intval($seconds));

        echo $datetime->format(DateTime::ATOM);
    }

}

