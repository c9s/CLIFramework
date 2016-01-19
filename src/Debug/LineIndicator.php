<?php
namespace CLIFramework\Debug;

class LineIndicator
{
    protected $contextLines = 4;

    protected $indicatedLineFormat = "> % 4d| %s";
    
    protected $contextLineFormat = "  % 4d| %s";

    public function __construct()
    {

    }

    /**
     *
     * @param string $file
     * @param integer|integer[] $line
     */
    public function indicateFile($file, $line)
    {
        $lines = file($file);
        $fromIndex = max($line - 1 - $this->contextLines, 0);
        $toIndex = min($line - 1 + $this->contextLines, count($lines));

        if ($fromIndex === $toIndex) {
            $indexRange = [ $fromIndex ];
        } else {
            $indexRange = range($fromIndex, $toIndex);
        }

        $output = [];
        $output[] = "$file @ line " . join(',', (array) $line);
        $output[] = str_repeat('=', strlen($output[0]) );
        foreach($indexRange as $index) {
            if ((is_integer($line) && $index + 1 == $line) ||  (is_array($line) && in_array($index + 1, $line) ) ) {
                $output[] = sprintf($this->indicatedLineFormat, $index + 1, rtrim($lines[$index]));
            } else {
                $output[] = sprintf($this->contextLineFormat, $index + 1, rtrim($lines[$index]));
            }
        }
        return join(PHP_EOL, $output) . PHP_EOL;
    }
}





