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

    /** @type string */
    private $path;

    /**
     * Constructor.
     *
     * @param \CLIFramework\CommandBase $parent object we want to load its
     *     commands/subcommands
     * @param string|null $path if string is given, load the commands in given
     *     path. If null is given, use default path.
     */
    public function __construct(CommandBase $parent, $path = null)
    {
        $this->parent = $parent;
        $this->path = is_null($path)
            ? $this->getCurrentCommandDirectory()
            : $path;
    }

    private function getCurrentCommandDirectory()
    {
        $reflector = new \ReflectionClass(get_class($this->parent));
        $classDir = dirname($reflector->getFileName());
        // @see self::autoload()
        $commandDirectoryBase= $this->parent->isApplication()
            ? 'Command'
            : $reflector->getShortName();
        return $classDir . DIRECTORY_SEPARATOR . $commandDirectoryBase;
    }

    /**
     * Load all commands of parent based on file-system structure.
     *
     * Commands to be autoloaded must located at specific directory.
     * If parent is Application, commands must be whthin App/Command/ directory.
     * If parent is another command named FooCommand, sub-commands must
     *     within App/Command/FooCommand/ directory.
     *
     * @return void
     */
    public function autoload()
    {
        $commands = $this->scanCommandsInPath();
        $this->addCommandsForParent($commands);
    }
    
    private function scanCommandsInPath()
    {
        if (!is_dir($this->path))
            return array();
        $files = scandir($this->path);
        $classFiles = $this->filterCommandClassFiles($files);
        return $this->translateClassFilesToCommands($classFiles);
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

    private function translateClassFilesToCommands(array $classFiles)
    {
        $classes = array_map(
            // remove extension part of file name
            function ($classFile) {
                return substr($classFile, 0, strpos($classFile, '.')); 
            },
            $classFiles
        );
        return array_values(array_map(
            [$this->parent->getLoader(), 'inverseTranslate'],
            $classes
        ));
    }

    private function addCommandsForParent($commands)
    {
        array_walk(
            $commands,
            array($this->parent, 'addCommand')
        );
    }
}
