<?php
use CLIFramework\Component\Table\DateFormatCell;

class DateFormatCellTest extends PHPUnit_Framework_TestCase
{
    public function testDateFormat()
    {
        $dateFormatCell = new DateFormatCell('en_US', IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'America/Los_Angeles');
        $str = $dateFormatCell->format(0);
        $this->assertStringMatchesFormat('Wednesday, December %d, %d at 4:00:00 PM Pacific Standard Time', $str);
    }
}

