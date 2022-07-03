<?php
declare(strict_types=1);

namespace UCRM\Plugins\Support;

/**
 * Our own custom FileSystem support functions.
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 - Spaeth Technologies Inc.
 */
final class FileSystem
{
    private static function onWindows(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === "WIN";
    }
    
    /**
     * Makes changes to a given path for consistency, replacing and trimming slashes as needed.
     * NOTE: This method does not rely upon the existence of the folder/file like realpath() and other functions.
     *
     * @param string $path The path upon which to operate.
     *
     * @return string Returns the modified path.
     */
    public static function path(string $path): string
    {
        return rtrim(str_replace(["\\", "/"], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
    }
    
    
    
    public static function execRemoveDirRecursive(string $dir): bool
    {
        if (!file_exists($dir) || !is_dir($dir))
            return FALSE;
            
        exec(self::onWindows() ? "rmdir /s /q $dir" : "rm -rf $dir");
        
        return !file_exists($dir);
    }
    
    

    /**
     * Calls unlink() repeatedly until the file is removed or the specified timeout is reached.
     *
     * @param string $path The file upon which to operate.
     * @param float $timeout The timeout (in seconds) to wait before aborting.
     * @param float $delay The amount of time (in seconds) to "sleep" between each attempt.
     *
     * @return bool Returns TRUE once the file is removed, or FALSE if the file could not be removed.
     */
    public static function unlinkRetry(string $path, float $timeout = 1.0, float $delay = 0.1): bool
    {
        if (is_dir($path) && !is_link($path))
            return self::rmdirRetry($path, $timeout, $delay);

        $timeout = intval($timeout * 1000000);
        $delay = intval($delay * 100000);
        $elapsed = 0;

        while(file_exists($path) && $elapsed < $timeout)
        {
            if (@unlink($path) || !file_exists($path))
                break;

            usleep($delay);
            $elapsed += $delay;
        }

        return !($elapsed >= $timeout);
    }

    /**
     * Calls rmdir() repeatedly until the directory is removed or the specified timeout is reached.
     *
     * @param string $path The directory upon which to operate.
     * @param float $timeout The timeout (in seconds) to wait before aborting.
     * @param float $delay The amount of time (in seconds) to "sleep" between each attempt.
     *
     * @return bool Returns TRUE once the directory is removed, or FALSE if the directory could not be removed.
     */
    public static function rmdirRetry(string $path, float $timeout = 1.0, float $delay = 0.1): bool
    {
        if (is_link($path) || !is_dir($path))
            return self::unlinkRetry($path, $timeout, $delay);

        $timeout = intval($timeout * 1000000);
        $delay = intval($delay * 100000);
        $elapsed = 0;

        while(file_exists($path) && $elapsed < $timeout)
        {
            if (@rmdir($path) || !file_exists($path))
                break;

            usleep($delay);
            $elapsed += $delay;
        }

        return !($elapsed >= $timeout);
    }

    /** @var string The base path to use when starting recursion and from which to build relative paths. */
    private static string $deleteBase = "";

    /**
     * Deletes all content from a directory (recursively).
     *
     * @param string $dir The directory upon which to act.
     * @param int $count The count of folders/files deleted.
     * @param array $exclusions An optional array of exclusions, relative to $dir.
     *
     * @return bool Returns TRUE on success (or if the $dir is non-existent), or FALSE on failure.
     */
    public static function rmdir(string $dir, array $exclusions = [], bool $verbose = FALSE, array &$counts = []): bool
    {
        if (!array_key_exists("files", $counts))
            $counts["files"] = 0;
    
        if (!array_key_exists("folders", $counts))
            $counts["folders"] = 0;
        
        $dir = self::path($dir);

        if (!self::$deleteBase)
        {
            if (!is_dir($dir))
                return FALSE;

            if (!file_exists($dir))
                return TRUE;

            self::$deleteBase = $dir;
        }

        foreach (array_diff(scandir($dir), array('.', '..')) as $file)
        {
            $path = self::path("$dir/$file");
            $relative = ltrim(str_replace(self::$deleteBase, "", $path), DIRECTORY_SEPARATOR);

            if (in_array($relative, $exclusions))
                continue;

            if (is_dir($path) && !is_link($path))
                self::rmdir($path, $exclusions, $verbose, $counts);
            else
            {
                if ($verbose)
                    print_r("Deleted: $path\n");
                
                //$count += unlink($path) ? 1 : 0;
                $counts["files"] += self::unlinkRetry($path) ? 1 : 0;
            }
        }

        // IF the current directory is the root, THEN return TRUE and keep the directory, OTHERWISE remove it
        if ($dir !== self::$deleteBase)
        {
            $counts["folders"] += 1;
            //return self::rmdirRetry($dir);
            //exec("rmdir /s /q $dir");
            //return TRUE;
            return self::execRemoveDirRecursive($dir);
        }
        else
        {
            if ($verbose)
            {
                $f = $counts["files"] !== 1 ? "files" : "file";
                $d = $counts["folders"] !== 1 ? "folders" : "folder";
                
                print_r("{$counts['files']} $f deleted and {$counts['folders']} $d removed!\n");
            }
            
            return TRUE;
        }
        //return !($dir !== self::$deleteBase) || $count++ && self::rmdirRetry($dir);
    }

    /**
     * @param string $path
     *
     * @return array
     */
    public static function loadJson(string $path) : array
    {
        if (!file_exists($path))
            return [];

        if (($file = file_get_contents($path)) === FALSE)
            return [];

        if (($json = json_decode($file, TRUE)) === NULL)
            return [];

        return $json;
    }

    /**
     * @param string $path
     * @param array $content
     * @param int $options
     *
     * @return bool
     */
    public static function saveJson(string $path, array $content, int $options = JSON_PRETTY_PRINT) : bool
    {
        if (($json = json_encode($content, $options)) === FALSE)
            return FALSE;

        if (!file_exists(dirname($path)))
            mkdir($path, 0755, TRUE);

        if (file_put_contents($path, $json) === FALSE)
            return FALSE;

        return TRUE;
    }


    public static function scandir(string $dir): array
    {
        $result = [];
        foreach(scandir($dir) as $file)
        {
            if ($file === "." || $file === "..")
                continue;

            $filePath = $dir . DIRECTORY_SEPARATOR . $file;

            if (is_dir($filePath)) {
                foreach (self::scandir($filePath) as $childFilename) {
                    $result[] = $file . DIRECTORY_SEPARATOR . $childFilename;
                }
            } else {
                $result[] = $file;
            }
        }
        return $result;
    }
    
    public static function each(string $dir, callable $func = NULL): array
    {
        $func = $func ?? function(string $file): string { return $file; };
        
        $result = [];
        foreach(scandir($dir) as $file)
        {
            if ($file === "." || $file === "..")
                continue;
            
            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            
            if (is_dir($filePath)) {
                foreach (self::scandir($filePath) as $childFilename) {
                    $result[] = $func($file . DIRECTORY_SEPARATOR . $childFilename);
                }
            } else {
                $result[] = $func($file);
            }
        }
        return $result;
    }
    
    /**
     * @param string $source
     * @param string $destination
     * @param bool $replace
     * @param bool $verbose
     *
     * @return false|array
     */
    public static function copyDir(string $source, string $destination, bool $replace = FALSE, bool $verbose = FALSE, array &$counts = [])
    {
        if (!array_key_exists("files", $counts))
            $counts["files"] = 0;
    
        if (!array_key_exists("folders", $counts))
            $counts["folders"] = 0;
        
        if (!is_dir($source))
            return FALSE;
    
        if (is_dir($destination) && !$replace)
            return FALSE;
        
        if (!is_dir($destination) && !mkdir($destination))
            return FALSE;
    
        $files = self::each($source,
            function($file) use ($source, $destination, $verbose, &$counts)
            {
                $s = $source . DIRECTORY_SEPARATOR . $file;
                $d = $destination . DIRECTORY_SEPARATOR . $file;
    
                if (!file_exists(dirname($d)))
                {
                    if (mkdir(dirname($d)))
                        $counts["folders"] += 1;
                    else
                        die("Could not create directory: ". dirname($d));
                }
                
                copy($s, $d);
                $counts["files"] += 1;
                
                if ($verbose)
                    print_r("Copied: $s\n");
                
                return $file;
            }
        );
        
        if ($verbose)
        {
            $f = $counts["files"] !== 1 ? "files" : "file";
            $d = $counts["folders"] !== 1 ? "folders" : "folder";
            print_r("{$counts['files']} $f copied and {$counts['folders']} $d created!\n");
        }
        
        return $files;
    }
    
    /**
     * @param string $url
     * @param string $dir
     * @param bool $degit
     * @param bool $verbose
     *
     * @return void
     */
    public static function gitClone(string $url, string $dir, bool $degit = FALSE, bool $verbose = FALSE)
    {
        $output = exec("git clone $url $dir");
    
        if ($verbose)
            print_r($output."\n");
    
        if (!$degit)
            self::execRemoveDirRecursive(self::path("$dir/.git/"));
    
    }
    
    
    
    
    
}
