<?php
namespace CLIFramework\Extension;
use CLIFramework\Application;

interface ApplicationExtension extends Extension {

    public function bindApplication(Application $app);

}

