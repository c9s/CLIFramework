<?php
namespace CLIFramework\Config;

class GlobalConfig
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var boolean
     */
    private $isVerbose = false;

    /**
     * @var boolean
     */
    private $isDebug = false;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Returns true if verbose option is enabled.
     * @return boolean
     */
    public function isVerbose()
    {
        if (isset($this->config['core']['verbose'])) {
            $this->isVerbose = $this->config['core']['verbose'] === '1';
        }
        return $this->isVerbose;
    }

    /**
     * Returns true if debug option is enabled.
     * @return boolean
     */
    public function isDebug()
    {
        if (isset($this->config['core']['debug'])) {
            $this->isDebug = $this->config['core']['debug'] === '1';
        }
        return $this->isDebug;
    }
}
