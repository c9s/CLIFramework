<?php
namespace CLIFramework\Testing;

class ConsoleTestCase extends \PHPUnit\Framework\TestCase
{
    protected function runScript($path, $input, callable $callback)
    {
        $descriptors = array(
            0 => array('pipe', 'r'), // stdin
            1 => array('pipe', 'w'), // stdout
            2 => array('pipe', 'w') // stderr
        );

        $pipes = array();


        // $_ENV is only populated if php.ini allows it, which it doesn't seem to
        // do by default, at least not in the default WAMP server installation.
        //
        // 
        $php = PHP_BINARY;
        $command = "$php $path";

        $process = proc_open($command, $descriptors, $pipes);
        // $process = proc_open($command, $descriptors, $pipes, NULL, [ 'PATH' => getenv('PATH') ]);
        // $process = proc_open($command, $descriptors, $pipes, NULL, $_ENV);
        // $process = proc_open($command, $descriptors, $pipes, NULL, [ 'PATH' => getenv('PATH') ]);


        if ($process === false) {
            throw new \RuntimeException("failed to proc_open '$command'");
        }

        $this->assertTrue(is_resource($process), 'The returned value should be resource');

        fwrite($pipes[0], $input);
        fflush($pipes[0]);
        fclose($pipes[0]);

        $content = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $callback($content);
        $code = proc_close($process);
        if ($code !== 0) {
            throw new \RuntimeException("proc_close failed '$command', code: $code");
        }
    }
}
