<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Support;

use ErrorException;

class Templater
{
    /** @var string RegEx pattern for matching variables. */
    protected const VAR_PATTERN = "/^(.*)\{\{ *([A-Za-z][A-Za-z0-9_-]*) *\}\}(.*)$/m";
    
    /** @var string RegEx pattern for matching commands. */
    protected const CMD_PATTERN = "/^(.*)\{\% *(.*) *\%\}(.*)(\r\n|\r|\n)?/m";
    
    /**
     * Keeps only named indices of an associative array.
     *
     * @param array $array The array on which to operate.
     *
     * @return array Returns an array containing only entries where the key is a string.
     */
    protected static function named(array $array): array
    {
        return array_filter($array, function($key) { return is_string($key); }, ARRAY_FILTER_USE_KEY);
    }
    
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
    
    /**
     * Replaces all occurrence of variables and available commands in the directory specified, recursively.
     *
     * @param string $dir The directory in which to search.
     * @param array<string, string> $replacements An associative array of variable names and replacement values.
     *
     * @return int The number of modified files.
     */
    public static function replace(string $dir, array $replacements = []): int
    {
        $modified = 0;
        
        // Iterate over all files in the specified directory, recursively...
        FileSystem::each($dir,
            function(string $path) use ($replacements, /* $uses,*/ &$modified): string
            {
                $info = pathinfo($path);
                
                $defaults = [
                    "TEMPLATE_PATH" => $path,
                    "TEMPLATE_NAME" => $info["filename"],
                    "TEMPLATE_FILE" => $info["basename"],
                    
                    // NOTE: Add any additional template defaults here!
                ];
                
                $replacements = array_merge($defaults, $replacements);
                
                $content = file_get_contents($path);
            
                $cmd_count = 0;
                $content = preg_replace_callback(self::CMD_PATTERN,
                    function(array $matches): string
                    {
                        $code = trim($matches[2]);
                        
                        $class = "";
                        $command = $code;
                        
                        if (strpos($command, "::") === 0)
                        {
                            //$class = "\\UCRM\\Plugins\\Support\\Templater";
                            $class = "\\" . __CLASS__;
                            $command = str_replace("::", "", $command);
                        }
                        
                        if (strpos($command, basename(__CLASS__) . "::") === 0)
                        {
                            $class = "\\" . __CLASS__; // "\\UCRM\\Plugins\\Support\\Templater";
                            $command = str_replace("Templater::", "", $command);
                        }
    
                        if (strpos($command, "::") > 0)
                        {
                            $parts = explode("::", $command);
                            $class = count($parts) > 0 ? $parts[0] : $command;
                            $command = count($parts) > 1 ? $parts[1] : "__invoke";
                        }
                        
                        if ($class)
                        {
                            if (class_exists($class))
                            {
                                if (method_exists($class, $command))
                                {
                                    return $class::$command($matches);
                                }
                                else
                                {
                                    return "TEMPLATE_UNKNOWN_METHOD";
                                }
        
                            }
                            
                            return "TEMPLATE_UNKNOWN_CLASS";
                        }
                        
                        //print_r(__CLASS__);
                        
                        ob_start();
                        
                        try
                        {
                            set_error_handler(
                                function($num, $msg, $file, $line /*, $context */)
                                {
                                    // Error was suppressed with the @-operator
                                    if (error_reporting() === 0)
                                        return FALSE;
        
                                    if ($num !== E_ERROR)
                                        throw new ErrorException(sprintf("%s: %s", $num, $msg), 0, $num, $file, $line);
        
                                    return TRUE;
                                }
                            );
                            
                            eval($code.";");
                            $eval = ob_get_contents();
                            restore_error_handler();
                        }
                        catch(ErrorException $e)
                        {
                            $eval = "TEMPLATE_ERROR";
                        }
                        finally
                        {
                            ob_end_clean();
                        }
                        
                        return ($matches[1] . $eval . $matches[3].$matches[4]);
                        
                    },
                    $content,
                    -1, // All occurrences
                    $cmd_count // Keep a count of occurrences
                );

                $var_count = 0;
                $content = preg_replace_callback(self::VAR_PATTERN,
                    function(array $matches) use ($replacements)
                    {
                        $name = $matches[2];
                        
                        if (!array_key_exists($name, $replacements))
                        {
                            if (defined($name))
                                return ($matches[1].constant($name).$matches[3]);
                            else
                                // Not named in replacements, skip!
                                return ($matches[1]."TEMPLATE_UNKNOWN_VARIABLE".$matches[3]);
                        }
                        
                        return ($matches[1] . $replacements[$name] . $matches[3]);
                    },
                    $content,
                    -1, // All occurrences
                    $var_count // Keep a count of occurrences
                );
    
                
                // IF any commands or variables have been replaced...
                if ($cmd_count > 0 || $var_count > 0)
                {
                    print_r($content);
                    //file_put_contents($path, $contents);
                    
                    $modified++;
                }
                
                // Return the unaltered path, even though we do not use it anywhere!
                return $path;
            },
            TRUE
        );
        
        // Return the count of modified files!
        return $modified;
    }
    
    #region COMMANDS
    
    
    
    /**
     * @param array $matches
     *
     * @return string
     * @noinspection PhpUnusedParameterInspection
     */
    public static function removeLine(array $matches): string
    {
        return "";
    }
    
    /**
     * @param array $matches
     *
     * @return string
     */
    public static function removeComment(array $matches): string
    {
        return preg_replace("#/\*.*\*/#", "", $matches[0]);
    }
    
    #endregion

}