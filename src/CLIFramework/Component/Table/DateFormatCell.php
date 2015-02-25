<?php
namespace CLIFramework\Component\Table;
use CLIFramework\Component\Table\CellAttribute;
use NumberFormatter;
use IntlDateFormatter;

class DateFormatCell extends NumberFormatCell
{
    public function __construct($locale, $datetype = IntlDateFormatter::FULL, $timetype = IntlDateFormatter::FULL, $timezone = NULL, $calendar = IntlDateFormatter::GREGORIAN, $pattern = "")
    {
        $this->locale = $locale;
        $this->formatter = new IntlDateFormatter($locale , $datetype, $timetype, $timezone, $calendar);
    }

    public function format($cell) {
        return $this->formatter->format($cell);
    }
}


