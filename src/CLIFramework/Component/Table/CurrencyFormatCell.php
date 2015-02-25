<?php
namespace CLIFramework\Component\Table;
use CLIFramework\Component\Table\CellAttribute;
use NumberFormatter;

class CurrencyFormatCell extends NumberFormatCell
{
    protected $currency;

    public function __construct($locale, $currency) {
        parent::__construct($locale);
        $this->currency = $currency;
    }

    public function format($cell) {
        return $this->formatter->formatCurrency($cell, $this->currency);
    }
}


