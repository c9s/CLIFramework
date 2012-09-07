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
    public function brief()
    {
        return 'show help message of a command';
    }

    /**
     * Show command help message
     *
     * @param string $subcommand command name
     */
    public function execute()
    {
        // if there is no subcommand to render help, show all available commands.
        $subcommands = func_get_args();
        if ($subcommands) {
            $subcommand = array_shift($subcommands);
            // get command object.
            $cmd = $this->application->getCommand( $subcommand );
            $formatter = $this->application->getFormatter();
            $usage = $cmd->usage();
            $option_lines = $cmd->optionSpecs->outputOptions();

            if ( $brief = $cmd->brief() ) {
                echo $brief, "\n";
            }

            if ( $usage = $cmd->usage() ) {
                echo "Usage:\n";
                echo $usage, "\n";
            }

            if ($option_lines) {
                echo "Options:\n";
                echo join("\n",$option_lines);
                echo "\n";
            }

            echo $cmd->getFormattedHelpText();

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
            foreach ($classes as $class) {
                if ( is_subclass_of($class,'CLIFramework\Command') ) {
                    $command_classes[] = $class;
                }
            }

            // print command brief list
            echo $this->formatter->format("Available commands:\n",'info2');
            foreach ($this->application->commands as $name => $class) {
                $cmd = new $class;
                $brief = $cmd->brief();
                printf("     %-12s - %s\n",
                    $name,
                    $brief );
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
