<?php
namespace CLIFramework\Component\Table;
use CLIFramework\Component\Table\CellAttribute;
use NumberFormatter;

class NumberFormatCell extends CellAttribute
{
    protected $locale;

    protected $formatter;

    public function __construct($locale) {
        $this->locale = $locale;
        $this->formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL); 
    }

    public function format($cell) {
        if (is_numeric($cell)) {
            return $this->formatter->format($cell);
        }
        return $cell;
    }
}


