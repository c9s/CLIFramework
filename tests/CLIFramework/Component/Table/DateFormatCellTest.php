<?php
use CLIFramework\Component\Table\DateFormatCell;

class DateFormatCellTest extends PHPUnit_Framework_TestCase
{
    public function testDateFormat()
    {
        $dateFormatCell = new DateFormatCell('en_US', IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'America/Los_Angeles');
        $str = $dateFormatCell->format(0);

        // older icu does not output "at"
        $this->assertRegExp('/Wednesday, December \d+, \d+( at)? \d:00:00 PM Pacific Standard Time/', $str);
    }
}

