<?php
use CLIFramework\ArgumentEditor\ArgumentEditor;

class ArgumentEditorTest extends PHPUnit_Framework_TestCase
{
    public function testArgumentEditor()
    {
        $editor = new ArgumentEditor(array('./configure','--enable-debug'));
        $editor->append('--enable-zip');
        is("./configure --enable-debug --enable-zip", $editor->__toString() );

        $editor->remove('--enable-zip');
        is("./configure --enable-debug", $editor->__toString() );

        $editor->escape();

        is("'./configure' '--enable-debug'", $editor->__toString() );


        
    }
}

