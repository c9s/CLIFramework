<?php
namespace CLIFramework\ExceptionPrinter;
use Exception;
use CLIFramework\ServiceContainer;
use CLIFramework\Logger;

class ProductionExceptionPrinter extends DevelopmentExceptionPrinter
{
    public $reportUrl;

    public function dump(Exception $e) 
    {
        $this->dumpBrief($e);
        $this->dumpTraceInPhar($e);
    }
}




