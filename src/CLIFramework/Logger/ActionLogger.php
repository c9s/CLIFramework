<?php

namespace CLIFramework\Logger;

use CLIFramework\Formatter;
use CLIFramework\Ansi\CursorControl;

class LogAction
{
    public $title;

    public $desc;

    public $status;

    protected $logger;

    protected $cursorControl;

    protected $actionColumnWidth = 38;

    public function __construct($logger, $title, $desc, $status = 'waiting')
    {
        $this->logger = $logger;
        $this->title = $title;
        $this->desc = $desc;
        $this->status = $status;

        $this->cursorControl = new CursorControl($this->logger->fd);
        $this->cursorControl->hide();
    }

    public function setStatus($status, $style = 'green')
    {
        $this->status = $status;
        $this->update($style);
    }

    public function setActionColumnWidth($width)
    {
        $this->actionColumnWidth = $width;
    }

    protected function update($style = 'green')
    {
        $padding = max($this->actionColumnWidth - strlen($this->title), 1);
        $buf = sprintf('  %s % -20s',
            $this->logger->formatter->format(sprintf('%s', $this->title), $style).str_repeat(' ', $padding),
            $this->status
        );
        fwrite($this->logger->fd, $buf."\r");
        fflush($this->logger->fd);
    }

    public function finalize()
    {
        fwrite($this->logger->fd, "\n");
        fflush($this->logger->fd);
        $this->cursorControl->show();
    }

    public function done()
    {
        $this->setStatus('done');
        $this->finalize();
    }
}

class ActionLogger
{
    public $fd;

    public $formatter;

    public function __construct($fd = null, $formatter = null)
    {
        $this->fd = $fd ?: fopen('php://stderr', 'w');
        $this->formatter = $formatter ?: new Formatter();
    }

    public function newAction($title, $desc = '', $status = 'waiting')
    {
        $logAction = new LogAction($this, $title, $desc);
        return $logAction;
    }
}
