<?php
namespace CLIFramework\Component\Progress;

class ProgressStar implements ProgressReporter
{
    public $stars = array('-','\\','|','/');

    public $i = 0;

    public $url;

    public $done = false;

    public function prettySize($bytes)
    {
        if ($bytes > 1000000) {
            return round( $bytes / 1000000, 2) . 'M';
        }
        elseif ($bytes > 1000) {
            return round($bytes / 1000, 2) . 'K';
        }
        return round($bytes,2) . 'B';
    }

    public function reset() {
        $this->done = false;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function curlCallback($ch, $downloadSize, $downloaded, $uploadSize, $uploaded)
    {
        /* 4kb */
        if ($this->done || $downloadSize == 0) {
            return;
        }

        // printf("%s % 4d%%", $s , $percent );
        if ($downloadSize != 0 && $downloadSize === $downloaded) {
            $this->done = true;
            printf("\r\t%-60s                           \n",$this->url);
        } else {
            $percent = ($downloaded > 0 ? (float) ($downloaded / $downloadSize) : 0.0 );
            if( ++$this->i > 3 )
                $this->i = 0;

            /* 8 + 1 + 60 + 1 + 1 + 1 + 6 = */
            printf("\r\tFetching %-60s %s % -3.1f%% %s", $this->url,
                $this->stars[ $this->i ], 
                $percent * 100, $this->prettySize($downloaded) );
        }
    }
}


