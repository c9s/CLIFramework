<?php
namespace CLIFramework\Completion;

class Utils
{

    static public function qq($str) {
        return '"' . addcslashes($str , '"') . '"';
    }

    static public function q($str) {
        return "'" . addcslashes($str, "'") . "'";
    }

    static public function array_qq(array $array) {
        return array_map("CLIFramework\\Completion\\Utils::qq", $array);
    }

    static public function array_q(array $array) {
        return array_map("CLIFramework\\Completion\\Utils::q", $array);
    }

    static public function array_escape_space(array $array)
    {
        return array_map(function($a) { return addcslashes($a, ' '); }, $array);
    }

    static public function array_indent(array $lines, $level = 1) 
    {
        $space = str_repeat('  ', $level);
        return array_map(function($line) use ($space) {
            return $space . $line;
        }, $lines);
    }
}




