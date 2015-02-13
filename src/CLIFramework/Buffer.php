<?php
namespace CLIFramework;

// TODO: refactoring this to a standalone package.

class Buffer {
    public $content = '';

    public $indent = 0;

    protected $indentCache = '';

    public $indentChar = '  ';

    public $format;

    const FORMAT_UNIX = 0;
    const FORMAT_DOS = 1;

    public $newline = "\n";

    public function __construct($content = '') {
        $this->content = $content;
        $this->format = self::FORMAT_UNIX;
    }

    public function indent() {
        $this->indent++;
        $this->_updateIndentCache();
    }

    /**
     * Set indent level
     *
     * @param integer $indent
     */
    public function setIndent($indent) {
        $this->indent = $indent;
        $this->_updateIndentCache();
    }

    /**
     * Get current indent level
     *
     * @return integer
     */
    public function getIndent() {
        return $this->indent;
    }

    public function setIndentChar($char) {
        $this->indentChar = $char;
    }

    public function getIndentChar() {
        return $this->indentChar;
    }

    public function unindent() {
        if ($this->indent > 0) {
            $this->indent--;
            $this->_updateIndentCache();
        }
    }

    public function _updateIndentCache() {
        $this->indentCache = str_repeat($this->indentChar, $this->indent);
    }

    public function makeIndent($level) {
        return str_repeat($this->indentChar, $level);
    }

    /**
     * Append a text to the buffer
     */
    public function append($text)
    {
        $this->content .= $text;
    }


    /**
     * Append a line with indent to the buffer
     *
     * @param string $line
     * @param integer $indent
     */
    public function appendLine($line, $indent = 0) {
        $this->content .= ($indent ? $this->makeIndent($indent) : $this->indentCache) . $line . $this->newline;
    }


    /**
     * Append multiple lines with indent to the buffer
     *
     * @param string[] $lines
     * @param integer $indent
     */
    public function appendLines($lines, $indent = 0) {
        foreach($lines as $line) {
            $this->appendLine($line, $indent);
        }
    }

    /**
     * Append a string and escape with charlist
     *
     * @param string $line
     * @param string $charlist
     */
    public function appendEscape($line, $charlist) {
        $this->content .= addcslashes($line, $charlist);
    }

    /**
     * Append a string with addslashes function to the buffer
     *
     * @param string $line
     */
    public function appendEscapeSlash($line) {
        $this->content .= addslashes($line);
    }

    /**
     * Append a string with double quotes 
     *
     * @param string $str
     */
    public function appendQuoteString($str) {
        $this->content .= '"' . addcslashes($str , '"') . '"';
    }


    /**
     * Append a string with single quotes
     *
     * @param string $str
     */
    public function appendSingleQuoteString($str) {
        $this->content .= "'" . addslashes($str) . "'";
    }


    /**
     * Append a new line to the buffer
     */
    public function newLine() {
        $this->content .= $this->newline;
    }


    /**
     * Set line format
     *
     * @param integer $format Buffer::FORMAT_UNIX or Buffer::FORMAT_DOS
     */
    public function setFormat($format) {
        $this->format = $format;
        if ($this->format == self::FORMAT_UNIX) {
            $this->newline = "\n";
        } elseif ($this->format == self::FORMAT_DOS) {
            $this->newline = "\r\n";
        }
    }


    /**
     * Append a string block (multilines)
     *
     * @param string $block
     * @param integer $indent = 0
     */
    public function appendBlock($block, $indent = 0) {
        $lines = explode("\n", $block);
        foreach($lines as $line) {
            $this->appendLine($line, $indent);
        }
    }


    /**
     * Append a buffer object
     *
     * @param Buffer $buf
     * @param integer $indent = 0
     */
    public function appendBuffer(Buffer $buf, $indent = 0) {
        if ( $indent ) {
            $this->setIndent($indent);
            $lines = $buf->lines();
            foreach( $lines as $line ) {
                $this->appendLine($line);
            }
        } else {
            $this->content .= $buf->__toString();
        }
    }


    /**
     * Split buffer content into lines
     *
     * @return string[] lines
     */
    public function lines() {
        return $this->explode($this->newline, $this->content);
    }

    /**
     * Output the buffer as a string
     */
    public function __toString() {
        return $this->content;
    }
}

