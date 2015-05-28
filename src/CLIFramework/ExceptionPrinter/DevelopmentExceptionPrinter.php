<?php
namespace CLIFramework\ExceptionPrinter;
use Exception;
use CLIFramework\ServiceContainer;
use CLIFramework\Logger;

function is_assoc_array(array $a) {
    return ! is_indexed_array($a);
}

function is_indexed_array(array $a) {
    $keys = array_keys($a);
    $indexes = array_filter($keys, 'is_numeric');
    return count($indexes) ? True : False;
}

function output_var($a) {
    if (is_array($a)) {
        if (is_indexed_array($a)) {
            $out = array();
            foreach ($a as $i) {
                $out[] = output_var($i);
            }
            return '[' . join(', ',$out) . ']';
        } else {

            $out = '[';
            foreach ($a as $k => $i) {
                $out .= $k . ' => ' . output_var($i);
            }
            $out .= ']';
            return $out;

        }
    } else if (is_scalar($a)) {

        return var_export($a, true);

    } else if (is_object($a)) {

        if (method_exists($a, '__toString')) {
            return $a->__toString();
        } else {
            return get_class($a);
        }

    } else {
        return '...';
    }
}

class DevelopmentExceptionPrinter
{
    public $reportUrl;

    public $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function dumpVar($var)
    {
        return output_var($var);
    }

    public function dumpArgs(array $args)
    {
        if (empty($args)) {
            return '';
        }

        $desc = array();
        foreach($args as $a) {
            $desc[] = output_var($a);
        }
        return join(', ', $desc);
    }

    public function dumpTraceInPhar(Exception $e)
    {
        $this->logger->info("Trace:\n");
        $trace = $e->getTrace();
        foreach($trace as $idx => $entry) {
            $argDesc = $this->dumpArgs($entry['args']);
            $this->logger->info(sprintf("    %d) %s%s%s(%s)", $idx, @$entry['class'], @$entry['type'], $entry['function'], $argDesc));
        }
        $this->logger->newline();
    }

    public function dumpTrace(Exception $e)
    {
        $this->logger->info("Trace:\n");
        $trace = $e->getTrace();
        foreach($trace as $idx => $entry) {

            $argDesc = $this->dumpArgs($entry['args']);

            $this->logger->info(sprintf("    %d) %s%s%s(%s)", $idx, @$entry['class'], @$entry['type'], $entry['function'], $argDesc));
            $this->logger->info(sprintf("        from %s: %d", $entry['file'], $entry['line']));
            $this->logger->newline();
        }
        $this->logger->newline();
    }

    public function dumpCodeBlock(Exception $e)
    {
        $line = $e->getLine();
        $file = $e->getFile();
        $this->logger->info("Thrown from $file at line $line:\n");

        $lines = file($file);
        $indexRange = range(max($line - 4, 0), min($line + 3, count($lines)));
        foreach($indexRange as $index) {
            if ($index == ($line - 1)) {
                $this->logger->warn(sprintf("> % 3d", $index + 1) . rtrim($lines[$index]));
            } else {
                $this->logger->info(sprintf("  % 3d", $index + 1) . rtrim($lines[$index]));
            }
        }

        $this->logger->newline();
    }

    public function dumpBrief(Exception $e)
    {
        $logger = $this->logger;
        $code = $e->getCode();
        $message = $e->getMessage();

        $file = $e->getFile();
        $line = $e->getLine();

        $class = get_class($e);

        if ($code) {
            $logger->error("$class: ($code) $message");
        } else {
            $logger->error("$class: $message");
        }
    }

    public function dump(Exception $e) 
    {
        $this->dumpBrief($e);
        $this->dumpCodeBlock($e);
        $this->dumpTrace($e);
    }
}




