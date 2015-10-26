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
use LazyRecord\BaseCollection;
use Exception;

class ConsoleDebug
{
    static public function dumpException(Exception $e)
    {
        $indicator = new LineIndicator;
        $output = [];
        $output[] = '[' . get_class($e) . '] was thrown with "' . $e->getMessage() . '".';
        $output[] = $indicator->indicateFile($e->getFile(), $e->getLine());

        $output[] = "Exception Stack Trace";
        $output[] = "=====================";
        $output[] = "";
        $output[] = $e->getTraceAsString();
        return join(PHP_EOL, $output);
    }


    /**
     * Dump Record Collection
     */
    static public function dumpCollection(BaseCollection $collection, array $options = array())
    {
        return self::dumpRows($collection->toArray(), $options);
    }


    static public function dumpRows(array $array, array $options = array())
    {
        $table = new Table;

        $keys = null;
        if (isset($options['keys'])) {
            $keys = $options['keys'];
        } else if (isset($array[0])) {
            $keys = array_keys($array[0]);
        }

        if ($keys) {
            $table->setHeaders($keys);
        }

        if (empty($array)) {
            return '0 rows.' . PHP_EOL;
        }

        foreach ($array as $item) {
            $values = [];
            foreach ($keys as $key) {
                $values[] = $item[$key];
            }
            $table->addRow($values);
        }
        return $table->render() . PHP_EOL
            . count($array) . ' rows.' . PHP_EOL;
    }
}



