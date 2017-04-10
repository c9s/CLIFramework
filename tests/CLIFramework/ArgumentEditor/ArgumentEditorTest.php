<?php
use CLIFramework\ArgumentEditor\ArgumentEditor;
use PHPUnit\Framework\TestCase;

class ArgumentEditorTest extends TestCase
{
    public function testAppendAndRemove()
    {
        $editor = new ArgumentEditor(array('./configure','--enable-debug'));
        $editor->append('--enable-zip');
        $this->assertEquals("./configure --enable-debug --enable-zip", $editor->__toString() );


        $editor->remove('--enable-zip');
        $this->assertEquals("./configure --enable-debug", $editor->__toString() );

        $editor->append('--with-sqlite','--with-postgres');
        $this->assertEquals("./configure --enable-debug --with-sqlite --with-postgres", $editor->__toString() );
    }

    public function testRemoveRegExp() {
        $editor = new ArgumentEditor(array('./configure','--enable-debug'));
        $editor->append('--enable-zip');
        $editor->removeRegExp('--enable');
    }

    public function testReplaceRegExp() {
        $editor = new ArgumentEditor(array('./configure','--enable-debug','--enable-zip'));
        $editor->replaceRegExp('--enable', '--with');
        $this->assertEquals("./configure --with-debug --with-zip", $editor->__toString() );
    }

    public function testFilter() {
        $editor = new ArgumentEditor(array('./configure','--enable-debug','--enable-zip'));
        $editor->filter(function($arg) {
            return escapeshellarg($arg);
        });
        $this->assertEquals("'./configure' '--enable-debug' '--enable-zip'", $editor->__toString() );
    }



    public function testReplace() {
        $editor = new ArgumentEditor(array('./configure','--enable-debug'));
        $old = $editor->replace('--enable-debug','--enable-foo');
        $this->assertEquals('--enable-debug', $old);
        $this->assertEquals("./configure --enable-foo", $editor->__toString() );
    }

    public function testEscape() {
        $editor = new ArgumentEditor(array('./configure','--enable-debug'));
        $editor->escape();
        $this->assertEquals("'./configure' '--enable-debug'", $editor->__toString() );
    }
}

