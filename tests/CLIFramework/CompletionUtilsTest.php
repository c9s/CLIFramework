<?php
use CLIFramework\CompletionUtils;
use PHPUnit\Framework\TestCase;

class CompletionUtilsTest extends TestCase
{
    public function testSplitWords()
    {
        $words = CompletionUtils::split_words("foo bar zoo");
        ok($words);
        count_ok(3, $words);
    }

    public function testPaths() {
        $paths = CompletionUtils::paths("src");
        ok($paths);
        ok(is_array($paths));
    }

    public function testClassnames() {
        $classes = CompletionUtils::classnames();
        ok( is_array($classes) );

        $classes = CompletionUtils::classnames('/CLI/');
        ok( is_array($classes) );
    }



}

