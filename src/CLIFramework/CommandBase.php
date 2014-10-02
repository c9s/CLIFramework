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
use CLIFramework\Exception\CommandNotFoundException;
use CLIFramework\Exception\InvalidCommandArgumentException;
use CLIFramework\Exception\CommandArgumentNotEnoughException;
use CLIFramework\ArgInfo;
use CLIFramework\ArgInfoList;

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




    public function aliases() {
        // methods for user to define alias.
    }

    public function addAlias($alias, $cmdName) {
        $this->aliases[$alias] = $cmdName;
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
        return $this->addCommand($command,$class);
    }

    public function setParent($parent)
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
     * register command to application, in init() method stage,
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
                throw Exception("Command class $class not found.");
        } else {
            if ($this->parent) {
                // get class name by subcommand rules.
                $class = $this->getLoader()->loadSubcommand($command,$this);
            } else {
                // get class name by command rules.
                $class = $this->getLoader()->load($command);
            }
        }
        if ( ! $class ) {
            throw new Exception("command class $class for command $command not found");
        }
        return $this->commands[ $command ] = $class;
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
        return isset($this->commands[$command]);
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
     * Return the command class name by command name
     *
     * @param  string $command command name.
     * @return string command class.
     */
    public function getCommandClass($command)
    {
        // translate alias to actual command name.
        if ( isset($this->aliases[$command]) ) {
            $command = $this->aliases[$command];
        }
        if ( isset($this->commands[ $command ]) ) {
            return $this->commands[ $command ];
        }
    }


    /**
     * Return the command name stack
     *
     * @return string[]
     */
    public function getCommandNameStack() {
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
        return join('.', $this->getCommandNameStack());
    }


    /**
     * Return the objects of all sub commands.
     *
     * @return Command[]
     */
    public function getCommandObjects() 
    {
        $cmds = array();
        foreach( $this->commands as $n => $cls ) {
            $cmd = $this->createCommand($cls);
            $cmds[ $n ] = $cmd;
        }
        return $cmds;
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
        if ( $commandClass = $this->getCommandClass($commandName) ) {
            return $this->createCommand($commandClass);
        }
        throw new CommandNotFoundException($commandName);
    }

    public function guessCommand($commandName) {
        // array of words to check against
        $words = array_keys($this->commands);

        // no shortest distance found, yet
        $shortest = -1;

        // loop through words to find the closest
        foreach ($words as $word) {

            // calculate the distance between the input word,
            // and the current word
            $lev = levenshtein($commandName, $word);

            // check for an exact match
            if ($lev == 0) {

                // closest word is this one (exact match)
                $closest = $word;
                $shortest = 0;

                // break out of the loop; we've found an exact match
                break;
            }

            // if this distance is less than the next found shortest
            // distance, OR if a next shortest word has not yet been found
            if ($lev <= $shortest || $shortest < 0) {
                // set the closest match, and shortest distance
                $closest  = $word;
                $shortest = $lev;
            }
        }
        if ($shortest == 0) {
            return array(0, $closest);
        }
        return array($shortest, $closest);
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

        // call_user_func_array(  );
        $ro = new ReflectionObject($this);

        if ( ! method_exists( $this,'execute' ) ) {
            throw new Exception('execute method is not defined.');
        }

        $method = $ro->getMethod('execute');
        $requiredNumber = $method->getNumberOfRequiredParameters();
        $parameters = $method->getParameters();
        foreach ($parameters as $param) {
            // TODO: add description to the argument
            $a = new ArgInfo($param->getName());
            if ($param->isOptional())
                $a->optional(true);
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
        // call_user_func_array(  );
        $refl = new ReflectionObject($this);

        if( ! method_exists( $this,'execute' ) )
            throw new Exception('execute method is not defined.');

        $reflMethod = $refl->getMethod('execute');
        $requiredNumber = $reflMethod->getNumberOfRequiredParameters();
        if ( count($args) < $requiredNumber ) {
            throw new CommandArgumentNotEnoughException($this, count($args), $requiredNumber);
            /*
            $this->getLogger()->error( "Command requires at least $requiredNumber arguments." );
            $this->getLogger()->error( "Command prototype:" );
            $params = $reflMethod->getParameters();
            foreach ($params as $param) {
                $this->getLogger()->error(
                    $param->getPosition() . ' => $' . $param->getName() , 1 );
            }
            throw new Exception('Wrong Parameter, Can not execute command.');
            */
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
        $prompter->style = 'ask';
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
        $chooser->style = 'choose';
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
