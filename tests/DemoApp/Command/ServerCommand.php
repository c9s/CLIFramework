<?php
namespace DemoApp\Command;

use CLIFramework\Command;
use CLIFramework\Extension\DaemonExtension;

class ServerCommand extends Command {
    public function brief()
    {
        return 'An example of using DaemonExtension';
    }

    public function init()
    {
        $this->enableExtension(new DaemonExtension());
    }

    public function execute($host, $port)
    {

        $server = stream_socket_server("tcp://$host:$port", $errno, $errorMessage);

        if ($server === false) {
            throw new \RuntimeException("Could not bind to socket: $errorMessage");
        }

        for (;;) {
            $socket = @stream_socket_accept($server);

            if ($socket) {
                $text = fread($socket, 1024);
                $this->getLogger()->writeln($text);
                fclose($socket);
            }
        }
    }
}
