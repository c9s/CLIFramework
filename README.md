CLIFramework
============

[![Build Status](https://travis-ci.org/c9s/CLIFramework.svg?branch=master)](https://travis-ci.org/c9s/CLIFramework)
[![Coverage Status](https://img.shields.io/coveralls/c9s/CLIFramework.svg)](https://coveralls.io/r/c9s/CLIFramework) 
[![Latest Stable Version](https://poser.pugx.org/corneltek/cliframework/v/stable.svg)](https://packagist.org/packages/corneltek/cliframework) 
[![Latest Unstable Version](https://poser.pugx.org/corneltek/cliframework/v/unstable.svg)](https://packagist.org/packages/corneltek/cliframework) 
[![Total Downloads](https://poser.pugx.org/corneltek/cliframework/downloads.svg)](https://packagist.org/packages/corneltek/cliframework) 
[![Monthly Downloads](https://poser.pugx.org/corneltek/cliframework/d/monthly)](https://packagist.org/packages/corneltek/cliframework)
[![License](https://poser.pugx.org/corneltek/cliframework/license.svg)](https://packagist.org/packages/corneltek/cliframework)


CLIFramework is a command-line application framework, for building flexiable, simple command-line applications.

Commands and Subcommands can be registered from outside of an application or your plugins.

Defining a new command is pretty simple, all you need to is declare a class which is inherited from `CLIFramework\Command` class.

Features
--------------------

- Intuitive command class and option spec

- command options are supported, powered by GetOptionKit. including long option, short option, required|optional|default value.

- Hierarchical commands.

- Automatic help page generation.

- Automatic zsh completion generator.

- Automatic bash completion generator.

- Friendly message when command arguments are not enough.

- Testable, CLIFramework provides PHPUnit test case for testing the commands in PHP.

- Argument validation, suggestion, 

- Command Groups

- HHVM compatible




Synopsis
--------------------

```php
class CommitCommand extends CLIFramework\Command {

    public function brief() { return 'brief of bar'; }

    public function options($opts) {
        $opts->add('C|reuse-message:','Take an existing commit object, and reuse the log message and the authorship information (including the timestamp) when creating the commit.')
            ->isa('string')
            ->valueName('commit hash')
            // ->validValues([ 'static-50768ab', 'static-c2efdc2', 'static-ed5ba6a', 'static-cf0b1eb'])
            ->validValues(function() {
                $output = array();
                exec("git rev-list --abbrev-commit HEAD -n 20", $output);
                return $output;
            })
            ;

        // Runtime completion by setting up a closure for completion
        $opts->add('c|reedit-message:','like -C, but with -c the editor is invoked, so that the user can further edit the commit message.')
            ->isa('string')
            ->valueName('commit hash')
            ->validValues(function() {
                // exec("git log -n 10 --pretty=format:%H:%s", $output);
                exec("git log -n 10 --pretty=format:%H:%s", $output);
                return array_map(function($line) {
                    list($key,$val) = explode(':',$line);
                    $val = preg_replace('/\W/',' ', $val);
                    return array($key, $val);
                }, $output);
            })
            ;

        $opts->add('author:', 'Override the commit author. Specify an explicit author using the standard A U Thor <author@example.com> format.')
            ->suggestions(array( 'c9s', 'foo' , 'bar' ))
            ->valueName('author name')
            ;

        $opts->add('output:', 'Output file')
            ->isa('file')
            ;
    }

    public function arguments($args) {
        $args->add('user')
            ->validValues(['c9s','bar','foo']);

        // Static completion result
        $args->add('repo')
            ->validValues(['CLIFramework','GetOptionKit']);

        // Add an argument info expecting multiple *.php files
        $args->add('file')
            ->isa('file')
            ->glob('*.php')
            ->multiple()
            ;
    }

    public function init() {

        $this->command('foo'); // register App\Command\FooCommand automatically

        $this->command('bar', 'WhatEver\MyCommand\BarCommand');

        $this->commandGroup('General Commands', ['foo', 'bar']);

        $this->commandGroup('Database Commands', ['create-db', 'drop-db']);

        $this->commandGroup('More Commands', [
            'foo' => 'WhatEver\MyCommand\FooCommand',
            'bar' => 'WhatEver\MyCommand\BarCommand'
        ]);
    }

    public function execute($user,$repo) {
        $this->logger->notice('executing bar command.');
        $this->logger->info('info message');
        $this->logger->debug('info message');
        $this->logger->write('just write');
        $this->logger->writeln('just drop a line');
        $this->logger->newline();

        return "Return result as an API"; // This can be integrated in your web application
    }
}
```


### Automatic Zsh Completion Generator

![Imgur](http://imgur.com/sU3mrDe.gif)

#### Zsh Completion With Lazy Completion Values:

![Imgur](http://i.imgur.com/ItYGDIu.gif)

#### Bash Completion

![Imgur](http://i.imgur.com/sF5UPX5.gif)


Documentation
-------------

See documentation on our wiki <https://github.com/c9s/CLIFramework/wiki>


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



Basic Requirement
-----------------

* PHP 5.3

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




Zsh Completion Generator
----------------------------

```sh
example/demo zsh demo > _demo
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


Building Phar Archive file
--------------------------

    COMPOSER=tests/fixture/composer.json.phar-test composer install
    php example/demo archive --working-dir /Users/c9s/work/php/CLIFramework \
            --composer tests/fixture/composer.json.phar-test \
            app.phar


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


Debug Utilities
-----------------------

### LineIndicator

```php
use CLIFramework\Debug\LineIndicator;
$indicator = new LineIndicator;
echo PHP_EOL, $indicator->indicateFile(__FILE__, __LINE__);
```


### ConsoleDebug class

```php
use CLIFramework\Debug\ConsoleDebug;

ConsoleDebug::dumpRows($pdo->fetchAll());

ConsoleDebug::dumpException($e);
```



Todos in the next release
-------------------------
[ ] provide a easy way to define chained commands
[ ] inheritable options for subcommands.
[ ] human readable exception renderer.
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




[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/c9s/cliframework/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

