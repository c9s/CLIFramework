<?php
namespace CLIFramework;

class CompletionUtils
{
    public static function split_words($line) {
        return preg_split("#\s+#", trim($line));
    }

    public static function paths($dir) {
        $names = scandir($dir);
        return array_filter($names, function($n) {
            return $n != '.' && $n != '..';
        });
    }

    public static function classnames($pattern = null) {
        $classes = get_declared_classes();
        if ($pattern) {
            return array_filter($classes, function($class) use ($pattern) {
                return preg_match($pattern, $class);
            });
        }
        return $classes;
    }

}


