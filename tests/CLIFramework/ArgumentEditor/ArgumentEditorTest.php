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

        $editor->append('--with-sqlite','--with-postgres');
        is("./configure --enable-debug --with-sqlite --with-postgres", $editor->__toString() );
    }

    public function testRemoveRegExp() {
        $editor = new ArgumentEditor(array('./configure','--enable-debug'));
        $editor->append('--enable-zip');
        $editor->removeRegExp('--enable');
    }

    public function testReplaceRegExp() {
        $editor = new ArgumentEditor(array('./configure','--enable-debug','--enable-zip'));
        $editor->replaceRegExp('--enable', '--with');
        is("./configure --with-debug --with-zip", $editor->__toString() );
    }

    public function testFilter() {
        $editor = new ArgumentEditor(array('./configure','--enable-debug','--enable-zip'));
        $editor->filter(function($arg) {
            return escapeshellarg($arg);
        });
        is("'./configure' '--enable-debug' '--enable-zip'", $editor->__toString() );
    }



    public function testReplace() {
        $editor = new ArgumentEditor(array('./configure','--enable-debug'));
        $old = $editor->replace('--enable-debug','--enable-foo');
        is('--enable-debug', $old);
        is("./configure --enable-foo", $editor->__toString() );
    }

    public function testEscape() {
        $editor = new ArgumentEditor(array('./configure','--enable-debug'));
        $editor->escape();
        is("'./configure' '--enable-debug'", $editor->__toString() );
    }
}

