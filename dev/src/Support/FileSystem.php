<?php
declare(strict_types=1);

// cspell:ignore degit

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
    public static function path(string $path, string $separator = DIRECTORY_SEPARATOR): string
    {
        return rtrim(str_replace(["\\", "/"], $separator, $path), $separator);
    }

    public static function uri(string $path): string
    {
        return "file://".(DIRECTORY_SEPARATOR === "\\" ? "/" : "").self::path($path, "/");
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
     * @param array $exclusions An optional array of exclusions, relative to $dir.
     * @param bool $verbose
     * @param array $counts
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
     * @noinspection PhpUnused
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
     * @noinspection PhpUnused
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


    /**
     * @param string $dir
     * @param string $separator
     * @param bool $absolute
     *
     * @return array
     */
    public static function scan(string $dir, string $separator = DIRECTORY_SEPARATOR, bool $absolute = FALSE): array
    {
        $result = [];
        foreach(scandir($dir) as $file)
        {
            if ($file === "." || $file === "..")
                continue;

            $filePath = $dir . $separator . $file;

            if (is_dir($filePath)) {
                foreach (self::scan($filePath, $separator) as $childFilename) {
                    $result[] = ($absolute ? $dir . $separator : ""). $file . $separator . $childFilename;
                }
            } else {
                $result[] = ($absolute ? $dir . $separator : "") . $file;
            }
        }
        return $result;
    }

    /**
     * @param string $dir
     * @param callable|NULL $func
     * @param string $separator
     * @param bool $absolute
     *
     * @return array
     */
    public static function each(string $dir, callable $func = NULL, string $separator = DIRECTORY_SEPARATOR, bool $absolute = FALSE): array
    {
        $func = $func ?? function(string $file): string { return $file; };

        $result = [];
        foreach(scandir($dir) as $file)
        {
            if ($file === "." || $file === "..")
                continue;

            $filePath = $dir . $separator . $file;

            if (is_dir($filePath)) {
                foreach (self::scan($filePath, $separator) as $childFilename) {
                    $result[] = $func(($absolute ? $dir . $separator : "") . $file . $separator . $childFilename);
                }
            } else {
                $result[] = $func(($absolute ? $dir . $separator : "") . $file);
            }
        }
        return $result;
    }

    /**
     * @param string $source
     * @param string $destination
     * @param bool $replace
     * @param bool $verbose
     * @param array $counts
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


    // as per RFC 3986
    // @see https://www.rfc-editor.org/rfc/rfc3986#section-5.2.4
    public static function canonical(string $path, string $separator = DIRECTORY_SEPARATOR)
    {
        // Force forward slashes!
        $path = FileSystem::path($path, "/");

        // 1.  The input buffer is initialized with the now-appended path
        //     components and the output buffer is initialized to the empty
        //     string.
        $output = '';

        // 2.  While the input buffer is not empty, loop as follows:
        while ($path !== '') {
            // A.  If the input buffer begins with a prefix of "`../`" or "`./`",
            //     then remove that prefix from the input buffer; otherwise,
            if (
                ($prefix = substr($path, 0, 3)) == '../' ||
                ($prefix = substr($path, 0, 2)) == './'
            ) {
                $path = substr($path, strlen($prefix));
            } else

                // B.  if the input buffer begins with a prefix of "`/./`" or "`/.`",
                //     where "`.`" is a complete path segment, then replace that
                //     prefix with "`/`" in the input buffer; otherwise,
                if (
                    ($prefix = substr($path, 0, 3)) == '/./' ||
                    ($prefix = $path) == '/.'
                ) {
                    $path = '/' . substr($path, strlen($prefix));
                } else

                    // C.  if the input buffer begins with a prefix of "/../" or "/..",
                    //     where "`..`" is a complete path segment, then replace that
                    //     prefix with "`/`" in the input buffer and remove the last
                    //     segment and its preceding "/" (if any) from the output
                    //     buffer; otherwise,
                    if (
                        ($prefix = substr($path, 0, 4)) == '/../' ||
                        ($prefix = $path) == '/..'
                    ) {
                        $path = '/' . substr($path, strlen($prefix));
                        $output = substr($output, 0, strrpos($output, '/'));
                    } else

                        // D.  if the input buffer consists only of "." or "..", then remove
                        //     that from the input buffer; otherwise,
                        if ($path == '.' || $path == '..') {
                            $path = '';
                        } else

                            // E.  move the first path segment in the input buffer to the end of
                            //     the output buffer, including the initial "/" character (if
                            //     any) and any subsequent characters up to, but not including,
                            //     the next "/" character or the end of the input buffer.
                        {
                            $pos = strpos($path, '/');
                            if ($pos === 0) $pos = strpos($path, '/', $pos+1);
                            if ($pos === false) $pos = strlen($path);
                            $output .= substr($path, 0, $pos);
                            $path = (string) substr($path, $pos);
                        }
        }

        // 3.  Finally, the output buffer is returned as the result of remove_dot_segments.
        return str_replace("/", $separator, $output);
    }




}
