<?php
namespace CLIFramework;

class CompletionUtils
{
    public static function split_words($line) {
        return preg_split("#\s+#", $line);
    }
}



