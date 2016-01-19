<?php
namespace CLIFramework\Component\Progress;
use Exception;
use CLIFramework\Formatter;
use CLIFramework\ConsoleInfo\EnvConsoleInfo;
use CLIFramework\ConsoleInfo\ConsoleInfoFactory;

class ProgressBar implements ProgressReporter
{
    protected $terminalWidth = 78;

    protected $formatter;

    protected $stream;

    protected $console;

    protected $decoratorLeft = '[';

    protected $decoratorRight = ']';

    protected $barCharacter = '=';

    protected $progressDescFormat = ' %d/%d %2d%%';

    public function __construct($stream)
    {
        $this->stream = $stream;
        $this->formatter = new Formatter;
        if ($this->console = ConsoleInfoFactory::create()) {
            $this->terminalWidth = $this->console->getColumns();
        }
    }

    public function update($finished, $total)
    {
        $percentage = $total > 0 ? round($finished / $total, 2) : 0.0;
        $desc = sprintf($this->progressDescFormat, $finished, $total, $percentage * 100);

        $barSize = $this->terminalWidth - strlen($desc) - strlen($this->decoratorLeft) - strlen($this->decoratorRight);
        $sharps = ceil($barSize * $percentage);

        fwrite($this->stream, "\r"
            . $this->formatter->format($this->decoratorLeft,'strong_white')
            . str_repeat($this->barCharacter, $sharps)
            . str_repeat(' ', $barSize - $sharps)
            . $this->formatter->format($this->decoratorRight,'strong_white')
            . $desc
            );
    }

    public function finish()
    {
        fwrite($this->stream, PHP_EOL);
    }
}

