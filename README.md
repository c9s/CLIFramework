CLIFramework
============

[![Build Status](https://travis-ci.org/c9s/php-CLIFramework.png?branch=master)](https://travis-ci.org/c9s/php-CLIFramework)

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
    $ pear install -a corneltek/CLIFramework

Or install from repository:

    $ git clone http://github.com/c9s/CLIFramework.git
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
        $logger = $this->logger;

        $logger->info('execute');
        $logger->error('error');

        $input = $this->ask('Please type something');

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

Console Prompt (Readline)
-------------------------

simple prompt:

```
    $input = $this->ask("Your name please");
```

    $ php demo.php
    Your name please: 

prompt and except valid values:

```
    $input = $this->ask("Your name please", array('John', 'Pedro'));
```


Version Info
------------
CLIFrameword has a built-in --version option, to setup the version info, 
you can simply override a const in your application class to setup version string:


```php
<?
class ConsoleApp extends CLIFramework\Application
{
    const name = 'YourApp';
    const version = '1.2.1';
}
```

This shows:

    $ yourapp.php --version
    YourApp - version 1.2.1





Example
-------
Please check `example/demo.php`

    $ php example/demo.php


Message style formatter
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


Prompter Component
------------------


```php
<?
    $prompter = new CLIFramework\Prompter;

    $prompter->style = 'strong_white';

    $value = $prompter->ask( "Please enter your email" );

    $value = $prompter->ask( "Apple or Banana" , array( 'Apple', 'Banana' ) );
```


Chooser Component
-----------------

```php
<?
    $chooser = new CLIFramework\Chooser;
    $value = $chooser->choose( "System Options" , array( 
        'use php-5.4.0' => '5.4.0',
        'use php-5.4.1' => '5.4.1',
        'use system' => '5.3.0',
    ));
```

Todo
----
* autocompleter.
* exception renderer.
* alias
* interact


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
- To register a subcommand, we use the `registerCommand` method to register commands or subcommands.
    - The command class is optional, if command class name is omitted, then the `registerCommand` method
      will try to guess the *real* command class, and try to load the command class.


