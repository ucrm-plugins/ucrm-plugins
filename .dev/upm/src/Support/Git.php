<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Support;

class Git
{
    /**
     * Attempts to get the current user's information from Git.
     *
     * @param bool $global
     *
     * @return string
     */
    protected static function getGitUser(bool $global = FALSE): string
    {
        // Build the command.
        $config = shell_exec("git config " . ($global ? "--global " : ""). "--list");
        
        if ($config && preg_match_all("/^user.(?<key>(name|email))=(?<value>.*)$/m", $config, $matches))
        {
            if (array_key_exists("key", $matches) && array_key_exists("value", $matches))
            {
                $info = array_combine($matches["key"], $matches["value"]);
                return $info["name"] . " <" . $info["email"] . ">";
            }
        }
        
        return "";
    }
    
    /**
     * Attempts to get the Author's information from Git.
     *
     * @param string $default The default to use when a Git user is not found.
     *
     * @return string The Author's information in the format `Name <email>`.
     */
    public static function getAuthor(string $default = "Unknown"): string
    {
        // Try local config first...
        if ($user = self::getGitUser())
            return $user;
        
        // Try global config next...
        if ($user = self::getGitUser(TRUE))
            return $user;
        
        // Otherwise, return the default!
        return $default;
    }
    
}
