<?php
namespace CLIFramework\Component\Progress;

interface ProgressReporter
{
    public function update($finishedValue, $totalValue);
}
