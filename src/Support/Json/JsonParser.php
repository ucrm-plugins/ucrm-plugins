<?php

declare(strict_types=1);

namespace UCRM\Plugins\Support\Json;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use DateTime;
use Nette\PhpGenerator\Property;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use UCRM\Plugins\Support\ArrayHelper;
use UCRM\Plugins\Support\Diff;
use UCRM\Plugins\Manifest;

/**
 * JsonParser
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 - Spaeth Technologies Inc.
 */
class JsonParser
{
    private const REGEX_COMMENT_REPLACE = '#"comment-(\d+)"\s*:\s*"([^"]*)",?#';
    private const REGEX_COMMENT_LINE    = '#^\s*//([^\n]*)$#';
    private const REGEX_COMMENT_BLOCK   = '#/\*.*\*/#s';
    private const REGEX_TRAILING_COMMA  = '#\,(?!\s*[\{\"\w])#';

    private const DEFAULT_ENCODE_FLAGS  = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;

    /**
     * @var array|object
     */
    private $decoded;

    /**
     * @var string
     */
    private string $json;

    /**
     * Constructor
     *
     * Loads and/or decodes the specified JSON string or file.
     *
     * @param string $jsonOrPath Either a JSON string or path to a JSON file.
     */
    public function __construct(string $jsonOrPath, ?bool $associative = null)
    {
        // IF the provided string is valid JSON...
        if (self::isJson($jsonOrPath))
        {
            // ...THEN, decode it!
            $this->decoded = self::decode($jsonOrPath, $associative);
            $this->json = $jsonOrPath;
        }
        else
        {
            // ...OTHERWISE, assume it is a path, so check its existence.

            if (!file_exists($jsonOrPath))
                throw new FileNotFoundException("Could not open file $jsonOrPath", 1);

            $this->decoded = self::decode(file_get_contents($jsonOrPath), $associative);
            $this->json = file_get_contents($jsonOrPath);
        }
    }


    private static function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function decoded()
    {
        return $this->decoded;
    }

//    public function asArray(): array
//    {
//        return ($this->decoded = self::decode($this->json, true));
//    }
//
//    public function asObject(): object
//    {
//        return ($this->decoded = self::decode($this->json, false));
//    }

    public function generate(string $namespace, string $class, string $dir): ?JsonObject
    {
        $fields = self::decode($this->json, true);
        /** @var Manifest $object */
        $object = new JsonObject($fields, $namespace, $class, $dir, function(Property &$property, string $class, string $key, $value): bool
        {
            if ($class === "UCRM\\Plugins\\Manifest\\Information\\UcrmVersionCompliancy" ||
                $class === "UCRM\\Plugins\\Manifest\\Information\\UnmsVersionCompliancy")
            {
                if($key === "min" || $key === "max")
                {
                    $property->setType("string")->setNullable();
                    return true;
                }

            }

            return false;
        });
        //$object->information->ucrmVersionCompliancy->


        return $object;
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
        $encoded = json_encode($this->decoded, $flags, $depth);
        $encoded = self::replaceComments($encoded);

        file_put_contents($path, $encoded);

        return $this;
    }

    /**
     * @param string|int $key
     * @param mixed $default
     * @param string $separator
     *
     * @return mixed
     */
    public function get($key, $default = null, string $separator = ".")
    {
        //print_r($this->decoded);
        print_r($this->decoded->information->version);
        exit;

        return ArrayHelper::get($this->decoded, $key, $default, $separator);
    }

    /**
     * @param string|int $key
     * @param mixed $value
     * @param string $separator
     *
     * @return JsonParser
     */
    public function set($key, $value, string $separator = "."): self
    {
        $this->decoded = ArrayHelper::set($this->decoded, $key, $value, $separator);
        return $this;
    }

//    public function get(...$nodes)
//    {
//        $current = $this->decoded;
//
//        foreach ($nodes as $node)
//        {
//            var_dump(array_keys($current));
//        }
//
//        return $current;
//    }



    /**
     * @param JsonParser $json
     * @param bool $ignoreComments
     *
     * @return array
     */
    public function diff(JsonParser $json, bool $ignoreComments = TRUE): array
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
    public static function decode(string $json, ?bool $associative = null, int $depth = 512, int $flags = 0)
    {
        $json = self::removeComments($json);
        $json = self::removeTrailingCommas($json);
        $json = json_decode($json, $associative, $depth, $flags);

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
        return preg_replace(self::REGEX_COMMENT_REPLACE, "//$2", $json);
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
