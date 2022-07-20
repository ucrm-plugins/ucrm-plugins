<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Support;

final class Templater
{
    private const VAR_PATTERN = "/^(.*)\{\{ *([A-Za-z][A-Za-z0-9_-]*) *\}\}(.*)$/m";
    private const CMD_PATTERN = "/^(.*)\{\% *(.*) *\%\}(.*)(\r\n|\r|\n)/m";
    
    
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
    public static function replace(string $dir, array $replacements, int &$modified = 0): bool
    {
        
        FileSystem::each($dir,
            function($path) use ($replacements, &$modified)
            {
                $content = file_get_contents($path);
    
            
                $cmd_count = 0;
                $content = preg_replace_callback(self::CMD_PATTERN,
                    function(array $matches)
                    {
                        switch($code = trim($matches[2]))
                        {
                            case "REMOVE_LINE":
                                return "";
                                
                            default:
                                ob_start();
                                eval($code.";");
                                $eval = ob_get_contents();
                                ob_end_clean();
    
                                return ($matches[1] . $eval . $matches[3]);
                            
                        }
                        
                        
                    },
                    $content, -1, $cmd_count
                );

                $var_count = 0;
                $names = [];
    
                $content = preg_replace_callback(self::VAR_PATTERN,
                    function(array $matches) use ($replacements, $names)
                    {
                        $name = $matches[2];
                        
                        if (in_array($name, $names) || !array_key_exists($name, $replacements))
                            // Not named in replacements, skip!
                            return ($matches[1] . "{{ " . $name . " }}" . $matches[3]);
                        
                        $names[] = $name;
                        return ($matches[1] . $replacements[$name] . $matches[3]);
                        
                        
                        //print_r($matches);
                    
                    
                    },
                    $content, -1, $var_count
                );
    
                
                
                if ($cmd_count > 0 || $var_count > 0)
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