<?php
namespace CLIFramework\Component\Progress;

interface ProgressReporter
{
    public function reportProgress($finishedValue, $totalValue);
}

