<?php
/*
 * This file is part of the CLIFramework package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace CLIFramework;

use CLIFramework\Formatter;
use CLIFramework\ServiceContainer;
use CLIFramework\IO\Writer;

class Logger
{
    /*
     * log level
     *
     * critical error = 1
     * error          = 2
     * warn           = 3
     * info           = 4
     * info2          = 5
     * debug          = 6
     * debug2         = 7
     * */
    public $logLevels = array(
        'critical' => 1,
        'error'    => 2,
        'warn'     => 3,
        'info' => 4,
        'info2' => 5,
        'debug' => 6,
        'debug2' => 7,
    );

    public $levelStyles = array(
        'critical' => 'strong_red',
        'error'    => 'strong_red',
        'warn'     => 'red',
        'info'     => 'green',
        'info2'    => 'green',
        'debug'    => 'white',
        'debug2'   => 'white',
    );

    /**
     * current level
     *
     * any message level lower than this will be displayed.
     * */
    public $level = 4;

    protected $indent = 0;

    protected $indentCharacter = '  ';

    /**
     * foramtter class
     *
     * @var CLIFramework\Formatter
     */
    public $formatter;

    /**
     * writer class
     *
     * @var CLIFramework\IO\Writer
     */
    private $writer;

    public function __construct(ServiceContainer $container = null)
    {
        $container = $container ?: ServiceContainer::getInstance();
        $this->formatter = $container['formatter']; // new Formatter;
        $this->writer = $container['writer'];
    }

    public function setLevel($level, $indent = 0)
    {
        $this->level = $level;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function quiet()
    {
        $this->level = 0;
    }

    public function setAbsoluteQuiet()
    {
        $this->level = 0;
    }

    public function setQuiet()
    {
        $this->level = 2;
    }

    public function setVerbose()
    {
        $this->level = $this->getLevelByName('info2');
    }

    public function isVerbose()
    {
        return $this->level == $this->getLevelByName('info2');
    }

    public function setDebug()
    {
        $this->level = $this->getLevelByName('debug2');
    }

    public function isDebug()
    {
        return $this->level == $this->getLevelByName('debug2') || $this->level == $this->getLevelByName('debug');
    }

    public function isQuiet()
    {
        return $this->level == 2;
    }


    /**
     * Set formatter object
     *
     * @param Formatter $formatter
     */
    public function setFormatter(Formatter $formatter)
    {
        $this->formatter = $formatter;
    }

    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * Set writer object
     *
     * @param Writer $writer
     */
    public function setWriter(Writer $writer)
    {
        $this->writer = $writer;
    }

    public function indent($level = 1)
    {
        $this->indent += $level;
    }

    public function unIndent($level = 1)
    {
        $this->indent = max(0, $this->indent - $level);
    }

    public function resetIndent()
    {
        $this->indent = 0;
    }

    /**
     * error method write message to STDERR
     *
     * @param string $msg
     */
    public function error($msg)
    {
        $level = $this->getLevelByName('error');
        $style = $this->getStyleByName('error');
        if ($level > $this->level) {
            return;
        }
        if ($this->level <= 4 && $level >= 4) {
            $style = 'dim';
        }
        fprintf(STDERR, str_replace('%', '%%', $this->formatter->format($msg, $style)) . PHP_EOL);
    }

    public function __call($method, $args)
    {
        $msg = $args[0];
        $indent = isset($args[1]) ? $args[1] : 0;
        $level = $this->getLevelByName($method);
        $style = $this->getStyleByName($method);
        if ($level > $this->level) {
            // do not print.
            return;
        }

        if ($this->level <= 4 && $level >= 4) {
            $style = 'dim';
        }

        if ($this->indent) {
            $this->writer->write(str_repeat($this->indentCharacter, $this->indent));
        }
        /* detect object */
        if (is_object($msg) || is_array($msg)) {
            $this->writer->writeln($this->formatter->format(print_r($msg, 1), $style));
        } else {
            $this->writer->writeln($this->formatter->format($msg, $style));
        }
    }

    /**
     * Write to console with given output format.
     *
     * $logger->writef('%s ... %s');
     *
     * @param string $format
     */
    public function writef($format)
    {
        $args = func_get_args();
        $this->writer->write(call_user_func_array('sprintf', $args));
    }

    /**
     * @param string $text text to write by `writer`
     */
    public function write($text)
    {
        $this->writer->write($text);
    }

    /**
     * @param string $text write text and append a newline charactor.
     */
    public function writeln($text)
    {
        $this->writer->writeln($text);
    }

    /**
     * Append a newline charactor to the console
     */
    public function newline()
    {
        $this->writer->writeln('');
    }

    /**
     * @param Exception $exception an exception to write to console.
     */
    public function logException(\Exception $exception)
    {
        echo $exception->getMessage();
        $this->newline();
    }

    /**
     * return the style of the given level name
     *
     * @param string $levelName
     */
    public function getStyleByName($levelName)
    {
        if (isset($this->levelStyles[$levelName])) {
            return $this->levelStyles[$levelName];
        }
    }



    /**
     * Return the log level name of the given level
     *
     * @param string $levelName
     */
    public function getLevelByName($levelName)
    {
        if (isset($this->logLevels[$levelName])) {
            return $this->logLevels[$levelName];
        }
    }

    public static function getInstance()
    {
        static $instance;
        return $instance ? $instance : $instance = new static;
    }
}
