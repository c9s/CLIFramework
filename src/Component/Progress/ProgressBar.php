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

    // protected $leftDecorator = '[';
    protected $leftDecorator = "❰";

    protected $rightDecorator = "❱";

    protected $barCharacter = '=';

    protected $descFormat = ' % 3d/% 3d %3d%%';

    public function __construct($stream, $container = null)
    {
        $this->stream = $stream;

        if ($container) {
            $this->formatter = $container['formatter'];
            if (isset($container['consoleInfo'])) {
                $this->console = $container['consoleInfo'];
            }
        } else {
            $this->formatter = new Formatter;
            if ($this->console = ConsoleInfoFactory::create()) {
                $this->terminalWidth = $this->console->getColumns();
            }
        }
    }

    public function update($finished, $total)
    {
        $percentage = $total > 0 ? round($finished / $total, 2) : 0.0;
        $desc = sprintf($this->descFormat, $finished, $total, $percentage * 100);

        $barSize = $this->terminalWidth - mb_strlen($desc) - mb_strlen($this->leftDecorator) - mb_strlen($this->rightDecorator);
        $sharps = ceil($barSize * $percentage);

        fwrite($this->stream, "\r"
            . $this->formatter->format($this->leftDecorator, 'strong_white')
            . str_repeat($this->barCharacter, $sharps)
            . str_repeat(' ', $barSize - $sharps)
            . $this->formatter->format($this->rightDecorator, 'strong_white')
            . $desc
            );
    }

    public function finish()
    {
        fwrite($this->stream, PHP_EOL);
    }
}

