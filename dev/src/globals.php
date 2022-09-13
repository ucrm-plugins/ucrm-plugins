<?php
declare(strict_types=1);

if (!defined("PROJECT_DIR"))
    define("PROJECT_DIR", realpath(__DIR__."/../../"));

function plugin_exists(string $name, string &$path = null): bool
{
    return realpath($path = PROJECT_DIR."/plugins/$name");
}
