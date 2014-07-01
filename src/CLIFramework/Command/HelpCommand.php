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
        return 'Show help message of a command';
    }

    /**
     * Show command help message
     *
     * @param string $subcommand command name
     */
    public function execute()
    {
        global $argv;

        $progname = $argv[0];

        // if there is no subcommand to render help, show all available commands.
        $subcommands = func_get_args();
        $formatter = $this->getFormatter();
        if ($subcommands) {
            // TODO: recursively get the last subcommand.
            $subcommand = $subcommands[0];
            // get command object.
            $cmd = $this->getApplication()->getCommand( $subcommand );

            $usage = $cmd->usage();
            $optionLines = $cmd->optionSpecs->outputOptions();

            if ( $brief = $cmd->brief() ) {
                echo $formatter->format(ucfirst($brief),'yellow'),"\n\n";
            }


            echo $formatter->format('Synopsis','yellow'),"\n";
            echo "\t" . $progname . ' ' . $cmd->getName();

            if ( ! empty($cmd->getOptionCollection()->options) ) {
                echo " [options]";
            }
            if ($cmd->hasCommands() ) {
                echo " <command> ...";
            } else {
                $argInfos = $cmd->getArgumentsInfo();
                foreach( $argInfos as $argInfo ) {
                    echo " <" . $argInfo->name . ">";
                }
            }
            echo "\n\n";

            if ( $usage = $cmd->usage() ) {
                echo $formatter->format('Usage','yellow'),"\n";
                echo $usage;
                echo "\n\n";
            }

            if ($optionLines) {
                echo $formatter->format('Options','yellow'),"\n";
                echo join("\n",$optionLines);
                echo "\n";
            }

            echo $cmd->getFormattedHelpText();

        } else {
            // print application subcommands
            // print application brief
            $cmd = $this->parent;
            echo $formatter->format( ucfirst($cmd->brief()) ,'yellow'),"\n\n";

            echo $formatter->format('Synopsis','yellow'),"\n";
            echo "\t" . $progname;
            if ( ! empty($cmd->getOptionCollection()->options) ) {
                echo " [options]";
            }
            if ($cmd->hasCommands() ) {
                echo " <command>";
            } else {
                $argInfos = $cmd->getArgumentsInfo();
                foreach( $argInfos as $argInfo ) {
                    echo " <" . $argInfo->name . ">";
                }
            }
            echo "\n\n";

            if( $usage = $cmd->usage() ) {
                echo $formatter->format("Usage",'yellow'),"\n";
                echo $usage;
                echo "\n\n";
            }

            // print application options
            echo $formatter->format("Options",'yellow'),"\n";
            $cmd->optionSpecs->printOptions();
            echo "\n\n";

            // get command list, command classes should be preloaded.
            $classes = get_declared_classes();
            $command_classes = array();
            foreach ($classes as $class) {
                if ( version_compare(phpversion(),'5.3.9') >= 0 ) {
                    if ( is_subclass_of($class,'CLIFramework\\Command',true) ) {
                        $command_classes[] = $class;
                    }
                } else {
                    if ( is_subclass_of($class,'CLIFramework\\Command') ) {
                        $command_classes[] = $class;
                    }
                }
            }

            // print command brief list
            echo $formatter->format("Commands\n",'yellow');
            foreach ($this->getApplication()->commands as $name => $class) {
                // skip subcommand with prefix underscore.
                if ( preg_match('#^_#', $name) ) {
                    continue;
                }


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


