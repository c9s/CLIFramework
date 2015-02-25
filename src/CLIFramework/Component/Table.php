<?php
namespace CLIFramework\Component;

class MarkdownTableStyle {
    public $cellPadding = 1;
    public $cellPaddingChar = ' ';
    public $verticalBorderChar = '|';
}



/**
 * RowSeparator is a slight separator for separating distinct rows...
 */
class RowSeparator {  }

/**
 * TableSeparator is more likely a section separator, the style is customizable.
 */
class TableSeparator {  }



/**
 * Feature:
 * 
 * - Support column wrapping if the cell text is too long.
 * - Table style
 */
class Table
{

    /**
     * @var string[] the rows are expanded by lines
     */
    protected $rows = array();

    /**
     * @var inteager[] contains the real row index
     */
    protected $rowIndex = array();

    protected $columnWidths = array();

    protected $headers = array();

    protected $style;

    protected $numberOfColumns;

    protected $wrapWidth = 50;

    /**
     * @var bool strip the white spaces from the begining of a 
     * string and the end of a string.
     */
    protected $trimSpaces = true;

    protected $trimLeadingSpaces = false;

    protected $trimTrailingSpaces = false;

    public function __construct() {
        $this->style = new MarkdownTableStyle;
    }

    public function setHeaders($headers) {
        $this->headers = $headers;
    }

    /**
     * Gets number of columns for this table.
     *
     * @return int
     */
    private function getNumberOfColumns()
    {
        if (null !== $this->numberOfColumns) {
            return $this->numberOfColumns;
        }

        $columns = array(count($this->headers));
        foreach ($this->rows as $row) {
            $columns[] = count($row);
        }
        return $this->numberOfColumns = max($columns);
    }

    public function addRow($row) {
        $this->rows[] = $row;

        // $keys = array_keys($this->rows);
        $lastRowIdx = count($this->rows) - 1;

        $this->rowIndex[$lastRowIdx] = 1;

        $cells = array_values($row);
        foreach ($cells as $col => $cell) {
            $lines = explode("\n",$cell);

            // do wrap if need
            $maxLineWidth = max(array_map('mb_strlen', $lines));
            if ($maxLineWidth > $this->wrapWidth) {
                $cell = wordwrap($cell, $this->wrapWidth, "\n");
                // re-explode the lines
                $lines = explode("\n",$cell);
            }

            $extraRowIdx = $lastRowIdx;
            foreach($lines as $line) {
                // trim the leading space
                if ($this->trimSpaces) {
                    $line = trim($line);
                } else {
                    if ($this->trimLeadingSpaces) {
                        $line = ltrim($line);
                    }
                    if ($this->trimTrailingSpaces) {
                        $line = rtrim($line);
                    }
                }

                if (isset($this->rows[$extraRowIdx])) {
                    $this->rows[$extraRowIdx][ $col ] = $line;
                } else {
                    $this->rows[$extraRowIdx] = array($col => $line);
                }
                $extraRowIdx++;
            }
        }
        return $this;
    }

    public function getColumnWidth($col) {
        $lengths = array();
        foreach($this->rows as $row) {
            if (isset($row[$col])) {
                $lengths[] = mb_strlen($row[$col]);
            }
        }


        return $this->columnWidth[$col] = max($lengths);
    }

    public function renderRow($rowIndex, $row) {
        $out = $this->style->verticalBorderChar;
        $columnNumber = $this->getNumberOfColumns();
        for ($c = 0 ; $c < $columnNumber ; $c++) {
            if (isset($row[$c])) {
                $cell = $row[$c];
            } else {
                $cell = '';
            }
            $width = $this->getColumnWidth($c);

            if (function_exists('mb_strlen') && false !== $encoding = mb_detect_encoding($cell)) {
                $width += strlen($cell) - mb_strlen($cell, $encoding);
            }

            $out .= str_repeat($this->style->cellPaddingChar, $this->style->cellPadding);
            $out .= str_pad($cell, $width, ' ');
            $out .= str_repeat($this->style->cellPaddingChar, $this->style->cellPadding);
            $out .= $this->style->verticalBorderChar;

        }
        if ($rowIndex > 0 && isset($this->rowIndex[$rowIndex])) {
            return $this->renderSeparator($rowIndex, $row) . $out . "\n";
        } else {
            return $out . "\n";
        }
    }

    public function renderSeparator($rowIndex, $row) {
        $columnNumber = $this->getNumberOfColumns();

        $out = '+';
        for ($c = 0 ; $c < $columnNumber ; $c++) {
            $columnWidth = $this->getColumnWidth($c);

            $out .= str_repeat('-', $columnWidth + $this->style->cellPadding * 2);
            $out .= '+';
        }
        return $out . "\n";
    }

    public function render() {
        $out = '';
        foreach($this->rows as $rowIndex => $row) {
            $out .= $this->renderRow($rowIndex, $row);
        }
        return $out;
    }

}




