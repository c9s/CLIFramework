<?php
/*
 * This file is part of the {{ }} package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

class CommandLoaderTest extends PHPUnit_Framework_TestCase 
{
    function test()
    {
        $command = new TestApp\Command\SimpleCommand;
        ok( $command );

        $text = $command->getFormattedHelpText();
        ok( $text );

        // echo $text;
        
        $return = $command->execute(123);
        ok( $return );
    }
}
