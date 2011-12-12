CLIFramework
============

Command Forms
-------------

CLIFramework supports many command-line forms, for example:

    $ app [app-opts] [subcommand1] [subcommand1-opts] [subcommand2] [subcommand2-opts] .... [arguments] 

If the subcommand is not defined, you can still use the simple form:

    $ app [app-opts] [arguments]


Tutorial
--------

To use CLIFramework, please define the application class first:

src/YourApp/CLIApplication.php

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
        }

    }

Then define your command class:

src/YourApp/Command/ListCommand.php

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

        function execute($arguments)
        {

        }
    }

To start your Application:

    <?php
    $app = new \YourApp\Application;
    $app->run( $argv );
