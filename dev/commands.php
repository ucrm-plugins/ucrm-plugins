<?php /** @noinspection PhpUnused */
declare(strict_types=1);

use UCRM\Plugins\Support\FileSystem;

require_once __DIR__ . "/../vendor/autoload.php";

/**
 * Loads all commands from the specified path, given the specified namespace.
 *
 *
 * @param string $path
 * @param string $fqns
 *
 * @return array
 */
function loadCommands(string $path, string $fqns): array
{
    $commands = [];

    if (!($path = realpath($path)))
        return $commands;

    // Remove the leading or trailing \ from the namespace provided.
    $fqns = trim($fqns, "\\");
    
    foreach(FileSystem::scan($path) as $file)
    {
        // Construct the fully qualified class name.
        $fqcn = $fqns . "\\" . (str_replace(".php", "", $file));

        try
        {
            // Attempt to reflect the class.
            $reflected = new ReflectionClass($fqcn);

            // IF the current class is abstract, we can't instantiate, so skip!
            if($reflected->isAbstract())
                continue;
        }
        catch (ReflectionException $e)
        {
            echo "Unable to load $fqcn, skipping\n";
            continue;
        }

        // We should be able to instantiate and add the Command at this point.
        $commands[] = new $fqcn();  //$application->add(new $fqcn());
    }

    return $commands;
}
