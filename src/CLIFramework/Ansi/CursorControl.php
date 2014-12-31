<?php
namespace CLIFramework\Ansi;

/**
 * @codeCoverageIgnore
 */
class CursorControl
{

    /**
     * Sets the cursor position where subsequent text will begin. If no 
     * row/column parameters are provided (ie. <ESC>[H), the cursor will move 
     * to the home position, at the upper left of the screen.
     */
    static public function home($row, $col) {
        echo "\e[{$row};{$column}H";
    }


    static public function up($count = 1) {
        echo "\e[{$count}A";
    }

    static public function down($count = 1) {
        echo "\e[{$count}B";
    }

    static public function forward($count = 1) {
        echo "\e[{$count}C";
    }

    static public function backward($count = 1) {
        echo "\e[{$count}D";
    }

    /**
     * Force Cursor Position
     *
     * Identical to Cursor Home.
     */
    static public function position($row, $column) {
        echo "\e[{$row},{$column}f";
    }

    /**
     * Save Cursor & Attrs
     *
     * Save current cursor position.
     */
    static public function save($attrs = true) {
        if ($attrs) {
            echo "\e7";
        }
        echo "\e[s";
    }


    /**
     * Restore Cursor & Attrs
     *
     * Restores cursor position after a Save Cursor.
     */
    static public function restore($attrs = true) {
        if ($attrs) {
            echo "\e8";
        }
        echo "\e[u";
    }

    static public function hide()
    {
        echo "\e[?25l";
    }

    static public function show()
    {
        echo "\e[?25h";
    }
}



