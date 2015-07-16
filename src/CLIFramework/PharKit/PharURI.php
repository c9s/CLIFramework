<?php
namespace CLIFramework\PharKit;

class PharURI
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

    public function __toString()
    {
        // $stmt = new RequireStatement("phar://$pharFile/" . $localPath);
        return 'phar://' . $this->alias . '/' . $this->localPath;
    }

}




