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
namespace CLIFramework;

use GetOptionKit\OptionSpecCollection;
use Exception;


/**
 * Command based class
 *
 * register subcommands.
 */
abstract class CommandBase 
{

    // command message logger
    public $logger;

    // command class loader
    public $loader;

    /* application commands */
    public $commands = array();

    public $options;

    public $parent;

    public $optionSpecs;

    function __construct()
    {
        $this->logger       = new Logger;
    }


    function usage()
    {
        // return usage
    }

    /* TODO: read brief from markdown format doc file. */
    function brief() 
    {
        return 'undefined.';
    }

    /* 
     * sub command override this method to define its option spec here 
     *
     * it's spec collection object.
     * */
    function options($getopt)
    {

    }

    /**
     * init function 
     *
     * register subcommand here
     * */
    function init()
    {

    }



    /**
     * register command to application, in init() method stage,
     * we save command classes in property `commands`.
     *
     * when command is needed, get the command from property `commands`, and 
     * initialize the command object.
     *
     * class name could be full-qualified or subclass name (under App\Command\ )
     *
     * @param string $command Command name or subcommand name
     * @param string $class   Full-qualified Class name
     * @return string         Loaded class name
     */
    public function registerCommand($command,$class = null)
    {



        // try to load the class/subclass.
        if( $class ) {
            if( $this->loader->loadClass( $class ) === false )
                throw Exception("Command class not found.");
        }
        else {
            if ( $this->parent ) {
                $class = $this->loader->loadSubcommand($command,$this);
            }
            else {
                $class = $this->loader->load($command);
            }
        }

        if( ! $class )
            throw new Exception("command class $class for command $command not found");

        return $this->commands[ $command ] = $class;
    }


    public function hasCommand($command)
    {
        return isset($this->commands[ $command ]);
    }


    /**
     * return command name list
     *
     * @return Array
     */
    public function getCommandList()
    {
        return array_keys( $this->commands );
    }


    /*
     * return the command class name
     *
     */
    public function getCommandClass($command)
    {
        return @$this->commands[ $command ];
    }



    /*
     * get subcommand object from current command
     * by command name
     *
     * @param string $command
     */
    public function getCommand($command)
    {

        // keep scope here. (hate)
        $command_class = $this->getCommandClass($command);
        if( ! $command_class ) {
            throw new Exception("command $subcommand not found.");
        }
        return $this->createCommand($command_class);
    }


    function createCommand($command_class)
    {
        // if current_cmd is not application, we should save parent command object.
        $cmd = new $command_class;

        // check self 
        if( is_a($this, '\CLIFramework\Application' ) ) {
            $cmd->application = $this;
            $cmd->parent = $this;
        } else {
            $cmd->application = $this->application;
            $cmd->parent = $this;
        } 

        // get option parser, init specs from the command.
        $specs = new OptionSpecCollection;

        // init application options
        $cmd->options($specs);


        // save options specs
        $cmd->optionSpecs = $specs;

        // let command has the command loader to register subcommand (load class)
        $cmd->loader = $this->loader;

        $cmd->init();
        return $cmd;
    }


    /* 
     * return comand options (parsed) 
     */
    function getOptions()
    {
        return $this->options;
    }


    /* 
     * set options
     *
     * @param OptionResult $options 
     */
    function setOptions( $options )
    {
        $this->options = $options;
    }

    function getLogger()
    {
        return $this->logger;
    }

    function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /* prepare stage */
    function prepare() { }

    /* for finalize stage */
    function finish() { }

    /* main command execute method */
    abstract function execute($arguments);
}



