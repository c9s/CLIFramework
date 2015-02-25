<?php
namespace CLIFramework\Component\Table;
use CLIFramework\Component\Table\CellAttribute;
use NumberFormatter;

class CurrencyFormatCell extends NumberFormatCell
{
    protected $currency;

    public function __construct($locale, $currency) {
        $this->locale = $locale;
        $this->currency = $currency;
        $this->formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY); 
    }

    public function format($cell) {
        return $this->formatter->formatCurrency($cell, $this->currency);
    }
}


