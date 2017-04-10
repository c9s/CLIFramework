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
use PHPUnit\Framework\TestCase;


class CommandLoaderTest extends TestCase 
{
    public function test()
    {
        $command = new TestApp\Command\SimpleCommand(new TestApp\Application);
        $text = $command->getFormattedHelpText();

        // tODO: use string format assertion API to verify this
        $this->assertNotNull($text);

        $return = $command->execute(123);
        $this->assertNotNull( $return );
    }
}
