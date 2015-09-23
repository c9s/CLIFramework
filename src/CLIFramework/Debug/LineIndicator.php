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
        $fromLine = max($line - $this->contextLines, 0);
        $toLine = min($line + $this->contextLines, count($lines));

        if ($fromLine === $toLine) {
            $indexRange = [ $fromLine ];
        } else {
            $indexRange = range($fromLine, $toLine);
        }

        $output = [];
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





