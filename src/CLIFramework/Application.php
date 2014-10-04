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
namespace CLIFramework;
use GetOptionKit\ContinuousOptionParser;
use GetOptionKit\OptionCollection;

use CLIFramework\CommandLoader;
use CLIFramework\CommandBase;
use CLIFramework\Logger;
use CLIFramework\CommandInterface;
use CLIFramework\Prompter;
use CLIFramework\CommandGroup;
use CLIFramework\Formatter;
use CLIFramework\Corrector;
use Exception;
use CLIFramework\Exception\CommandNotFoundException;
use ReflectionClass;

class Application extends CommandBase
    implements CommandInterface
{
    const CORE_VERSION = '1.10.0';
    const VERSION = '1.10.0';
    const NAME = 'CLIFramework';

    // options parser
    public $getoptParser;
    public $supportReadline;

    /**
    * command message logger
    *
    * @var CLIFramework\Logger
    */
    public $logger;

    public $showAppSignature = true;


    /**
     *
     */
    public $topics = array();

    /**
     *
     * @var CLIFramework\Formatter
     */
    public $formatter;

    public function __construct()
    {
        parent::__construct();


        $this->formatter = new Formatter;
        $this->logger = new Logger;

        // initliaze command loader
        $this->loader = CommandLoader::getInstance();

        // get current class namespace, add {App}\Command\ to loader
        $app_ref_class = new ReflectionClass($this);
        $app_ns = $app_ref_class->getNamespaceName();
        $this->loader->addNamespace( '\\' . $app_ns . '\\Command' );
        $this->loader->addNamespace( array('\\CLIFramework\\Command' ));

        $this->supportReadline = extension_loaded('readline');
    }

    public function getCurrentAppNamespace() {
        $refClass = new ReflectionClass($this);
        return $refClass->getNamespaceName();
    }

    public function brief()
    {
        return 'application brief';
    }

    public function usage()
    {
    }

    /**
     * register application option specs to the parser
     */
    public function options($opts)
    {
        $opts->add('v|verbose','Print verbose message.');
        $opts->add('d|debug'  ,'Print debug message.');
        $opts->add('q|quiet'  ,'Be quiet.');
        $opts->add('h|help'   ,'help');
        $opts->add('version'  ,'show version');
    }

    public function topics(array $topics) {
        foreach($topics as $key => $val) {
            if (is_numeric($key)) {
                $this->topics[$val] = $this->loadTopic($val);
            } else {
                $this->topics[$key] = $this->loadTopic($val);
            }
        }
    }

    public function topic($topicId, $topicClass = null) {
        $this->topics[$topicId] = $topicClass ? new $topicClass: $this->loadTopic($topicId);
    }

    public function getTopic($topicId) {
        if (isset($this->topics[$topicId])) {
            return $this->topics[$topicId];
        }
    }

    public function loadTopic($topicId) {
        // existing class name or full-qualified class name
        if (class_exists($topicId, true)) {
            return new $topicId;
        }
        if (!preg_match('/Topic$/', $topicId)) {
            $className = ucfirst($topicId) . 'Topic';
        } else {
            $className = ucfirst($topicId);
        }
        $possibleNs = array($this->getCurrentAppNamespace(), 'CLIFramework');
        foreach($possibleNs as $ns) {
            $class = $ns . '\\' . 'Topic' . '\\' . $className;
            if (class_exists($class, true)) {
                return new $class;
            }
        }
        throw new Exception("Topic $topicId not found.");
    }

    /*
     * init application,
     *
     * users register command mapping here. (command to class name)
     */
    public function init()
    {
        // $this->addCommand('list','CLIFramework\\Command\\ListCommand');
        $this->addCommand('help','CLIFramework\\Command\\HelpCommand');
        $this->addCommand('_zsh', 'CLIFramework\\Command\\ZshCompletionCommand');
        $this->addCommand('_meta', 'CLIFramework\\Command\\MetaCommand');
        $this->addCommand('_build-github-wiki', 'CLIFramework\\Command\\BuildGitHubWikiTopicsCommand');
    }

    public function runWithTry($argv)
    {
        try {
            return $this->run($argv);
        } catch ( Exception $e ) {
            $this->getLogger()->error( $e->getMessage() );
        }

        return false;
    }

    /**
     * Run application with
     * list argv
     *
     * @param Array $argv
     *
     * */
    public function run(Array $argv)
    {
        $current_cmd = $this;

        // init application,
        // before parsing options, we have to known the registered commands.
        $current_cmd->_init();

        // use getoption kit to parse application options
        $getopt = new ContinuousOptionParser($current_cmd->optionSpecs);

        // parse the first part options (options after script name)
        // option parser should stop before next command name.
        //
        //    $ app.php -v -d next
        //                  |
        //                  |->> parser
        //
        $current_cmd->setOptions( $getopt->parse( $argv ) );
        $current_cmd->prepare();

        $command_stack = array();
        $arguments = array();

        // get command list from application self
        while ( ! $getopt->isEnd() ) {
            $a = $getopt->getCurrentArgument();

            // if current command is in subcommand list.

            if ($current_cmd->hasCommands()) {
                $a = $getopt->getCurrentArgument();

                if (!$current_cmd->hasCommand($a) ) {
                    if ($guess = $current_cmd->guessCommand($a)) {
                        $a = $guess;
                    } else {
                        throw new CommandNotFoundException($a);
                    }
                }

                $getopt->advance(); // advance position

                // get command object
                $current_cmd = $current_cmd->getCommand($a);

                $getopt->setSpecs($current_cmd->optionSpecs);

                // parse options for command.
                $current_cmd_options = $getopt->continueParse();

                // run subcommand prepare
                $current_cmd->setOptions( $current_cmd_options );

                // echo get_class($current_cmd) , ' => ' , print_r($current_cmd_options);

                $command_stack[] = $current_cmd; // save command object into the stack

            } else {
                $a = $getopt->advance();
                $arguments[] = $a;
            }
        }

        foreach ($command_stack as $cmd) {
            $cmd->prepare();
        }

        // get last command and run
        if ( $last_cmd = array_pop( $command_stack ) ) {
            $return = $last_cmd->executeWrapper( $arguments );
            $last_cmd->finish();
            while ( $cmd = array_pop( $command_stack ) ) {
                // call finish stage.. of every command.
                $cmd->finish();
            }
        } else {
            // no command specified.
            return $this->executeWrapper( $arguments );
        }
        $current_cmd->finish();

        return true;
    }

    public function prepare()
    {
        $options = $this->getOptions();
        if ($options->verbose) {
            $this->getLogger()->setVerbose();
        } elseif ($options->debug) {
            $this->getLogger()->setDebug();
        } elseif ($options->quiet) {
            $this->getLogger()->setLevel(2);
        }
    }

    public function getCoreVersion()
    {
        if ( defined('static::core_version') ) {
            return static::core_version;
        }
        if ( defined('static::CORE_VERSION') ) {
            return static::CORE_VERSION;
        }
    }

    public function getVersion()
    {
        if ( defined('static::VERSION') ) {
            return static::VERSION;
        }
        if ( defined('static::version') ) {
            return static::version;
        }
    }

    public function getName()
    {
        if ( defined('static::NAME') ) {
            return static::NAME;
        }
        if ( defined('static::name') ) {
            return static::name;
        }
    }

    public function execute()
    {
        $options = $this->getOptions();

        if ($options->version) {
            $this->logger->writeln($this->getName() . ' - ' . $this->getVersion());
            $this->logger->writeln("cliframework core: " . $this->getCoreVersion());
            return;
        }

        $arguments = func_get_args();
        // show list and help by default
        $help = $this->getCommand( 'help' );
        if ($help || $options->help) {
            $help->executeWrapper($arguments);
        } else {
            throw new Exception("Help command is not defined.");
        }
    }


    public function getFormatter()
    {
        return $this->formatter;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public static function getInstance()
    {
        static $app;
        if( $app )
            return $app;
        return $app = new static;
    }
    

}
