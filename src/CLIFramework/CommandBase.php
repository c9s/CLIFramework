<?php
/*
 * This file is part of the CLIFramework package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace CLIFramework;
use Exception;
use ReflectionObject;
use ArrayAccess;
use IteratorAggregate;
use GetOptionKit\OptionCollection;
use GetOptionKit\OptionResult;
use CLIFramework\Prompter;
use CLIFramework\Application;
use CLIFramework\Chooser;
use CLIFramework\CommandLoader;
use CLIFramework\CommandGroup;
use CLIFramework\Exception\CommandNotFoundException;
use CLIFramework\Exception\InvalidCommandArgumentException;
use CLIFramework\Exception\CommandArgumentNotEnoughException;
use CLIFramework\Exception\CommandClassNotFoundException;
use CLIFramework\Exception\ExecuteMethodNotDefinedException;
use CLIFramework\ArgInfo;
use CLIFramework\ArgInfoList;
use CLIFramework\Corrector;

/**
 * Command based class (application & subcommands inherit from this class)
 *
 * register subcommands.
 */
abstract class CommandBase
    implements ArrayAccess, IteratorAggregate, CommandInterface
{



    /**
     * @var application commands
     *
     * which is an associative array, contains command class mapping info
     *
     *     command name => command class name
     *
     * */
    public $commands = array();


    /**
     * @var CommandGroup[]
     */
    public $commandGroups = array();

    public $aliases = array();

    /**
     * @var GetOptionKit\OptionResult parsed options
     */
    public $options;

    /**
     * Parent commmand object. (the command caller)
     *
     * @var CLIFramework\CommandBase or CLIFramework\Application
     */
    public $parent;

    public $optionSpecs;

    public $argInfos = array();

    public function __construct() {
    }


    /**
     * Returns one line brief for this command.
     *
     * @return string brief
     */
    public function brief()
    {
        return 'awesome brief for your app.';
    }

    /**
     * Usage string  (one-line)
     *
     * @return string usage
     */
    public function usage()
    {
    
    }

    /**
     * Detailed help text
     *
     * @return string helpText
     */
    public function help()
    {
        return '';
    }




    /**
     * Method for users to define alias.
     */
    public function aliases() {
    }

    /**
     * Add a command group and register the commands automatically
     *
     * @param string $groupName The group name
     * @param array  $commands  Command array combines indexed command names or command class assoc array.
     * @return CommandGroup
     */
    public function addCommandGroup($groupName, $commands = array() ) {
        $group = new CommandGroup($groupName);
        foreach($commands as $key => $val) {
            $name = $val;
            if (is_numeric($key)) {
                $cmd = $this->addCommand($val);
            } else {
                $cmd = $this->addCommand($key, $val);
                $name = $key;
            }
            $group->addCommand($name, $cmd);
        }
        $this->commandGroups[] = $group;
        return $group;
    }

    public function getCommandGroups() {
        return $this->commandGroups;
    }

    public function isApplication() {
        return $this instanceof Application;
    }

    /**
     * Returns help message text of a command object.
     *
     */
    public function getFormattedHelpText()
    {
        $text = $this->help();

        // format text styles
        $formatter = $this->getFormatter();
        $text = preg_replace_callback( '#<(\w+)>(.*?)</\1>#i', function($matches) use ($formatter) {
            $style = $matches[1];
            $text = $matches[2];

            switch ($style) {
                case 'b': $style = 'bold'; break;
                case 'u': $style = 'underline'; break;
            }

            if ( $formatter->hasStyle($style) ) {
                return $formatter->format( $text , $style );
            }

            return $matches[0];
        }, $text );

        // support simple markdown style
        $text = preg_replace_callback( '#[*]([^*]*?)[*]#' , function($matches) use ($formatter) {
            return $formatter->format( $matches[1] , 'bold' );
        } , $text );

        $text = preg_replace_callback( '#[_]([^_]*?)[_]#' , function($matches) use ($formatter) {
            return $formatter->format( $matches[1] , 'underline' );
        } , $text );

        return $text;
    }

    /**
     * Subcommand can override this method to define its option spec here
     *
     * @code
     *
     *      function options($opts) {
     *          $opts->add('v|verbose','Verbose messages');
     *          $opts->add('d|debug',  'Debug messages');
     *          $opts->add('level:',  'Level takes a value.');
     *      }
     *
     * @param GetOptionKit\OptionCollection Spec collection object.
     *
     * @see GetOptionKit\OptionCollection
     */
    public function options($getopt)
    {

    }

    /**
     * user-defined init function
     *
     * register custom subcommand here
     *
     **/
    public function init()
    {

    }


    public function _init() {
        // get option parser, init specs from the command.
        $this->optionSpecs = new OptionCollection;
        // init application options
        $this->options($this->optionSpecs);
        $this->init();
    }



    /**
     * A short alias for registerCommand method
     *
     * @param string $command
     * @param string $class
     */
    public function registerCommand($command,$class = null)
    {
        trigger_error("'registerCommand' method is deprecated, please use 'addCommand' instead.\n");
        return $this->addCommand($command, $class);
    }

    public function setParent(CommandBase $parent)
    {
        $this->parent = $parent;
    }

    public function getParent() 
    {
        return $this->parent;
    }

    /**
     * Returns command loader object.
     */
    public function getLoader() 
    {
        return CommandLoader::getInstance();
    }

    public function commandGroup($groupName, $commands = array())
    {
        if (is_string($commands)) {
            $commands = explode(' ',$commands);
        }
        return $this->addCommandGroup($groupName, $commands);
    }

    public function command($command, $class = null) 
    {
        return $this->addCommand($command, $class);
    }

    /**
     * Register a command to application, in init() method stage,
     * we save command classes in property `commands`.
     *
     * When command is needed, get the command from property `commands`, and
     * initialize the command object.
     *
     * class name could be full-qualified or subclass name (under App\Command\ )
     *
     * @param  string $command Command name or subcommand name
     * @param  string $class   Full-qualified Class name
     * @return string Loaded class name
     */
    public function addCommand($command,$class = null)
    {
        // try to load the class/subclass,
        // or generate command class name automatically.
        if ($class) {
            if ($this->getLoader()->loadClass($class) === false )
                throw CommandClassNotFoundException("Command class $class not found.");
        } else {
            if ($this->parent) {
                // get class name by subcommand rules.
                $class = $this->getLoader()->loadSubcommand($command,$this);
            } else {
                // get class name by command rules.
                $class = $this->getLoader()->load($command);
            }
        }
        if (! $class) {
            throw new CommandClassNotFoundException("command class $class for command $command not found");
        }
        // register command to table
        $cmd = $this->createCommand($class);
        $this->connectCommand($command, $cmd);
        return $cmd;
    }



    public function getAllCommandPrototype() {
        $lines = array();

        if (method_exists($this,'execute')) {
            $lines[] = $this->getCommandPrototype();
        }
        if ($this->hasCommands()) {
            foreach( $this->commands as $name => $subcmd) {
                $lines[] = $subcmd->getCommandPrototype();
            }
        }
        return $lines;
    }

    public function getCommandPrototype() {
        $out = array();

        $out[] = $this->getApplication()->getProgramName();

        // $out[] = $this->getName();
        foreach($this->getCommandNameTraceArray() as $n) {
            $out[] = $n;
        }

        if (! empty($this->getOptionCollection()->options) ) {
            $out[] = "[options]";
        }
        if ($this->hasCommands() ) {
            $out[] = "<subcommand>";
        } else {
            $argInfos = $this->getArgumentsInfo();
            foreach( $argInfos as $argInfo ) {
                $out[] = "<" . $argInfo->name . ">";
            }
        }
        return join(" ",$out);
    }



    /**
     * Connect command object with the current command object.
     *
     */
    protected function connectCommand($name, CommandBase $cmd) {
        $cmd->setName($name);
        $this->commands[$name] = $cmd;

        // regsiter command aliases to the alias table.
        $aliases = $cmd->aliases();
        if (is_string($aliases)) {
            $aliases = preg_split('/\s+/', $aliases);
        }
        if (!is_array($aliases)) {
            throw new InvalidArgumentException("Aliases needs to be an array or a space-separated string.");
        }
        foreach( $aliases as $alias) {
            $this->aliases[$alias] = $cmd;
        }
    }



    /**
     * Aggregate command info
     */
    public function aggregate() {
        $groups = array();
        $commands = array();
        foreach($this->getVisibleCommands() as $name => $cmd) {
            $commands[ $name ] = $cmd;
        }

        foreach($this->commandGroups as $g) {
            if ($g->isHidden) {
                continue;
            }
            foreach($g->getCommands() as $name => $cmd) {
                unset($commands[$name]);
            }
        }

        uasort($this->commandGroups, function($a, $b) { 
            if ($a->getId() == "dev") return 1;
            return 0;
        });

        return array(
            'groups' => $this->commandGroups,
            'commands' => $commands,
        );
    }


    /**
     * Return true if this command has subcommands.
     *
     * @return boolean
     */
    public function hasCommands() {
        return ! empty($this->commands);
    }

    /**
     * Check if a command name is registered in this application / command object.
     *
     * @param string $command command name
     *
     * @return CLIFramework\Command
     */
    public function hasCommand($command)
    {
        return isset($this->commands[$command]) || isset($this->aliases[$command]);
    }

    /**
     * Get command name list
     *
     * @return Array command name list
     */
    public function getCommandList()
    {
        return array_keys( $this->commands );
    }


    public function getVisibleCommands() {
        $cmds = array();
        foreach( $this->getVisibleCommandList() as $name ) {
            $cmds[ $name ] = $this->commands[ $name ];
        }
        return $cmds;
    }


    public function getVisibleCommandList() {
        return array_filter(array_keys($this->commands), function($name) {
            return !preg_match('#^_#', $name);
        });
    }


    /**
     * Return the command name stack
     *
     * @return string[]
     */
    public function getCommandNameTraceArray() {
        $cmdStacks = array( $this->getName() );
        $p = $this->parent;
        while($p) {
            if (! $p instanceof Application) {
                $cmdStacks[] = $p->getName();
            }
            $p = $p->parent;
        }
        return array_reverse($cmdStacks);
    }

    public function getSignature() {
        return join('.', $this->getCommandNameTraceArray());
    }


    /**
     * Return the objects of all sub commands.
     *
     * @return Command[]
     */
    public function getCommands() 
    {
        return $this->commands;
    }

    /*
     * Get subcommand object from current command
     * by command name.
     *
     * @param string $command
     *
     * @return Command initialized command object.
     */
    public function getCommand($command)
    {
        if ( isset($this->aliases[$command]) ) {
            return $this->aliases[$command];
        }
        if ( isset($this->commands[ $command ]) ) {
            return $this->commands[ $command ];
        }
        throw new CommandNotFoundException($this, $command);
    }

    public function guessCommand($commandName) {
        // array of words to check against
        $words = array_keys($this->commands);
        $correction = new Corrector($words);
        return $correction->correct($commandName);
    }



    /**
     * Create and initialize command object.
     *
     * @param  string  $commandClass Command class.
     * @return Command command object.
     */
    public function createCommand($commandClass)
    {
        // if current_cmd is not application, we should save parent command object.
        if ( $this instanceof \CLIFramework\Application ) {
            $cmd = new $commandClass($this);
            $cmd->parent = $this;
        } else {
            $cmd = new $commandClass($this->application);
            $cmd->parent = $this;
        }
        $cmd->_init();
        return $cmd;
    }

    /**
     * Get Option Results
     *
     * @return GetOptionKit\OptionCollection command options object (parsed, and a option results)
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set option results
     *
     * @param GetOptionKit\OptionResult $options
     */
    public function setOptions(OptionResult $options)
    {
        $this->options = $options;
    }

    /**
     * Get Command-line Option spec
     *
     * @return GetOptionKit\OptionCollection
     */
    public function getOptionCollection()
    {
        return $this->optionSpecs;
    }

    /**
     * Prepare stage method 
     */
    public function prepare() { }

    /**
     * Finalize stage method
     */
    public function finish() { }

    /**
     * abstract method let user define their own argument info.
     */
    public function arguments($args) { }

    public function getArgumentsInfo() {
        if (!$this->argInfos || empty($this->argInfos)) {
            $args = new ArgInfoList;
            $this->arguments($args);
            if (count($args)) {
                $this->argInfos = $args;
            }
        }
        // if it still empty
        if (!$this->argInfos || empty($this->argInfos)) {
            $this->argInfos = $this->getArgumentsInfoByReflection();
        }
        return $this->argInfos;
    }

    /**
     * The default behaviour: get argument info from method parameters
     */
    public function getArgumentsInfoByReflection() { 
        $argInfo = new ArgInfoList;

        $ro = new ReflectionObject($this);
        if (!method_exists($this,'execute')) {
            throw new ExecuteMethodNotDefinedException;
        }

        $method = $ro->getMethod('execute');
        $requiredNumber = $method->getNumberOfRequiredParameters();
        $parameters = $method->getParameters();
        foreach ($parameters as $param) {
            // TODO: add description to the argument
            $a = new ArgInfo($param->getName());
            if ($param->isOptional()) {
                $a->optional(true);
            }
            $argInfo->append($a);
        }
        return $argInfo;
    }


    /**
     * Execute command object, this is a wrapper method for execution.
     *
     * In this method, we check the command arguments by the Reflection feature
     * provided by PHP.
     *
     * @param  array $args command argument list (not associative array).
     * @return mixed the value of execution result.
     */
    public function executeWrapper(array $args)
    {
        // Validating arguments
        $argInfos = $this->getArgumentsInfo();

        for ($i = 0; $i < count($argInfos); $i++ ) {
            $argInfo = $argInfos[$i];
            if (isset($args[$i])) {
                $arg = $args[$i];

                $valid = false;
                $message = NULL;
                $ret = $argInfo->validate($arg);

                if (is_array($ret)) {
                    $valid = $ret[0];
                    $message = $ret[1];
                } elseif (is_bool($ret)) {
                    $valid = $ret;
                }

                if ($valid === FALSE) {
                    $this->logger->error($message ?: "Invalid argument $arg");
                    return;
                }
            }
        }

        // call_user_func_array(  );
        $refl = new ReflectionObject($this);
        if (!method_exists( $this,'execute' )) {
            throw new ExecuteMethodNotDefinedException();
        }

        $reflMethod = $refl->getMethod('execute');
        $requiredNumber = $reflMethod->getNumberOfRequiredParameters();
        if ( count($args) < $requiredNumber ) {
            throw new CommandArgumentNotEnoughException($this, count($args), $requiredNumber);
        }
        return call_user_func_array(array($this,'execute'), $args);
    }

    /**
     * Show prompt with message, you can provide valid options
     * for the simple validation.
     *
     * TODO: let user register their custom prompt handler.
     *
     * @param string $prompt       Prompt message.
     * @param array  $validAnswers an array of valid values (optional)
     *
     * @return string user input value
     */
    public function ask($prompt, $validAnswers = null )
    {
        $prompter = new Prompter;
        $prompter->setStyle('ask');
        return $prompter->ask( $prompt , $validAnswers );
    }

    /**
     * Provide a simple console menu for choices,
     * which gives values an index number for user to choose items.
     *
     * @code
     *
     *      $val = $app->choose('Your versions' , array(
     *          'php-5.4.0' => '5.4.0',
     *          'php-5.4.1' => '5.4.1',
     *          'system' => '5.3.0',
     *      ));
     *      var_dump($val);
     *
     * @code
     *
     * @param  string $prompt  Prompt message
     * @param  array  $choices
     * @return mixed  value
     */
    public function choose($prompt, $choices)
    {
        $chooser = new Chooser;
        $chooser->setStyle('choose');
        return $chooser->choose( $prompt, $choices );
    }

    public function offsetExists($key)
    {
        return isset($this->commands[$key]);
    }

    public function offsetSet($key,$value)
    {
        $this->commands[$key] = $value;
    }

    public function offsetGet($key)
    {
        return $this->commands[$key];
    }

    public function offsetUnset($key)
    {
        unset($this->commands[$key]);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->commands);
    }

}
