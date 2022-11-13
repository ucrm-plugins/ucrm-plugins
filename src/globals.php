<?php
declare(strict_types=1);

if (!defined("PROJECT_DIR"))
    define("PROJECT_DIR", realpath(__DIR__."/.."));

function plugin_exists(string $name, string &$path = null): bool
{
    return realpath($path = PROJECT_DIR . "/plugins/$name");
}

if (!function_exists("plugin_dir")) {
    /**
     * Gets the directory of the specified Plugin.
     *
     * @param string $plugin Name of the plugin
     * @return string|false Returns the directory of the plugin, or FALSE if it does not exist.
     */
    function plugin_dir(string $plugin)
    {
        return realpath(PROJECT_DIR . "/plugins/$plugin");
    }
}
