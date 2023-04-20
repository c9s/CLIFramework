<?php
namespace CLIFramework\Testing;
use PHPUnit\Framework\TestCase;

#[\AllowDynamicProperties]
abstract class CommandTestCase extends TestCase
{
    public $app;

    public $outputBufferingActive = false;

    abstract public function setupApplication();

    public function getApplication()
    {
        return $this->app;
    }

    protected function setUp(): void
    {
        if ($this->outputBufferingActive) {
            ob_start();
        }
        $this->app = $this->setupApplication();
    }

    protected function tearDown(): void
    {
        $this->app = NULL;
        if ($this->outputBufferingActive) {
            ob_end_clean();
        }
    }

    public function runCommand($args) {
        if (is_string($args)) {
            $args = Parser::getArguments($args);
        }
        return $this->app->run($args);
    }
}
