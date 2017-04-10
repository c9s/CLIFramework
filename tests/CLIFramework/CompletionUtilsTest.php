<?php
use CLIFramework\CompletionUtils;
use PHPUnit\Framework\TestCase;


class CompletionUtilsTest extends TestCase
{
    public function testSplitWords()
    {
        $words = CompletionUtils::split_words("foo bar zoo");
        $this->assertCount(3, $words);
    }

    public function testPaths() {
        $paths = CompletionUtils::paths("src");
        $this->assertTrue(is_array($paths));
    }

    public function testClassnames() {
        $classes = CompletionUtils::classnames();
        $this->assertTrue( is_array($classes) );

        $classes = CompletionUtils::classnames('/CLI/');
        $this->assertTrue( is_array($classes) );
    }



}

