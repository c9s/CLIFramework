<?php
namespace CLIFramework\IO;

/**
 * @code
 *  $writer = new StreamWriter(STDERR);
 */
class StreamWriter implements Writer
{
    private $stream;

    public function __construct($stream)
    {
        if (!is_resource($stream)) {
            throw new \RuntimeException("invalid stream");
        }

        $this->stream = $stream;
    }

    public function write($text)
    {
        fwrite($this->stream, $text);
    }

    public function writeln($text)
    {
        fwrite($this->stream, $text . "\n");
    }

    public function writef($format)
    {
        $args = func_get_args();
        $this->write(call_user_func_array('sprintf', $args));
    }
}
