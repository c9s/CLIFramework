<?php
namespace CLIFramework\Completion;

class Utils
{
    public static function qq($str)
    {
        return '"' . addcslashes($str, '"') . '"';
    }

    public static function q($str)
    {
        return "'" . addcslashes($str, "'") . "'";
    }

    public static function array_qq(array $array)
    {
        return array_map("CLIFramework\\Completion\\Utils::qq", $array);
    }

    public static function array_q(array $array)
    {
        return array_map("CLIFramework\\Completion\\Utils::q", $array);
    }

    public static function array_escape_space(array $array)
    {
        return array_map(function ($a) {
            return addcslashes($a, ' ');
        }, $array);
    }

    public static function array_indent(array $lines, $level = 1)
    {
        $space = str_repeat('  ', $level);
        return array_map(function ($line) use ($space) {
            return $space . $line;
        }, $lines);
    }
}
