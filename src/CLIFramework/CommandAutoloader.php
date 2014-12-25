<?php
namespace CLIFramework;

/**
 * This class tries to autoload a CommandBase's all sub-commands
 *
 * @method __construct
 * @method autoload
 */
class CommandAutoloader
{
    /** @var \CLIFramework\CommandBase */
    private $parent;

    /**
     * Constructor.
     *
     * @param \CLIFramework\CommandBase $parent object we want to load its
     *     commands/subcommands
     */
    public function __construct(CommandBase $parent)
    {
        $this->parent = $parent;
    }
    
    /**
     * Add all commands in a directory to parent command
     *
     * @param string|null $path if string is given, add the commands in given
     *     path. If null is given, use current command's path.
     * @return void
     */
    public function autoload($path = null)
    {
        if (is_null($path))
            $path = $this->getCurrentCommandDirectory();
        $commands = $this->scanCommandsInPath($path);
        $this->addCommandsForParent($commands);
    }
    
    private function getCurrentCommandDirectory()
    {
        $reflector = new \ReflectionClass(get_class($this->parent));
        $classDir = dirname($reflector->getFileName());

        /*
         * Commands to be autoloaded must located at specific directory.
         * If parent is Application, commands must be whthin App/Command/ directory.
         * If parent is another command named FooCommand, sub-commands must
         *     within App/Command/FooCommand/ directory.
         */
        $commandDirectoryBase= $this->parent->isApplication()
            ? 'Command'
            : $reflector->getShortName();
        return $classDir . DIRECTORY_SEPARATOR . $commandDirectoryBase;
    }
    
    private function scanCommandsInPath($path)
    {
        if (!is_dir($path))
            return array();
        $files = scandir($path);
        return $this->translateFileNamesToCommands($files);
    }

    private function translateFileNamesToCommands(array $fileNames)
    {
        $commands = array_map(
            array($this, 'translateFileNameToCommand'),
            $fileNames
        );
        return array_filter(
            $commands,
            function ($command) { return $command !== false; }
        );
    }

    private function translateFileNameToCommand($fileName)
    {
        $extensions = explode(',', spl_autoload_extensions());
        $isCommandClassFile = ($fileName[0] !== '.'
            and preg_match('/(^.*Command)(\..*)$/', $fileName, $matches) === 1
            and in_array($matches[2], $extensions));
        return $isCommandClassFile
            ? $this->parent->getLoader()->inverseTranslate($matches[1])
            : false;
    }

    private function addCommandsForParent($commands)
    {
        foreach ($commands as $command)
            $this->parent->addCommand($command);
    }
}
