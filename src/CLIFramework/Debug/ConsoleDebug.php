<?php
namespace CLIFramework\Debug;
use CLIFramework\Component\Table\Table;
use CLIFramework\Component\Table\TableStyle;
use CLIFramework\Component\Table\CompactTableStyle;
use CLIFramework\Component\Table\MarkdownTableStyle;
use CLIFramework\Component\Table\CellAttribute;
use CLIFramework\Component\Table\NumberFormatCell;
use CLIFramework\Component\Table\CurrencyCellAttribute;
use CLIFramework\Component\Table\SpellOutNumberFormatCell;
use CLIFramework\Component\Table\RowSeparator;

class ConsoleDebug
{
    static public function dumpArray(array $array)
    {
        $table = new Table;
        if (isset($array[0])) {
            $table->setHeaders(array_keys($array[0]));
        }

        foreach ($array as $item) {
            $values = array_values($item);
            $table->addRow($values);
        }
        echo $table->render(), PHP_EOL;
    }
}



