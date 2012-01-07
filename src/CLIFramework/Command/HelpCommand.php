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


    /**
     * one line description
     */
    function brief()
    {
        return 'show help message.';
    }

    function execute($arguments)
    {
        $subcommand = array_shift($arguments);

        // if there is no subcommand to render help, show all available commands.
        if( $subcommand ) {
            // get command object.
            $cmd = $this->application->getCommand( $subcommand );
            $brief_line = $cmd->brief();
            $usage_line = $cmd->usage();
            $option_lines = $cmd->optionSpecs->outputOptions();


            if( $usage_line ) {
                echo "Usage:\n";
                echo "\t" . $usage_line;
                echo "\n";
            }

            if( $brief_line ) {
                echo "Brief:\n";
                echo "\t" . $brief_line;
                echo "\n";
            }

            if( $option_lines ) {
                echo "Options:\n";
                echo join("\n",$option_lines);
            }

            echo "\n";
            echo $cmd->help();


        } else {
            // print application subcommands

            // print application brief
            echo $this->parent->brief() . "\n\n";

            // print application options
            echo $this->formatter->format("Available options:\n",'info2');
            $this->parent->optionSpecs->printOptions();

            echo "\n\n";

            // get command list, command classes should be preloaded.
            $classes = get_declared_classes();
            $command_classes = array();
            foreach( $classes as $class ) {
                if( is_subclass_of($class,'CLIFramework\Command') ) 
                    $command_classes[] = $class;
            }

            // print command brief list
            echo $this->formatter->format("Available commands:\n",'info2');
            foreach( $command_classes as $class ) {
                $cmd = new $class;
                $brief = $cmd->brief();
                printf("     %-12s - %s\n", $cmd->getCommandName(), $brief );
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
