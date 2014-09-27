<?php
use CLIFramework\ArgumentEditor\ArgumentEditor;

class ArgumentEditorTest extends PHPUnit_Framework_TestCase
{
    public function testAppendAndRemove()
    {
        $editor = new ArgumentEditor(array('./configure','--enable-debug'));
        $editor->append('--enable-zip');
        is("./configure --enable-debug --enable-zip", $editor->__toString() );

        $editor->remove('--enable-zip');
        is("./configure --enable-debug", $editor->__toString() );
    }

    public function testRemoveRegExp() {
        $editor = new ArgumentEditor(array('./configure','--enable-debug'));
        $editor->append('--enable-zip');
        $editor->removeRegExp('--enable');
    }

    public function testEscape() {
        $editor = new ArgumentEditor(array('./configure','--enable-debug'));
        $editor->escape();
        is("'./configure' '--enable-debug'", $editor->__toString() );
    }
}

