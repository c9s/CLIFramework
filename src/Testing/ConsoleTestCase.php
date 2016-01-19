<?php
namespace CLIFramework\Testing;

class ConsoleTestCase extends \PHPUnit_Framework_TestCase
{
    protected function runScript($path, $input, $callback)
    {
        $descriptors = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w')
        );
        $pipes = array();
        $process = proc_open("php $path", $descriptors, $pipes, null, $_ENV);

        $this->assertTrue(is_resource($process));

        fwrite($pipes[0], $input);
        fflush($pipes[0]);
        $content = trim(stream_get_contents($pipes[1]));
        @fclose($pipes[0]);
        @fclose($pipes[1]);
        @fclose($pipes[2]);
        @pclose($process);
        $callback($content);
    }
}
