<?php
namespace CLIFramework\Component\Progress;
use Exception;
use CLIFramework\Formatter;

class ProgressBar implements ProgressReporter
{

    public $done = false;

    public $terminalWidth = 78;

    public $formatter;

    public function __construct()
    {
        $this->formatter = new Formatter;
    }

    public function reset() {
        $this->done = false;
    }

    /**
     * 5.5.0 Added the cURL resource as the first argument to the CURLOPT_PROGRESSFUNCTION callback.
     */
    public function curlCallback($ch, $downloadSize, $downloaded, $uploadSize, $uploaded)
    {
        if ($this->done || $downloadSize == 0) {
            return;
        }
        $unit = 'B';
        if ($downloadSize > 1024 * 1024 ) {
            $unit = 'MB';
            $downloadSize /= (1024 * 1024.0);
            $downloaded /= (1024 * 1024.0);
        } elseif ($downloadSize > 1024) {
            $unit = 'KB';
            $downloadSize /= 1024.0;
            $downloaded /= 1024.0;
        }

        $barSize = $this->terminalWidth - 12;
        $percentage = ($downloaded > 0 && $downloadSize > 0 ? round($downloaded / $downloadSize, 2) : 0.0);
        $sharps = ceil($barSize * $percentage);

        fwrite(STDOUT,"\r"
            . $this->formatter->format('[','strong_white')
            . str_repeat('=', $sharps)
            . str_repeat(' ', $barSize - $sharps )
            . $this->formatter->format(']','strong_white')
            . sprintf( ' %.2f/%.2f%s %2d%%', $downloaded, $downloadSize, $unit, $percentage * 100 )
            );

        if ($downloadSize === $downloaded && $downloadSize > 0) {
            $this->done = true;
            fwrite(STDOUT,"\n");
        }
    }
}

