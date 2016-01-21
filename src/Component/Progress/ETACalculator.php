<?php
namespace CLIFramework\Component\Progress;

class ETACalculator
{
    static public function calculateRemainingSeconds($proceeded, $total, $start, $now)
    {
        $secondDiff = ($now - $start);
        $speed = $secondDiff > 0 ? $proceeded / $secondDiff : 0;
        $remaining = $total - $proceeded;
        if ($speed > 0) {
            $remainingSeconds = $remaining / $speed;
            return $remainingSeconds;
        }
    }

    static public function calculateEstimatedPeriod($proceeded, $total, $start, $now)
    {
        $str = '--';
        if ($remainingSeconds = self::calculateRemainingSeconds($proceeded, $total, $start, $now)) {
            $str = '';

            $days = 0;
            $hours = 0;
            $minutes = 0;
            if ($remainingSeconds > (3600 * 24)) {
                $days = ceil($remainingSeconds / (3600 * 24));
                $remainingSeconds = $remainingSeconds % (3600 * 24);
            }

            if ($remainingSeconds > 3600) {
                $hours = ceil($remainingSeconds / 3600);
                $remainingSeconds = $remainingSeconds % 3600;
            }

            if ($remainingSeconds > 60) {
                $minutes = ceil($remainingSeconds / 60);
                $remainingSeconds = $remainingSeconds % 60;
            }

            if ($days > 0) {
                $str .= $days . 'd'; 
            }
            if ($hours) {
                $str .= $hours . 'h';
            }
            if ($minutes) {
                $str .= $minutes . 'm';
            }
            if ($remainingSeconds > 0) {
                $str .= intval($remainingSeconds) . 's';
            }
        }
        return $str;
    }

    static public function calculateEstimatedTime($proceeded, $total, $start, $now)
    {
        if ($remainingSeconds = self::calculateRemainingSeconds($proceeded, $total, $start, $now)) {
            return $now + $remainingSeconds;
        }
    }
}




