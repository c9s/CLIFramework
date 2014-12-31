<?php
namespace CLIFramework\ConsoleInfo;

class TputConsoleInfo implements ConsoleInfoInterface
{
    public function getColumns() 
    {
        return intval(exec('tput cols'));
    }

    public function getRows() 
    {
        return intval(exec('tput lines'));
    }

    static public function hasSupport()
    {
        $paths = explode(':',getenv('PATH'));
        foreach($paths as $path) {
            $bin = $path . DIRECTORY_SEPARATOR . 'tput';
            if (file_exists($bin) && is_executable($bin)) {
                return true;
            }
        }
        return false;
    }

}



