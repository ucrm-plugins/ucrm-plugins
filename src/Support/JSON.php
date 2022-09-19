<?php

declare(strict_types=1);

namespace UCRM\Plugins\Support;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 *
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 - Spaeth Technologies Inc.
 */
final class JSON
{
    private const REGEX_COMMENT_REPLACE = '#"comment-(\d+)"\s*:\s*"([^"]*)",?#';
    private const REGEX_COMMENT_LINE    = '#^\s*//([^\n]*)$#';
    private const REGEX_COMMENT_BLOCK   = '#/\*.*\*/#s';
    private const REGEX_TRAILING_COMMA  = '#\,(?!\s*[\{\"\w])#';

    private const DEFAULT_ENCODE_FLAGS  = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;

    private string $encoded;
    private array  $decoded;

    /**
     *
     *
     * @param string $json
     * @param boolean $decode
     */
    public function __construct(string $json)
    {
        $this->decoded = self::decode($json, true);
    }

    /**
     * Reads a JSON file
     *
     * @param string $path
     * @return self
     */
    public static function load(string $path): self
    {
        if (!file_exists($path))
            throw new FileNotFoundException("Could not open file $path");

        return new self(file_get_contents($path));
    }

    public function getDecoded(): array
    {
        return $this->decoded;
    }

    /**
     *
     *
     * @param string $path
     * @param integer $flags
     * @param integer $depth
     * @return void
     */
    public function save(string $path, int $flags = self::DEFAULT_ENCODE_FLAGS, int $depth = 512): self
    {
        $this->encoded = json_encode($this->decoded, $flags, $depth);
        $this->encoded = self::replaceComments($this->encoded);

        file_put_contents($path, $this->encoded);

        return $this;
    }

    public function get(...$nodes)
    {
        $current = $this->decoded;

        foreach ($nodes as $node)
        {
            var_dump(array_keys($current));
        }

        return $current;
    }

    /**
     * @param JSON $json
     * @param bool $ignoreComments
     *
     * @return array
     */
    public function diff(JSON $json, bool $ignoreComments = TRUE): array
    {
        return Diff::array($this->getDecoded(), $json->getDecoded());
    }



    /**
     * Decodes the JSON string
     *
     * @param string $json
     * @param boolean|null $associative
     * @param integer $depth
     * @param integer $flags
     * @return mixed
     */
    public static function decode(string $json, int $depth = 512, int $flags = 0)
    {
        $json = self::removeComments($json);
        $json = self::removeTrailingCommas($json);
        $json = json_decode($json, TRUE, $depth, $flags);

        if(json_last_error() !== JSON_ERROR_NONE)
            return FALSE;

        return $json;
    }

    /**
     *
     * @param mixed $value
     * @param integer $flags
     * @param integer $depth
     * @return string|false
     */
    public static function encode($value, int $flags = self::DEFAULT_ENCODE_FLAGS, int $depth = 512)
    {
        $json = json_encode($value, $flags, $depth);

        if ($json === false)
            return false;

        $json = self::replaceComments($json);

        return $json;
    }






    private static function removeComments(string $json): string
    {
        $i = 0;
        $json = preg_replace_callback(
            self::REGEX_COMMENT_LINE,
            function ($match) use (&$i)
            {
                //$comment = "comment-" . $i++;
                $comment = "comment-" . self::generateCommentId();
                $value = $match[1];

                // if (preg_match("#^\s*\[\s*(.*)\s*:\s*([^\s]+)\s*\]\s*(.*)\s*$#", $match[1], $matches))
                // {
                //     $comment = "comment-${matches[1]}:${matches[2]}";
                //     $value = $matches[3];
                // }

                return "\"$comment\": \"$value\",";
            },
            $json
        );

        return $json;
    }

    private static function replaceComments(string $json): string
    {
        $i = 0;
        $json = preg_replace(self::REGEX_COMMENT_REPLACE, "//$2", $json);
return $json;
    }

    private static function removeTrailingCommas(string $json): string
    {
        return preg_replace(self::REGEX_TRAILING_COMMA, "", $json);
    }

    private static function generateCommentId(int $length = 8)
    {
        return substr(md5((string)rand()), 0, $length);
    }
}
