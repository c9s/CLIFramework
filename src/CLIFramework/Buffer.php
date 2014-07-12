<?php
namespace CLIFramework;

// TODO: refactoring this to a standalone package.

class Buffer {
    public $content = '';

    public $indent = 0;

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
    }

    public function unindent() {
        if ($this->indent > 0)
            $this->indent--;
    }

    public function append($text) {
        $this->content .= $text;
    }

    public function appendRow($str) {
        $this->content .= $str;
    }

    public function appendLine($line, $indent = 0) {
        $this->content .= str_repeat(' ', $indent ? $indent : $this->indent) . $line . $this->newline;
    }

    public function appendLines($lines, $indent = 0) {
        foreach($lines as $line) {
            $this->appendLine($line, $indent);
        }
    }

    public function appendEscape($line, $escape) {
        $this->content .= addcslashes($line, $escape);
    }

    public function appendEscapeSlash($line) {
        $this->content .= addslashes($line);
    }

    public function newLine() {
        $this->content .= $this->newline;
    }

    public function setFormat($format) {
        $this->format = $format;
        if ($this->format == self::FORMAT_UNIX) {
            $this->newline = "\n";
        } elseif ($this->format == self::FORMAT_DOS) {
            $this->newline = "\r\n";
        }
    }

    public function appendBlock($block, $indent = 0) {
        $lines = explode("\n", $block);
        foreach($lines as $line) {
            $this->appendLine($line, $indent);
        }
    }

    public function setIndent($indent) {
        $this->indent = $indent;
    }

    public function getIndent() {
        return $this->indent;
    }

    public function __toString() {
        return $this->content;
    }
}

