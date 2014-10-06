<?php
namespace CLIFramework\Testing;
use PHPUnit_Framework_TestCase;

abstract class CommandTestCase extends PHPUnit_Framework_TestCase
{
    public $app;

    abstract public function setupApplication();

    public function getApplication()
    {
        return $this->app;
    }

    public function setUp()
    {
        $this->app = $this->setupApplication();
    }

    public function tearDown()
    {
        $this->app = NULL;
    }

    public function runCommand($args) {
        if (is_string($args)) {
            $args = preg_split('/\s+/',$args);
        }
        return $this->app->run($args);
    }
}
