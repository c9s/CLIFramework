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
use CLIFramework\OptionPrinter;
use CLIFramework\Corrector;


class HelpCommand extends Command implements CommandInterface {

    /**
     * one line description
     */
    public function brief()
    {
        return 'Show help message of a command';
    }

    public function options($opts)
    {
        $opts->add('dev','Show development commands');
    }

    public function displayTopic($topic) {
        $this->logger->write($this->formatter->format('TOPIC', 'strong_white') . "\n");
        $this->logger->write("\t" . $topic->getTitle() . "\n\n");
        $this->logger->write($this->formatter->format('DESCRIPTION', 'strong_white') . "\n");
        $this->logger->write($topic->getContent() . "\n\n");

        if ($footer = $topic->getFooter()) {
            $this->logger->write($this->formatter->format('MORE', 'strong_white') . "\n");
            $this->logger->write($footer . "\n");
        }
    }

    public function calculateColumnWidth($words, $min = 0) {
        $maxWidth = $min;
        foreach($words as $word) {
            if (strlen($word) > $maxWidth) {
                $maxWidth = strlen($word);
            }
        }
        return $maxWidth;
    }

    public function layoutCommands($commands, $indent = 4) {
        $cmdNames = array_filter(array_keys($commands), function($n) {
            return ! preg_match('#^_#', $n);
        });
        $maxWidth = $this->calculateColumnWidth($cmdNames, 12);
        foreach ($commands as $name => $class) {
            $cmd = new $class;
            $brief = $cmd->brief();
            $this->logger->writeln(str_repeat(' ' , $indent) 
                . sprintf("%" . ($maxWidth + $indent) . "s    %s",
                    $name,
                    $brief 
                ));
        }
        $this->logger->newline();
    }

    /**
     * Show command help message
     *
     * @param string $subcommand command name
     */
    public function execute()
    {
        $logger = $this->logger;
        $app = $this->getApplication();
        $progname = basename($app->getProgramName());

        $printer = new OptionPrinter;
        $formatter = $this->getFormatter();

        // if there is no subcommand to render help, show all available commands.
        $commandNames = func_get_args();

        if (count($commandNames) == 1) {
            // Check topic
            if ($topic = $app->getTopic($commandNames[0])) {
                return $this->displayTopic($topic);
            } elseif(!$app->hasCommand($commandNames[0])) {
                $corrector = new Corrector(array_keys($app->topics));
                if ($match = $corrector->correct($commandNames[0])) {
                    return $this->displayTopic($app->topics[$match]);
                }
                return;
            }
        }

        if (count($commandNames)) {
            $subcommand = $commandNames[0];
            $cmd = $app;
            for ($i = 0; $cmd && $i < count($commandNames) ; $i++ ) {
                $cmd = $cmd->getCommand($commandNames[$i]);
            }
            if (!$cmd) {
                throw new Exception("Command entry " . join(' ', $commandNames) . " not found");
            }


            $usage = $cmd->usage();

            if ($brief = $cmd->brief()) {
                $logger->write($formatter->format('NAME', 'strong_white') . "\n");
                $logger->write("\t" . $formatter->format($cmd->getName(), 'strong_white') . ' - ' . $brief . "\n\n");
            }

            if ($aliases = $cmd->aliases()) {
                $logger->write($formatter->format('ALIASES', 'strong_white') . "\n");
                $logger->write("\t" . $formatter->format(join(', ', $aliases), 'strong_white') . "\n\n");
            }

            if ( $usage = trim($cmd->usage()) ) {
                $logger->write( $formatter->format('USAGE', 'strong_white') . "\n" );
                $logger->write( "\t" . $usage );
                $logger->write( "\n\n" );
            }

            $logger->write($formatter->format('SYNOPSIS', 'strong_white') . "\n");
            $prototypes = $cmd->getAllCommandPrototype();
            foreach($prototypes as $prototype) {
                $logger->writeln("\t" . ' ' . $prototype);
            }
            $logger->write("\n");


            if ($optionLines = $printer->render($cmd->optionSpecs)) {
                $logger->write($formatter->format('OPTIONS', 'strong_white') . "\n");
                $logger->write($optionLines);
                $logger->write("\n");
            }

            $logger->write($cmd->getFormattedHelpText());
        } else {

            $cmd = $this->parent;
            $logger->write( $formatter->format( ucfirst($cmd->brief()), "strong_white")."\n\n");

            if( $usage = trim($cmd->usage()) ) {
                $logger->write($formatter->format("USAGE", "strong_white") . "\n");
                $logger->write($usage);
                $logger->write("\n\n");
            }

            $logger->write( $formatter->format("SYNOPSIS", "strong_white")."\n" );
            $logger->write( "\t" . $progname );
            if ( ! empty($cmd->getOptionCollection()->options) ) {
                $logger->write(" [options]");
            }

            if ($cmd->hasCommands() ) {
                $logger->write(" <command>");
            } else {
                $argInfos = $cmd->getArgInfoList();
                foreach( $argInfos as $argInfo ) {
                    $logger->write(" <" . $argInfo->name . ">");
                }
            }

            $logger->write("\n\n");


            // print application options
            $logger->write($formatter->format("OPTIONS",'strong_white') . "\n");
            $logger->write($printer->render($cmd->optionSpecs));
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

            $logger->write($formatter->format("COMMANDS\n",'strong_white'));
            $ret = $app->aggregate();

            // show "General commands" title if there are more than one groups
            if (count($ret['groups']) > 1 || $this->options->dev) {
                $this->logger->writeln("  " . $formatter->format("General Commands",'strong_white'));
            }
            $this->layoutCommands($ret['commands']);

            foreach($ret['groups'] as $group) {
                if (!$this->options->dev && $group->getId() == "dev")
                    continue;
                $this->logger->writeln("  " . $formatter->format($group->getName(),'strong_white'));
                $this->layoutCommands($group->getCommands());
            }

            $this->logger->write($this->getFormattedHelpText());

            if ($app->topics) {
                $logger->write($formatter->format("TOPICS\n",'strong_white'));
                $maxWidth = $this->calculateColumnWidth(array_keys($app->topics), 8);
                foreach($app->topics as $topicId => $topic) {
                    printf("%" . ($maxWidth + 8) . "s    %s\n", $topicId, $topic->getTitle());
                }
                $logger->newline();
            }

            $logger->write($formatter->format("HELP\n",'strong_white'));
            $this->logger->writeln(wordwrap(
                "\t'$progname help' lists available subcommands and some" .
                " topics. See '$progname help <command>' or '$progname help <topic>'" .
                " to read about a specific subcommand or $progname.", 70, "\n\t"));
        }

        if ($app->showAppSignature) {
            $logger->newline();
            $logger->write( $formatter->format("{$app->getName()} {$app->getVersion()}","gray"));
            $logger->writeln( $formatter->format("\t\tpowered by https://github.com/c9s/CLIFramework","gray"));
        }
        return true;
    }
}


