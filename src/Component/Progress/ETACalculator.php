<?php
namespace CLIFramework\Component\Progress;

class ETACalculator
{
    protected $start;

    public function __construct()
    {
        $this->start = microtime(true);
    }

    public function start()
    {
        $this->start = microtime(true);
    }

    public function calculateRemainingSeconds($proceeded, $total)
    {
        $now = microtime(true);
        $secondDiff = ($now - $this->start);
        $speed = $proceeded / $secondDiff;
        $remaining = $total - $proceeded;
        $remainingSeconds = $remaining/$speed;
        return $remainingSeconds;
    }

    public function calculate($proceeded, $total)
    {
        $remainingSeconds = $this->calculateRemainingSeconds($proceeded, $total);
        $etaTimestamp = microtime(true) + $remainingSeconds;
        return $etaTimestamp;
    }
}




