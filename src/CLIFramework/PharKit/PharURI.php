<?php
namespace CLIFramework\PharKit;
use CodeGen\Renderable;

class PharURI implements Renderable
{
    protected $alias;

    protected $localPath;

    public function __construct($alias, $localPath)
    {
        $this->alias = $alias;
        $this->localPath = $localPath;
    }

    public function getLocalPath()
    {
        return $this->localPath;
    }

    public function getAlias()
    {
        return $this->alias;
    }


    /**
     * 'render' is a method of Renderable interface
     */
    public function render(array $args = array())
    {
        return $this->__toString();
    }


    public function __toString()
    {
        // $stmt = new RequireStatement("phar://$pharFile/" . $localPath);
        return var_export('phar://' . $this->alias . '/' . $this->localPath, true);
    }

}




