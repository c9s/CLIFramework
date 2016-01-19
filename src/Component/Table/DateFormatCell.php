<?php
namespace CLIFramework\Component\Table;
use CLIFramework\Component\Table\CellAttribute;
use NumberFormatter;
use IntlDateFormatter;
use DateTime;

class DateFormatCell extends NumberFormatCell
{

    /**
     * IntlDateFormatter::FULL (integer)
        Completely specified style (Tuesday, April 12, 1952 AD or 3:30:42pm PST)
     * IntlDateFormatter::LONG (integer)
        Long style (January 12, 1952 or 3:30:32pm)
     * IntlDateFormatter::MEDIUM (integer)
        Medium style (Jan 12, 1952)
     * IntlDateFormatter::SHORT (integer)
        Most abbreviated style, only essential data (12/13/52 or 3:30pm)
     */
    public function __construct($locale, $datetype = IntlDateFormatter::FULL, $timetype = IntlDateFormatter::FULL, $timezone = NULL, $calendar = IntlDateFormatter::GREGORIAN, $pattern = "")
    {
        $this->locale = $locale;
        $this->formatter = new IntlDateFormatter($locale , $datetype, $timetype, $timezone, $calendar);
    }

    public function format($cell) {
        if ($cell instanceof DateTime) {
            return $this->formatter->formatObject($cell);
        }
        return $this->formatter->format($cell);
    }
}


