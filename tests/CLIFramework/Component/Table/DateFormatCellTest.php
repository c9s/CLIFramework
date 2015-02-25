<?php
use CLIFramework\Component\Table\DateFormatCell;

class DateFormatCellTest extends PHPUnit_Framework_TestCase
{
    public function testDateFormat()
    {
        $dateFormatCell = new DateFormatCell('en_US');
        $str = $dateFormatCell->format(0);
        $this->assertEquals('Thursday, January 1, 1970 at 8:00:00 AM Taipei Standard Time', $str);
    }
}

