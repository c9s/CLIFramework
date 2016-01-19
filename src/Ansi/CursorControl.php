<?php

namespace CLIFramework\Ansi;

/**
 * @codeCoverageIgnore
 */
class CursorControl
{
    protected $fd;

    public function __construct($fd = null)
    {
        $this->fd = $fd ?: fopen('php://stderr', 'w');
    }

    /**
     * Sets the cursor position where subsequent text will begin. If no 
     * row/column parameters are provided (ie. <ESC>[H), the cursor will move 
     * to the home position, at the upper left of the screen.
     */
    public function home($row, $col)
    {
        fwrite($this->fd, "\e[{$row};{$column}H");
    }

    public function up($count = 1)
    {
        fwrite($this->fd, "\e[{$count}A");
    }

    public function down($count = 1)
    {
        fwrite($this->fd, "\e[{$count}B");
    }

    public function forward($count = 1)
    {
        fwrite($this->fd, "\e[{$count}C");
    }

    public function backward($count = 1)
    {
        fwrite($this->fd, "\e[{$count}D");
    }

    /**
     * Force Cursor Position.
     *
     * Identical to Cursor Home.
     */
    public function position($row, $column)
    {
        fwrite($this->fd, "\e[{$row},{$column}f");
    }

    /**
     * Save Cursor & Attrs.
     *
     * Save current cursor position.
     */
    public function save($attrs = true)
    {
        if ($attrs) {
            fwrite($this->fd, "\e7");
        }
        fwrite($this->fd, "\e[s");
    }

    /**
     * Restore Cursor & Attrs.
     *
     * Restores cursor position after a Save Cursor.
     */
    public function restore($attrs = true)
    {
        if ($attrs) {
            fwrite($this->fd, "\e8");
        }
        fwrite($this->fd, "\e[u");
    }

    public function hide()
    {
        fwrite($this->fd, "\e[?25l");
    }

    public function show()
    {
        fwrite($this->fd, "\e[?25h");
    }
}
