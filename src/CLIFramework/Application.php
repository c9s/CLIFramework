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
use GetOptionKit\OptionSpecCollection;

use CLIFramework\CommandLoader;
use CLIFramework\CommandBase;
use CLIFramework\Logger;

use Exception;

class Application extends CommandBase
{
    const core_version = '1.2.1';
    const version  = '1.2.1';
    const name = 'CLIFramework';

    // options parser
    public $getoptParser;
    public $supportReadline;

    /**
    * command message logger
    *
    * @var CLIFramework\Logger
    */
    static $logger;

    function __construct()
    {
        parent::__construct();

        // get current class namespace, add {App}\Command\ to loader
        $app_ref_class = new \ReflectionClass($this);
        $app_ns = $app_ref_class->getNamespaceName();

        $this->loader = new CommandLoader();
        $this->loader->addNamespace( array( '\\CLIFramework\\Command' ) );
        $this->loader->addNamespace( '\\' . $app_ns . '\\Command' );


        // init option parser
        $this->getoptParser = new ContinuousOptionParser;

        $this->supportReadline = extension_loaded('readline');
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


    /* 
     * init application,
     *
     * users register command mapping here. (command to class name)
     */
    public function init()
    {
        // $this->registerCommand('list','\\CLIFramework\\Command\\ListCommand');
        $this->registerCommand('help','\\CLIFramework\\Command\\HelpCommand');
    }


    /**
     * run application with 
     * list argv 
     *
     * @param Array $argv
     *
     * */
    public function run(Array $argv)
    {
        try {
            $current_cmd = $this;

            // init application,
            // before parsing options, we have to known the registered commands.
            $current_cmd->init();

            // use getoption kit to parse application options
            $getopt = $this->getoptParser;
            $specs = new OptionSpecCollection;
            $getopt->setSpecs( $specs );

            // init application options
            $current_cmd->options($specs);

            // save options specs
            $current_cmd->optionSpecs = $specs;

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
            $subcommand_list = $current_cmd->getCommandList();
            while( ! $getopt->isEnd() ) 
            {
                $a = $getopt->getCurrentArgument();

                // if current command is in subcommand list.
                if( in_array(  $getopt->getCurrentArgument() , $subcommand_list ) ) 
                {
                    $subcommand = $getopt->getCurrentArgument();
                    $getopt->advance(); // advance position

                    // get command object
                    $current_cmd = $current_cmd->getCommand( $subcommand );

                    $getopt->setSpecs($current_cmd->optionSpecs);

                    // parse options for command.
                    $current_cmd_options = $getopt->continueParse();

                    // run subcommand prepare
                    $current_cmd->setOptions( $current_cmd_options );

                    // echo get_class($current_cmd) , ' => ' , print_r($current_cmd_options);

                    $command_stack[] = $current_cmd; // save command object into the stack

                    // update subcommand list
                    $subcommand_list = $current_cmd->getCommandList();

                } else {
                    $a = $getopt->advance();
                    $arguments[] = $a;
                }
            }

            foreach($command_stack as $cmd) {
                $cmd->prepare();
            }


            // get last command and run
            if( $last_cmd = array_pop( $command_stack ) ) {
                $return = $last_cmd->executeWrapper( $arguments );
                $last_cmd->finish();
                while( $cmd = array_pop( $command_stack ) ) {
                    // call finish stage.. of every command.
                    $cmd->finish();
                }
            }
            else {
                // no command specified.
                return $this->executeWrapper( $arguments );
            }

            $current_cmd->finish();
        } 
        catch( Exception $e ) 
        {
            $this->getLogger()->error( $e->getMessage() );
            return false;
        }
        return true;
    }

    public function prepare()
    {
        $options = $this->getOptions();
        if( $options->verbose ) {
            static::getLogger()->setVerbose();
        }
        elseif( $options->debug ) {
            static::getLogger()->setDebug();
        }
        elseif( $options->quiet ) {
            static::getLogger()->setLevel(2);
        }
    }

    public function getCoreVersion()
    {
        return static::core_version;
    }

    public function getVersion()
    {
        return static::version;
    }

    public function getName()
    {
        return static::name;
    }

    public function execute()
    {
        $options = $this->getOptions();

        if( $options->version ) {
            echo $this->getName() , ' - ' , $this->getVersion() , "\n";
            echo "cliframework core: ", $this->getCoreVersion() , "\n";
            return;
        }

        $arguments = func_get_args();
        // show list and help by default
        $help_class = $this->getCommandClass( 'help' );
        if( $help_class || $options->help ) {
            $help = new $help_class;
            $help->application = $this;
            $help->parent = $this;
            $help->executeWrapper($arguments);
        }
        else {
            throw new Exception("Help command is not defined.");
        }
    }

    static function getLogger()
    {
        if( static::$logger )
            return static::$logger;
        return static::$logger = new Logger;
    }

}

