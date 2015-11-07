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
use CLIFramework\ServiceContainer;
use CLIFramework\Exception\CommandNotFoundException;
use CLIFramework\Exception\CommandArgumentNotEnoughException;
use CLIFramework\Exception\ExecuteMethodNotDefinedException;
use Pimple\Container;

use CLIFramework\ExceptionPrinter\ProductionExceptionPrinter;
use CLIFramework\ExceptionPrinter\DevelopmentExceptionPrinter;

use Exception;
use ReflectionClass;
use InvalidArgumentException;
use BadMethodCallException;

class Application extends CommandBase
    implements CommandInterface
{
    const CORE_VERSION = '2.5.4';
    const VERSION = "2.8.1";
    const NAME = 'CLIFramework';


    /**
     * timestamp when started
     */
    public $startedAt;


    public $supportReadline;


    public $showAppSignature = true;


    /**
     *
     */
    public $topics = array();

    /**
     * @var CLIFramework\Formatter
     */
    public $formatter;

    /**
     * Command message logger.
     *
     * (This should be deprecated since we use service container from now on).
     *
     * @var CLIFramework\Logger
     */
    public $logger;


    public $programName;


    

    /**
     * @var CLIFramework\ServiceContainer
     */
    protected $serviceContainer;


    /**
     * @var Unviersal\Event\PhpEvent
     */
    protected $eventService;


    /**
     * cliframework global config
     */
    protected $globalConfig;


    /** @var bool */
    protected $commandAutoloadEnabled = false;

    public function __construct(Container $container = null, CommandBase $parent = null)
    {
        parent::__construct($parent);

        $this->serviceContainer = $container ?: ServiceContainer::getInstance();

        if (isset($this->serviceContainer['event'])) {
            $this->eventService = $this->serviceContainer['event'];
        } else {
            $this->eventService = PhpEvent::getInstance();
        }

        // initliaze command loader
        // TODO: if the service is not defined, we should create them with default settings.
        $this->loader    = $this->serviceContainer['command_loader'];
        $this->logger    = $this->serviceContainer['logger'];
        $this->formatter = $this->serviceContainer['formatter'];
        $this->globalConfig = $this->serviceContainer['config'];

        // get current class namespace, add {App}\Command\ to loader
        $appRefClass = new ReflectionClass($this);
        $appNs = $appRefClass->getNamespaceName();
        $this->loader->addNamespace( '\\' . $appNs . '\\Command' );
        $this->loader->addNamespace( array('\\CLIFramework\\Command' ));

        $this->supportReadline = extension_loaded('readline');
    }


    /**
     * @return Pimple\Container
     */
    public function getService()
    {
        return $this->serviceContainer;
    }

    public function getEventService()
    {
        return $this->eventService;
    }

    /**
     * Enable command autoload feature.
     *
     * @return void
     */
    public function enableCommandAutoload()
    {
        $this->commandAutoloadEnabled = true;
    }

    /**
     * Disable command autoload feature.
     *
     * @return void
     */
    public function disableCommandAutoload()
    {
        $this->commandAutoloadEnabled = false;
    }

    /**
     * Use ReflectionClass to get the namespace of the current running app.
     * (not CLIFramework\Application itself)
     *
     * @return string classname
     */
    public function getCurrentAppNamespace() {
        $refClass = new ReflectionClass($this);
        return $refClass->getNamespaceName();
    }


    /**
     * @return string brief of this application
     */
    public function brief()
    {
        return 'application brief';
    }

    public function usage()
    {
    }

    /**
     * Register application option specs to the parser
     */
    public function options($opts)
    {
        $opts->add('v|verbose','Print verbose message.');
        $opts->add('d|debug'  ,'Print debug message.');
        $opts->add('q|quiet'  ,'Be quiet.');
        $opts->add('h|help'   ,'Show help.');
        $opts->add('version'  ,'Show version.');

        $opts->add('p|profile','Display timing and memory usage information.');
        $opts->add('log-path?', 'The path of a log file.');
        // Un-implemented options
        $opts->add('no-interact','Do not ask any interactive question.');
        // $opts->add('no-ansi', 'Disable ANSI output.');
    }

    public function topics(array $topics) 
    {
        foreach($topics as $key => $val) {
            if (is_numeric($key)) {
                $this->topics[$val] = $this->loadTopic($val);
            } else {
                $this->topics[$key] = $this->loadTopic($val);
            }
        }
    }

    public function topic($topicId, $topicClass = null) 
    {
        $this->topics[$topicId] = $topicClass ? new $topicClass: $this->loadTopic($topicId);
    }

    public function getTopic($topicId) 
    {
        if (isset($this->topics[$topicId])) {
            return $this->topics[$topicId];
        }
    }

    public function loadTopic($topicId) 
    {
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
        throw new InvalidArgumentException("Topic $topicId not found.");
    }

    /*
     * init application,
     *
     * users register command mapping here. (command to class name)
     */
    public function init()
    {
        // $this->addCommand('list','CLIFramework\\Command\\ListCommand');
        parent::init();
        $this->command('help','CLIFramework\\Command\\HelpCommand');
        $this->commandGroup("Development Commands", array(
            'zsh'                 => 'CLIFramework\\Command\\ZshCompletionCommand',
            'bash'                => 'CLIFramework\\Command\\BashCompletionCommand',
            'meta'                => 'CLIFramework\\Command\\MetaCommand',
            'compile'             => 'CLIFramework\\Command\\CompileCommand',
            'archive'             => 'CLIFramework\\Command\\ArchiveCommand',
            'github:build-topics' => 'CLIFramework\\Command\\BuildGitHubWikiTopicsCommand',
        ))->setId('dev');
    }

    /**
     * Execute `run` method with a default try & catch block to catch the exception.
     *
     * @param array $argv
     *
     * @return bool return true for success, false for failure. the returned
     *              state will be reflected to the exit code of the process.
     */
    public function runWithTry(array $argv)
    {
        try {
            return $this->run($argv);
        } catch (CommandArgumentNotEnoughException $e) {
            $this->logger->error( $e->getMessage() );
            $this->logger->writeln("Expected argument prototypes:");
            foreach($e->getCommand()->getAllCommandPrototype() as $p) {
                $this->logger->writeln("\t" . $p);
            }
            $this->logger->newline();
        } catch (CommandNotFoundException $e) {
            $this->logger->error( $e->getMessage() . " available commands are: " . join(', ', $e->getCommand()->getVisibleCommandList())  );
            $this->logger->newline();

            $this->logger->writeln("Please try the command below to see the details:");
            $this->logger->newline();
            $this->logger->writeln("\t" . $this->getProgramName() . ' help ' );
            $this->logger->newline();
        } catch (BadMethodCallException $e) {

            $this->logger->error($e->getMessage());
            $this->logger->error("Seems like an application logic error, please contact the developer.");

        } catch (Exception $e) {

            if ($this->options && $this->options->debug) {
                $printer = new DevelopmentExceptionPrinter($this->getLogger());
                $printer->dump($e);
            } else {
                $printer = new ProductionExceptionPrinter($this->getLogger());
                $printer->dump($e);
            }
        }

        return false;
    }

    /**
     * Run application with
     * list argv
     *
     * @param Array $argv
     *
     * @return bool return true for success, false for failure. the returned
     *              state will be reflected to the exit code of the process.
     * */
    public function run(Array $argv)
    {
        $this->setProgramName($argv[0]);

        $currentCmd = $this;

        // init application,
        // before parsing options, we have to known the registered commands.
        $currentCmd->_init();

        // use getoption kit to parse application options
        $getopt = new ContinuousOptionParser($currentCmd->optionSpecs);

        // parse the first part options (options after script name)
        // option parser should stop before next command name.
        //
        //    $ app.php -v -d next
        //                  |
        //                  |->> parser
        //
        //
        $appOptions = $getopt->parse( $argv );
        $currentCmd->setOptions($appOptions);
        if (false === $currentCmd->prepare()) {
            return false;
        }


        $command_stack = array();
        $arguments = array();

        // get command list from application self
        while ( ! $getopt->isEnd() ) {
            $a = $getopt->getCurrentArgument();

            // if current command is in subcommand list.

            if ($currentCmd->hasCommands()) {
                $a = $getopt->getCurrentArgument();

                if (!$currentCmd->hasCommand($a) ) {
                    if (!$appOptions->noInteract && ($guess = $currentCmd->guessCommand($a)) !== NULL) {
                        $a = $guess;
                    } else {
                        throw new CommandNotFoundException($currentCmd, $a);
                    }
                }

                $getopt->advance(); // advance position

                // get command object
                $currentCmd = $currentCmd->getCommand($a);
                $getopt->setSpecs($currentCmd->optionSpecs);

                // parse options for command.
                $currentCmd->setOptions($getopt->continueParse());
                $command_stack[] = $currentCmd; // save command object into the stack

            } else {
                $a = $getopt->advance();
                $arguments[] = $a;
            }
        }

        foreach ($command_stack as $cmd) {
            if (false === $cmd->prepare()) {
                return false;
            }
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
        $currentCmd->finish();
        $this->finish();
        return true;
    }

    /**
     * This is a `before` trigger of an app. when the application is getting
     * started, we run `prepare` method to prepare the settings.
     */
    public function prepare()
    {
        $this->startedAt = microtime(true);
        $options = $this->getOptions();
        $config = $this->getGlobalConfig();

        if ($options->debug || $options->verbose || $options->quiet) {

            if ($options->debug) {
                $this->getLogger()->setDebug();
            } else if ($options->verbose) {
                $this->getLogger()->setVerbose();
            } else if ($options->quiet) {
                $this->getLogger()->setLevel(2);
            }

        } else {

            if ($config->isDebug()) {
                $this->getLogger()->setDebug();
            } else if ($config->isVerbose()) {
                $this->getLogger()->setVerbose();
            }

        }

        return true;
    }

    public function finish() {
        if ($this->options->profile) {
            $this->logger->info(
                sprintf('Memory usage: %.2fMB (peak: %.2fMB), time: %.4fs',
                    memory_get_usage(true) / (1024 * 1024),
                    memory_get_peak_usage(true) / (1024 * 1024),
                    (microtime(true) - $this->startedAt)
                )
            );
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

    public function setProgramName($programName) {
        $this->programName = $programName;
    }

    public function getProgramName() {
        return $this->programName;
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

    /**
     * This method is the top logic of an application. when there is no
     * argument provided, we show help content by default.
     *
     * @return bool return true if success
     */
    public function execute()
    {
        $options = $this->getOptions();
        if ($options->version) {
            $this->logger->writeln($this->getName() . ' - ' . $this->getVersion());
            $this->logger->writeln("cliframework core: " . $this->getCoreVersion());
            return true;
        }

        $arguments = func_get_args();

        // show list and help by default
        $help = $this->getCommand('help');
        $help->setOptions($options);
        if ($help || $options->help) {
            $help->executeWrapper($arguments);
            return true;
        }
        throw new CommandNotFoundException($this, 'help');
    }

    public function getFormatter()
    {
        return $this->formatter;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function getGlobalConfig()
    {
        return $this->globalConfig;
    }


    /**
     * A quick helper for accessing service
     */
    public function __get($name) 
    {
        if (isset($this->serviceContainer[$name])) {
            return $this->serviceContainer[$name];
        }
        throw new InvalidArgumentException("Application class doesn't have `$name` service or property.");
    }

    public static function getInstance()
    {
        static $app;
        if( $app )
            return $app;
        return $app = new static;
    }
    

}
