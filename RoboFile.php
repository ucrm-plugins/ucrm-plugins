<?php /** @noinspection PhpUnused */
declare(strict_types=1);
require_once __DIR__."/vendor/autoload.php";

use Robo\Symfony\ConsoleIO;
use Robo\Tasks;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see https://robo.li/
 */
class RoboFile extends Tasks
{
    function hello(ConsoleIO $io, $world)
    {
        $io->say("Hello, $world");
    }
}
