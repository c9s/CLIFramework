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
use LogicException;
use InvalidArgumentException;
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
use CLIFramework\Exception\ExtensionException;
use CLIFramework\ArgInfo;
use CLIFramework\ArgInfoList;
use CLIFramework\Corrector;
use CLIFramework\Extension\Extension;
use CLIFramework\Extension\ExtensionBase;
use CLIFramework\Extension\CommandExtension;
use CLIFramework\Extension\ApplicationExtension;

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
    protected $commands = array();


    /**
     * @var CommandGroup[]
     */
    protected $commandGroups = array();

    protected $aliases = array();

    /**
     * @var \GetOptionKit\OptionResult parsed options
     */
    public $options;

    /**
     * Parent commmand object. (the command caller)
     *
     * @var \CLIFramework\CommandBase or CLIFramework\Application
     */
    public $parent;

    public $optionSpecs;

    protected $argInfos = array();

    protected $extensions = array();

    public function __construct(CommandBase $parent = null) 
    {
        // this variable is optional (for backward compatibility)
        if ($parent) {
            $this->setParent($parent);
        }
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
     *
     * @return string[]
     */
    public function aliases() 
    { 
    }





    /**
     * Register and bind the extension
     *
     * @param CLIFramework\Extension\ExtensionBase
     */
    public function addExtension(ExtensionBase $extension)
    {
        if (!$extension->isAvailable()) {
            throw new ExtensionException("Extension " . get_class($extension) . " is not available", $extension);
        }
        $this->bindExtension($extension);
        $this->extensions[] = $extension;
    }

    /**
     * method `extension` is an alias of addExtension
     *
     * @param CLIFramework\Extension\ExtensionBase
     */
    public function extension($extension)
    {
        if (is_string($extension)) {
            $extension = new $extension;
        } else if (! $extension instanceof ExtensionBase) {
            throw new LogicException("Not an extension object or an extension class name.");
        }
        return $this->addExtension($extension);
    }


    protected function bindExtension(ExtensionBase $extension)
    {
        if ($extension instanceof CommandExtension) {
            $extension->bindCommand($this);
        } else if ($extension instanceof ApplicationExtension) {
            $extension->bindApplication($this->getApplication());
        }
    }

    protected function initExtensions()
    {
        foreach ($this->extensions as $extension) {

        }
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
     * Default init function.
     *
     * Register custom subcommand here.
     */
    public function init()
    {
        if ($this->isCommandAutoloadEnabled())
            $this->autoloadCommands();
    }


    public function isCommandAutoloadEnabled()
    {
        return $this->isApplication()
            ? $this->commandAutoloadEnabled
            : $this->getApplication()->commandAutoloadEnabled;
    }

    /**
     * Get the main application object from parents or the object itself.
     *
     * @return CLIFramework\Application
     */
    public function getApplication() {
        if ($this instanceof Application) {
            return $this;
        }

        $p = $this->parent;
        while ($p) {
            if ($p instanceof Application) {
                return $p;
            }
            $p = $p->parent;
        }
    }


    /**
     * Autoload comands/subcommands in a given directory
     *
     * @param string|null $path path of directory commands placed at.
     * @return void
     */
    protected function autoloadCommands($path = null)
    {
        $autoloader = new CommandAutoloader($this);
        $autoloader->autoload($path);
    }


    public function _init() 
    {
        // get option parser, init specs from the command.
        $this->optionSpecs = new OptionCollection;

        // create an empty option result, please note this result object will
        // be replaced with the parsed option result.
        $this->options = new OptionResult;

        // init application options
        $this->options($this->optionSpecs);


        // build argument info list 
        $args = new ArgInfoList;
        $this->arguments($args);
        if (count($args) > 0) {
            $this->argInfos = $args;
        } else {
            $this->argInfos = $this->getArgInfoListByReflection();
        }


        $this->init();
        $this->initExtensions();
    }



    /**
     * A short alias for registerCommand method
     *
     * @param string $command
     * @param string $class
     */
    public function registerCommand($command, $class = null)
    {
        $trace = debug_backtrace(false, 2);
        $call = $trace[0]['file'].':'.$trace[0]['line'];
        trigger_error("'registerCommand' method is deprecated, please use 'addCommand' instead. Called on $call\n");
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


    /**
     * Register commands into group
     *
     * @param string $groupName The group name
     * @param string|array $commands The command names. when given string, it must be space-separated.
     *
     * @return CLIFramework\CommandGroup
     */
    public function commandGroup($groupName, $commands = array())
    {
        if (is_string($commands)) {
            $commands = explode(' ',$commands);
        }
        return $this->addCommandGroup($groupName, $commands);
    }


    /**
     * Register command
     *
     * @param string $command The command name
     * @param string $class   (optional) The command class. if this argument is
     *                        ignroed, the class name is automatically detected.
     */
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
     * @throws CommandClassNotFoundException
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



    /**
     * getAllCommandPrototype() method is used for returning command prototype in string.
     *
     * Very useful when user entered command with wrong argument or format.
     *
     * @return string
     */
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
            $argInfos = $this->getArgInfoList();
            foreach( $argInfos as $argInfo ) {
                $out[] = "<" . $argInfo->name . ">";
            }
        }
        return join(" ",$out);
    }



    /**
     * connectCommand connects a command name with a command object.
     *
     * @param string $name
     * @param CLIFramework\CommandBase $cmd
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



    /**
     * Some commands are not visible. when user runs 'help', we should just
     * show them these visible commands
     *
     * @return array[string]CommandBase command map
     */
    public function getVisibleCommands() 
    {
        $cmds = array();
        foreach( $this->getVisibleCommandList() as $name ) {
            $cmds[ $name ] = $this->commands[ $name ];
        }
        return $cmds;
    }



    /**
     * Command names start with understore are hidden command. we ignore the
     * commands.
     *
     * @return CommandBase[]
     */
    public function getVisibleCommandList() 
    {
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
    public function getCommand($commandName)
    {
        if ( isset($this->aliases[$commandName]) ) {
            return $this->aliases[$commandName];
        }
        if ( isset($this->commands[ $commandName ]) ) {
            return $this->commands[ $commandName ];
        }
        throw new CommandNotFoundException($this, $commandName);
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
        $cmd = new $commandClass($this);
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
    public function prepare()
    {
        foreach ($this->extensions as $extension) { 
            $extension->prepare();
        }
    }

    /**
     * Finalize stage method
     */
    public function finish() 
    {
        foreach ($this->extensions as $extension) { 
            $extension->finish();
        }
    }

    /**
     * abstract method let user define their own argument info.
     *
     * @param CLIFramework\ArgInfoList
     */
    public function arguments($args) { }

    public function getArgInfoList() 
    {
        return $this->argInfos;
    }

    /**
     * The default behaviour: get argument info from method parameters
     */
    public function getArgInfoListByReflection() { 
        $argInfo = new ArgInfoList;

        $ro = new ReflectionObject($this);
        if (!method_exists($this,'execute')) {
            throw new ExecuteMethodNotDefinedException($this);
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
        $argInfos = $this->getArgInfoList();

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
            throw new ExecuteMethodNotDefinedException($this);
        }

        $reflMethod = $refl->getMethod('execute');
        $requiredNumber = $reflMethod->getNumberOfRequiredParameters();
        if ( count($args) < $requiredNumber ) {
            throw new CommandArgumentNotEnoughException($this, count($args), $requiredNumber);
        }

        $event = $this->getApplication()->getEventService();

        // runs the global triggers
        $event->trigger('execute.before');

        $event->trigger('execute');
        foreach ($this->extensions as $extension) { 
            $extension->execute();
        }

        $ret = call_user_func_array(array($this,'execute'), $args);

        $event->trigger('execute.after');


        return $ret;
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
