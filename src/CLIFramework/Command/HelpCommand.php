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
            $formatter = $this->getFormatter();

            $usage = $cmd->usage();
            $option_lines = $cmd->optionSpecs->outputOptions();

            if ( $brief = $cmd->brief() ) {
                echo $this->getFormatter()->format(ucfirst($brief),'yellow'),"\n\n";
            }

            if ( $usage = $cmd->usage() ) {
                echo $this->getFormatter()->format('Usage','yellow'),"\n";
                echo $usage, "\n";
            }

            if ($option_lines) {
                echo $this->getFormatter()->format('Options','yellow'),"\n";
                echo join("\n",$option_lines);
                echo "\n";
            }

            echo $cmd->getFormattedHelpText();

        } else {
            // print application subcommands

            // print application brief
            echo $this->getFormatter()->format( ucfirst($this->parent->brief()) ,'yellow'),"\n\n";

            if( $usage = $this->parent->usage() ) {
                echo $this->getFormatter()->format("Usage",'yellow'),"\n";
                echo "\t", basename($_SERVER['SCRIPT_FILENAME']) , ' ' , $usage , "\n\n";
            }

            // print application options
            echo $this->getFormatter()->format("Options",'yellow'),"\n";
            $this->parent->optionSpecs->printOptions();

            echo "\n\n";

            // get command list, command classes should be preloaded.
            $classes = get_declared_classes();
            $command_classes = array();
            foreach ($classes as $class) {
                if ( is_subclass_of($class,'CLIFramework\Command',true) ) {
                    $command_classes[] = $class;
                }
            }

            // print command brief list
            echo $this->getFormatter()->format("Commands\n",'yellow');
            foreach ($this->application->commands as $name => $class) {
                $cmd = new $class;
                $brief = $cmd->brief();
                printf("%24s   %s\n",
                    $name,
                    $brief );
            }

            echo "\n";
            echo $this->getFormattedHelpText();
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


