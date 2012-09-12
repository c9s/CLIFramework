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
use GetOptionKit\OptionSpecCollection;
use CLIFramework\Prompter;
use CLIFramework\Chooser;
use Exception;
use ReflectionObject;
use ArrayAccess;
use IteratorAggregate;

/**
 * Command based class
 *
 * register subcommands.
 */
abstract class CommandBase
    implements ArrayAccess, IteratorAggregate, CommandInterface
{


    /**
     * command class loader
     *
     * @var CLIFramework\CommandLoader
     */
    public $loader;

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
     * @var GetOptionKit\OptionResult parsed options
     */
    public $options;

    /**
     * parent commmand
     *
     * @var CLIFramework\CommandBase or CLIFramework\Application
     */
    public $parent;

    public $optionSpecs;

    public function __construct() {  }

    /**
     * Returns one line brief for this command.
     *
     * @return string brief
     */
    public function brief()
    {
        return 'your awesome brief.';
    }

    /**
     * Usage string  (one-line)
     *
     * @return string usage
     */
    public function usage()
    {
        // return usage
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
     * @param GetOptionKit\OptionSpecCollection Spec collection object.
     *
     * @see GetOptionKit\OptionSpecCollection
     */
    public function options($getopt)
    {

    }

    /**
     * init function
     *
     * register custom subcommand here
     *
     **/
    public function init()
    {

    }

    /**
     * A short alias for registerCommand method
     *
     * @param string $command
     * @param string $class
     */
    public function addCommand($command,$class = null)
    {
        return $this->registerCommand($command,$class);
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
    public function registerCommand($command,$class = null)
    {

        // try to load the class/subclass,
        // or generate command class name automatically.
        if ($class) {
            if( $this->loader->loadClass( $class ) === false )
                throw Exception("Command class not found.");
        } else {
            if ($this->parent) {
                // get class name by subcommand rules.
                $class = $this->loader->loadSubcommand($command,$this);
            } else {
                // get class name by command rules.
                $class = $this->loader->load($command);
            }
        }
        if( ! $class )
            throw new Exception("command class $class for command $command not found");
        return $this->commands[ $command ] = $class;
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
        return isset($this->commands[ $command ]);
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
        if( isset($this->commands[ $command ]) )

            return $this->commands[ $command ];
    }

    /*
     * get subcommand object from current command
     * by command name
     *
     * @param string $command
     *
     * @return Command initialized command object.
     */
    public function getCommand($command)
    {
        // keep scope here. (hate)
        if ( $commandClass = $this->getCommandClass($command) ) {
            return $this->createCommand($commandClass);
        }
        throw new Exception("command $command not found.");
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

        // check self
        if ( $this instanceof \CLIFramework\Application ) {
            $cmd = new $commandClass($this);
            $cmd->parent = $this;
        } else {
            $cmd = new $commandClass($this->application);
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

    /**
     * Get Option Results
     *
     * @return GetOptionKit\OptionSpecCollection command options object (parsed, and a option results)
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
    public function setOptions( $options )
    {
        $this->options = $options;
    }

    /**
     * Get Command-line Option spec
     *
     * @return GetOptionKit\OptionSpecCollection
     */
    public function getOptionSpec()
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
     * Execute command object, this is a wrapper method for execution.
     *
     * In this method, we check the command arguments by the Reflection feature
     * provided by PHP.
     *
     * @param  array $args command argument list (not associative array).
     * @return mixed the value of execution result.
     */
    public function executeWrapper($args)
    {
        // call_user_func_array(  );
        $refl = new ReflectionObject($this);

        if( ! method_exists( $this,'execute' ) )
            throw new Exception('execute method is not defined.');

        $reflMethod = $refl->getMethod('execute');
        $requiredNumber = $reflMethod->getNumberOfRequiredParameters();
        if ( count($args) < $requiredNumber ) {
            $this->getLogger()->error( "Command requires at least $requiredNumber arguments." );
            $this->getLogger()->error( "Command prototype:" );
            $params = $reflMethod->getParameters();
            foreach ($params as $param) {
                $this->getLogger()->error(
                    $param->getPosition() . ' => $' . $param->getName() , 1 );
            }
            throw new Exception('Wrong Parameter, Can not execute command.');
        }

        return call_user_func_array(array($this,'execute'), $args);
    }

    /**
     * Show prompt with message, you can provide valid options
     * for the simple validation.
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
    public function choose($prompt, $choices )
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
