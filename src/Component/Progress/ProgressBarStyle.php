<?php
namespace CLIFramework\Component\Progress;

use Exception;
use CLIFramework\Formatter;
use CLIFramework\ConsoleInfo\EnvConsoleInfo;
use CLIFramework\ConsoleInfo\ConsoleInfoFactory;

class ProgressBarStyle
{
    public $leftDecorator = "|";

    public $rightDecorator = "|";

    public $barCharacter = '#';
}
