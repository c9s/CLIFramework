<?php
namespace CLIFramework\Testing;

class Parser
{
    public static function getArguments($command)
    {
        $command = self::normalizeCommand($command);
        $result = array();
        $i = 0;
        $next = strpos($command, " ");
        while ($next)
        {
            if ($command[$i] == "\"")
            {
                $i = $i + 1;
                $next = strpos($command, "\"", $i);
            }

            $result[] = substr($command, $i, $next - $i);
            $i = $next + 1;
            $next = strpos($command, " ", $i);
        }

        $result[] = substr($command, $i, strlen($command) - $i);
        return $result;
    }

    public static function normalizeCommand($command)
    {
        $command = preg_replace('/\s+/', ' ', $command);
        return preg_replace('/"\s+/', '"', $command);
    }
}
?>
