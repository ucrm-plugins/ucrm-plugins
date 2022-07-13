<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Support;

final class Templater
{
    private const VAR_PATTERN = "/^(.*)\{\{ *([A-Za-z][A-Za-z0-9_-]*) *\}\}(.*)$/m";
    private const CMD_PATTERN = "/^(.*)\{\% *(.*) *\%\}(.*)$/m";
    
    
    private static function named(array $array): array
    {
        return array_filter($array, function($key) { return is_string($key); }, ARRAY_FILTER_USE_KEY);
    }
    
    private static function getGitUser(bool $global = FALSE): string
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
    
    
    /**
     * @param string $dir
     * @param array<string, string> $replacements
     *
     * @return bool
     */
    public static function replace(string $dir, array $replacements): bool
    {
        $modified = 0;
        
        
        FileSystem::each($dir,
            function($path) use ($replacements, &$modified)
            {
                $changed = FALSE;
                $content = file_get_contents($path);
    
                if (preg_match_all(self::CMD_PATTERN, $content, $matches))
                {
                    // FUTURE: Determine how we want to handle commands in templates!
                    $changed = TRUE;
                }
                
                if (preg_match_all(self::VAR_PATTERN, $content, $matches))
                {
                    $names = [];
                    
                    foreach($matches[2] as $name)
                    {
                        if (in_array($name, $names) || !array_key_exists($name, $replacements))
                            continue; // Skip!
                        else
                            $names[] = $name;
                
                        $pattern = str_replace("([A-Za-z][A-Za-z0-9_-]*)", "($name)", self::VAR_PATTERN);

                        $content = preg_replace($pattern, "$1".$replacements[$name]."$3", $content);
                        $changed = TRUE;
                    }
                    
                    
                }
    
                if ($changed)
                {
                    print_r($content);
                    //file_put_contents($path, $contents);
                    
                    $modified++;
                }
                
                
                
            },
            TRUE
        );
        
        print_r($modified);
        
        
        return TRUE;
    }



}