<?php
/*
 * This file is part of the CLIFramework package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace CLIFramework\Command;
use CLIFramework\Command;
use CLIFramework\CommandInterface;

class HelpCommand extends Command
    implements CommandInterface
{

    function brief()
    {
        return 'show help message.';
    }

    function execute($arguments)
    {
        $subcommand = array_shift($arguments);

        // if there is no subcommand to render help, show all available commands.
        if( $subcommand ) {


        } else {
            // print application subcommands


            // print application brief
            echo $this->parent->brief() . "\n\n";

            // print application options
            $this->parent->optionSpecs->printOptions();

            // get command list, command classes should be preloaded.
            $classes = get_declared_classes();
            $command_classes = array();
            foreach( $classes as $class ) {
                if( is_subclass_of($class,'CLIFramework\Command') ) 
                    $command_classes[] = $class;
            }

            // print command brief list
            echo "* Available commands:\n";
            foreach( $command_classes as $class ) {
                $cmd = new $class($this->dispatcher);
                $brief = $cmd->brief();
                printf("  % 10s - %s\n", $cmd->getCommandName(), $brief );
            }
        }


        // if empty command list
        /*
        $file =  __FILE__ . '.md';
        if( file_exists( $file ) )
            echo file_get_contents( $file );
        */
        return true;
    }

}
