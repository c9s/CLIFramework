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

        $logger = $this->logger;
        $app = $this->getApplication();

        // if there is no subcommand to render help, show all available commands.
        $subcommands = func_get_args();
        $formatter = $this->getFormatter();
        if ($subcommands) {
            // TODO: recursively get the last subcommand.
            $subcommand = $subcommands[0];
            // get command object.
            $cmd = $app->getCommand( $subcommand );

            $usage = $cmd->usage();
            $optionLines = $cmd->optionSpecs->outputOptions();

            if ( $brief = $cmd->brief() ) {
                $logger->write($formatter->format('NAME', 'strong_white') . "\n");
                $logger->write("\t" . $formatter->format($subcommand, 'strong_white') . ' - ' . ucfirst($brief) . "\n\n");
            }


            $logger->write($formatter->format('SYNOPSIS', 'strong_white') . "\n");
            $logger->write("\t" . $progname . ' ' . $cmd->getName());

            if ( ! empty($cmd->getOptionCollection()->options) ) {
                $logger->write(" [options]");
            }
            if ($cmd->hasCommands() ) {
                $logger->write(" <command> ...");
            } else {
                $argInfos = $cmd->getArgumentsInfo();
                foreach( $argInfos as $argInfo ) {
                    $logger->write(" <" . $argInfo->name . ">");
                }
            }
            $logger->write("\n\n");

            if ( $usage = $cmd->usage() ) {
                $logger->write( $formatter->format('USAGE', 'strong_white') . "\n" );
                $logger->write( $usage );
                $logger->write( "\n\n" );
            }

            if ($optionLines) {
                $logger->write( $formatter->format('OPTIONS', 'strong_white') . "\n" );
                $logger->write( join("\n",$optionLines) );
                $logger->write( "\n" );
            }

            $logger->write($cmd->getFormattedHelpText());

        } else {
            // print application subcommands
            // print application brief
            $cmd = $this->parent;
            $logger->write( $formatter->format( ucfirst($cmd->brief()), "strong_white")."\n\n");

            $logger->write( $formatter->format("Synopsis", "strong_white")."\n" );
            $logger->write( "\t" . $progname );
            if ( ! empty($cmd->getOptionCollection()->options) ) {
                $logger->write(" [options]");
            }
            if ($cmd->hasCommands() ) {
                $logger->write(" <command>");
            } else {
                $argInfos = $cmd->getArgumentsInfo();
                foreach( $argInfos as $argInfo ) {
                    $logger->write(" <" . $argInfo->name . ">");
                }
            }
            $logger->write("\n\n");

            if( $usage = $cmd->usage() ) {
                $logger->write($formatter->format("USAGE", "strong_white") . "\n");
                $logger->write($usage);
                $logger->write("\n\n");
            }

            // print application options
            $logger->write($formatter->format("OPTIONS",'strong_white') . "\n");
            $cmd->optionSpecs->printOptions();
            $logger->write("\n\n");

            // get command list, Command classes should be preloaded.
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
            $logger->write($formatter->format("Commands\n",'strong_white'));
            foreach( $app->commands as $name => $class) {
                // skip subcommand with prefix underscore.
                if (preg_match('#^_#', $name)) {
                    continue;
                }

                $cmd = new $class;
                $brief = $cmd->brief();
                printf("%24s   %s\n",
                    $name,
                    $brief );
            }

            $logger->write("\n");
            $logger->write($this->getFormattedHelpText());
        }

        if ($app->showAppSignature) {
            $logger->write( $formatter->format("\n", 'gray') );
            $logger->write( $formatter->format("CLIFramework {$app->getVersion()}\thttps://github.com/c9s/CLIFramework\n", 'gray') );
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


