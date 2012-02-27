CLIFramework
============

CLIFramework is a command-line application framework, for building flexiable, simple command-line applications.

In one CLIFramework application, each command is a class file, a command class can have many subcommands,

and each subcommand can also have their subcommands and arguments, options, and so on.

Commands and Subcommands can be registered from outside of an application or your plugins.

Defining a new command is pretty simple, all you need to is declare a class which is inherited from `CLIFramework\Command` class.

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

Command Forms
-------------

CLIFramework supports many command-line forms, for example:

    $ app [app-opts] [subcommand1] [subcommand1-opts] [subcommand2] [subcommand2-opts] .... [arguments] 

If the subcommand is not defined, you can still use the simple form:

    $ app [app-opts] [arguments]

For example,

    $ app db schema --clean dbname
    $ app gen controller --opt1 --opt2 ControllerName 

Requirement
-----------

* PHP5.3
* PSR-0 Autoloader
* pear.corneltek.com/Universal
* pear.corneltek.com/GetOptionKit


Installation
------------

From pear:

    $ pear channel-discover pear.corneltek.com
    $ pear install corneltek/CLIFramework

Or install through source:

    $ pear install -f package.xml

Tutorial
--------

To use CLIFramework, please define the application class first,

`src/YourApp/CLIApplication.php`:

```php
<?php
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
        $this->registerCommand( 'list', '\YourApp\Command\ListCommand' );
        $this->registerCommand( 'foo', '\YourApp\Command\FooCommand' );
        $this->registerCommand( 'bar' );    // initialize with \YourApp\Command\BarCommand
    }

}
```

Then define your command class:

`src/YourApp/Command/ListCommand.php`:

```php
<?php
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
        $logger = $this->getLogger();

        $logger->info('execute');
        $logger->error('error');

        $input = $this->prompt('Please type something');

    }
}
```

To start your Application:

```php
<?php

// include your PSR-0 autoloader to load classes here...
$app = new \YourApp\Application;
$app->run( $argv );
```

Example
-------
Please check `example/demo.php`

    $ php example/demo.php


Message style format
--------------------

```php
<?php
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



Todo
----
* autocompleter.
* exception renderer.
* alias
* interact
