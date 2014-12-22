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
    /** @type \CLIFramework\CommandBase */
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
     * Load all commands of parent based on file-system structure.
     *
     * Commands to be autoloaded must located at specific directory.
     * If parent is Application, commands must be whthin Command/ directory.
     * If parent is another command named FooCommand, sub-commands must
     * within FooCommand/ directory.
     *
     * @return void
     */
    public function autoload()
    {
        $path = $this->getCommandDirectoryPath();
        $commands = $this->readCommandsInPath($path);
        $this->addCommandsForParent($commands);
    }

    private function getCommandDirectoryPath()
    {
        $reflector = new \ReflectionClass(get_class($this->parent));
        $classDir = dirname($reflector->getFileName());
        $commandDirectoryBase= $this->parent->isApplication()
            ? 'Command'
            : $reflector->getShortName();
        return $classDir . '/' . $commandDirectoryBase;
    }

    private function readCommandsInPath($path)
    {
        if (!is_dir($path))
            return [];
        $files = scandir($path);
        $classFiles = $this->filterCommandClassFiles($files);
        return $this->mapClassFilesToCommands($classFiles);
    }

    private function filterCommandClassFiles($files)
    {
        return array_filter($files, [$this, 'isCommandClassFile']);
    }

    private function isCommandClassFile($file)
    {
        $extensions = explode(',', spl_autoload_extensions());
        return $file[0] !== '.'
            and preg_match('/Command(\..*)$/', $file, $matches) === 1
            and in_array($matches[1], $extensions);
    }

    private function mapClassFilesToCommands($classFiles)
    {
        $classes = array_map(
            // remove extension part of file name
            function ($classFile) {
                return substr($classFile, 0, strpos($classFile, '.')); 
            },
            $classFiles
        );
        return array_map(
            [$this->parent->getLoader(), 'inverseTranslate'],
            $classes
        );
    }

    private function addCommandsForParent($commands)
    {
        array_walk(
            $commands,
            function ($command) { $this->parent->addCommand($command); }
        );
    }
}
