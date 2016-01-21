<?php
namespace CLIFramework\Component\Progress;
use Exception;
use CLIFramework\Formatter;
use CLIFramework\ConsoleInfo\EnvConsoleInfo;
use CLIFramework\ConsoleInfo\ConsoleInfoFactory;
use CLIFramework\Component\Progress\ETACalculator;
use CLIFramework\Ansi\Colors;

class LaserProgressBarStyle extends ProgressBarStyle
{
    public $leftDecorator = "❰";

    public $rightDecorator = "❱";

    public $barCharacter = '#';
}

class ProgressBar implements ProgressReporter
{
    protected $terminalWidth = 78;

    protected $formatter;

    protected $stream;

    protected $console;

    // protected $leftDecorator = "❰";
    protected $leftDecorator = "[";

    // protected $rightDecorator = "❱";
    protected $rightDecorator = "]";

    protected $columnDecorator = " | ";

    protected $barCharacter = '#';

    protected $descFormat = '%finished%/%total% %unit% | %percentage% | %eta_period%';

    protected $unit;

    protected $title;

    protected $start;

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
            $this->console = ConsoleInfoFactory::create();
            $this->updateLayout();
        }
    }

    public function updateLayout()
    {
        if ($this->console) {
            $this->terminalWidth = $this->console->getColumns();
        }
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    public function start($title = null)
    {
        if ($title) {
            $this->setTitle($title);
        }
        $this->start = microtime(true);
    }

    public function update($finished, $total)
    {
        $percentage = $total > 0 ? round($finished / $total, 2) : 0.0;

        $etaTime = ETACalculator::calculateEstimatedTime($finished, $total, $this->start, microtime(true));
        $etaPeriod = ETACalculator::calculateEstimatedPeriod($finished, $total, $this->start, microtime(true));
        $desc = str_replace([
            '%finished%', '%total%', '%unit%', '%percentage%', '%eta_time%', '%eta_period%',
        ], [
            $finished,
            $total,
            $this->unit,
            ($percentage * 100) . '%',
            'ETA: ' . ($etaTime ? date('H:i', $etaTime) : '--'),
            'ETA: ' . $etaPeriod,
        ], $this->descFormat);

        $barSize = $this->terminalWidth 
            - mb_strlen($desc) 
            - mb_strlen($this->leftDecorator) 
            - mb_strlen($this->rightDecorator)
            - mb_strlen($this->columnDecorator)
            ;

        if ($this->title) {
            $barSize -= (mb_strlen($this->title) + mb_strlen($this->columnDecorator));
        }

        $sharps = ceil($barSize * $percentage);

        $lightup = $finished % 10;

        fwrite($this->stream, "\r"
            . ( $this->title ? $this->title . $this->columnDecorator : "")
            . Colors::decorate($this->leftDecorator, $lightup ? 'purple' : 'light_purple')
            . Colors::decorate(str_repeat($this->barCharacter, $sharps), $lightup ? 'purple' : 'light_purple')
            . str_repeat(' ', $barSize - $sharps)
            . Colors::decorate($this->rightDecorator, $lightup ? 'purple' : 'light_purple')
            . $this->columnDecorator 
            . Colors::decorate($desc, $lightup ? 'light_gray' : 'white')
            );
    }

    public function finish($title = null)
    {
        if ($title) {
            $this->setTitle($title);
        }
        fwrite($this->stream, PHP_EOL);
    }
}

