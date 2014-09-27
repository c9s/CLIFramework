CLIFramework
============

[![Build Status](https://travis-ci.org/c9s/CLIFramework.png?branch=master)](https://travis-ci.org/c9s/CLIFramework)

CLIFramework is a command-line application framework, for building flexiable, simple command-line applications.

In one CLIFramework application, each command is a class file, a command class can have many subcommands,

and each subcommand can also have their subcommands and arguments, options, and so on.

Commands and Subcommands can be registered from outside of an application or your plugins.

Defining a new command is pretty simple, all you need to is declare a class which is inherited from `CLIFramework\Command` class.

Features
--------------------

- Intuitive command class and option spec
- GetOpt supported, powered by GetOptionKit. supports long option, short option, required|optional|default value.
- Automatic zsh completion generator.
- Hierarchical subcommand support.

Synopsis
--------------------

```php
class CommitCommand extends CLIFramework\Command {

    public function brief() { return 'brief of bar'; }

    public function options($opts) {
        $opts->add('a|all','Tell the command to automatically stage files that have been modified and deleted, but new files you have not told Git about are not affected.');

        $opts->add('p|patch','Use the interactive patch selection interface to chose which changes to commit. See git-add(1) for details.');

        $opts->add('C|reuse-message:','Take an existing commit object, and reuse the log message and the authorship information (including the timestamp) when creating the commit.')
            ->isa('string')
            ->validValues([ '50768ab', 'c2efdc2', 'ed5ba6a', 'cf0b1eb'])
            ;

        $opts->add('c|reedit-message:','like -C, but with -c the editor is invoked, so that the user can further edit the commit message.')
            ->isa('string')
            ->validValues([ '50768ab', 'c2efdc2', 'ed5ba6a', 'cf0b1eb'])
            ;

        $opts->add('author:', 'Override the commit author. Specify an explicit author using the standard A U Thor <author@example.com> format.')
            ->suggestions([ 'c9s', 'foo' , 'bar' ])
            ;
    }

    public function arguments($args) {
        $args->add('user')
            ->validValues(['c9s','bar','foo']);
        $args->add('repo')
            ->validValues(['CLIFramework','GetOptionKit']);
    }

    public function execute($user,$repo) {
        $this->getLogger()->notice('executing bar command.');
    }
}
```

Automatic Zsh Completion Generator:

![Imgur](http://imgur.com/sU3mrDe.gif)

With Lazy Completion Values:

![Imgur](http://i.imgur.com/ItYGDIu.gif)


Command Forms
---------------------

CLIFramework supports many command-line forms, for example:

    $ app [app-opts] [subcommand1] [subcommand1-opts] [subcommand2] [subcommand2-opts] .... [arguments] 

If the subcommand is not defined, you can still use the simple form:

    $ app [app-opts] [arguments]

For example,

    $ app db schema --clean dbname
    $ app gen controller --opt1 --opt2 ControllerName 

Subcommand Hierarchy
------------------------

Commands have methods for stages, like `prepare`, `execute`, `finish`, for a command like below:

    $ app foo_cmd bar_cmd arg1 arg2 arg3

The call graph is like:

    app->run
    - app->prepare
      - foo_cmd->prepare
        - bar_cmd->prepare
        - bar_cmd->execute
        - bar_cmd->finish
      - foo_cmd->finish
    - app->finish



Requirement
-----------

* PHP5.3

Installation
------------

From composer

```json
{
    "require": {
        "corneltek/cliframework": "*"
    }
}
```

Or install from repository:

    $ git clone http://github.com/c9s/CLIFramework.git
    $ pear install -f package.xml

Tutorial
--------

To use CLIFramework, please define the application class first,

`src/YourApp/CLIApplication.php`:

```php
namespace YourApp;
use CLIFramework\Application;

class CLIApplication extends Application
{

    /* init your application options here */
    function options($opts)
    {
        $opts->add('v|verbose', 'verbose message');
        $opts->add('path:', 'required option with a value.');
        $opts->add('path?', 'optional option with a value');
        $opts->add('path+', 'multiple value option.');
    }

    /* register your command here */
    function init()
    {
        $this->addCommand( 'list', '\YourApp\Command\ListCommand' );
        $this->addCommand( 'foo', '\YourApp\Command\FooCommand' );
        $this->addCommand( 'bar' );    // initialize with \YourApp\Command\BarCommand
    }

}
```

Then define your command class:

`src/YourApp/Command/ListCommand.php`:

```php
namespace YourApp\Command;
use CLIFramework\Command;
class ListCommand extends Command {

    function init()
    {
        // register your subcommand here ..
    }

    function options($opts)
    {
        // command options

    }

    function execute($arg1,$arg2,$arg3 = 0)
    {
        $logger = $this->logger;

        $logger->info('execute');
        $logger->error('error');

        $input = $this->ask('Please type something');

    }
}
```

To start your Application:

```php
// include your PSR-0 autoloader to load classes here...
$app = new \YourApp\Application;
$app->run( $argv );
```

Defining Command Argument Info
--------------------------------
In order to provide more information about your command, and generate
meaningful completion script, CLIFramework provides a way for you to define the
argument info of a command:

```php
namespace YourApp\Command;
use CLIFramework\Command;
class FooCommand extends Command {
    public function arguments() {
        $this->arg('name')->desc('name parameter')->suggests([ 'c9s', 'foo', 'bar' ]);
        $this->arg('email')->desc('email parameter');
        $this->arg('phone')->desc('phone parameter')->optional();
    }
}
```


Zsh Completion Generator
----------------------------

```sh
example/demo _zsh demo > _demo
source _demo
```

```sh
demo <TAB>
```

![Imgur](http://imgur.com/BOZRFJT.png)

![Imgur](http://imgur.com/AXUji1T.png)

![Imgur](http://imgur.com/bg2PPIF.png)

![Imgur](http://imgur.com/DLmzKD4.png)


Console Prompt (Readline)
-------------------------

simple prompt:

```php
$input = $this->ask("Your name please");
```

    $ php demo.php
    Your name please: 

prompt and except valid values:

```php
$input = $this->ask("Your name please", array('John', 'Pedro'));
```


Version Info
------------
CLIFrameword has a built-in --version option, to setup the version info, 
you can simply override a const in your application class to setup version string:


```php
class ConsoleApp extends CLIFramework\Application
{
    const NAME = 'YourApp';
    const VERSION = '1.2.1';
}
```

This shows:

    $ yourapp.php --version
    YourApp - version 1.2.1


Example
-------
Please check `example/demo.php`

    $ php example/demo.php



ArgumentEditor
----------------------

```php
use CLIFramework\ArgumentEditor\ArgumentEditor;

$editor = new ArgumentEditor(array('./configure','--enable-debug'));
$editor->append('--enable-zip');
$editor->append('--with-sqlite','--with-postgres');

echo $editor;
# ./configure --enable-debug --enable-zip --with-sqlite --with-postgres
```

Message style formatter
--------------------

```php
$formatter = new CLIFramework\Formatter;
$formatter->format( 'message' , 'green' );
```

Built-in styles:

    'red'          => array('fg' => 'red'),
    'green'        => array('fg' => 'green'),
    'white'        => array('fg' => 'white'),
    'yellow'       => array('fg' => 'yellow'),
    'strong_red'   => array('fg' => 'red', 'bold'  => 1),
    'strong_green' => array('fg' => 'green','bold' => 1),
    'strong_white' => array('fg' => 'white','bold' => 1),


Prompter Component
------------------


```php
$prompter = new CLIFramework\Prompter;

$prompter->style = 'strong_white';

$value = $prompter->ask( "Please enter your email" );

$value = $prompter->ask( "Apple or Banana" , array( 'Apple', 'Banana' ) );
```


Chooser Component
-----------------

```php
$chooser = new CLIFramework\Chooser;
$value = $chooser->choose( "System Options" , array( 
    'use php-5.4.0' => '5.4.0',
    'use php-5.4.1' => '5.4.1',
    'use system' => '5.3.0',
));
```

Todos in the next release
-------------------------
[ ] provide a easy way to define chained commands
[ ] inheritable options for subcommands.
[ ] bash completion generator
[ ] human readable exception renderer.
[ ] command alias
[ ] interact utilities

Hacking
=======

Setup
-------

1. Download & install Onion from http://github.com/c9s/Onion

2. Use Onion to bundle the dependencies:

    $ onion bundle

3. Run tests, it should pass.

4. Hack hack hack.

5. Run tests.

6. Send a pull request.


How command class register works
--------------------------------

- CLIApplication is inherited from CommandBase.
- Command is also inherited from CommandBase.
- To register a subcommand, we use the `addCommand` method to register commands or subcommands.
    - The command class is optional, if command class name is omitted, then the `addCommand` method
      will try to guess the *real* command class, and try to load the command class.


