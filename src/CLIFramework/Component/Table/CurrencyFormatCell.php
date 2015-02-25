<?php
namespace CLIFramework\Component\Table;
use CLIFramework\Component\Table\CellAttribute;
use NumberFormatter;

class CurrencyFormatCell extends NumberFormatCell
{
    public function __construct($locale) {
        $this->locale = $locale;
        $this->formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY); 
    }
}


