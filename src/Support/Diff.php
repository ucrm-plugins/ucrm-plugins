<?php
declare(strict_types=1);

namespace UCRM\Plugins\Support;

use Exception;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use UCRM\Plugins\Support\Diffs\ArrayDiff;
use UCRM\Plugins\Support\Diffs\JsonDiff;
use UCRM\Plugins\Support\Diffs\TextDiff;

/**
 *
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 - Spaeth Technologies Inc.
 */
abstract class Diff
{
    protected array $array1;
    protected array $array2;
    protected array $diff;

    public function __construct(array $array1, array $array2)
    {
        $this->array1 = $array1;
        $this->array2 = $array2;
        $this->diff = self::difference($array1, $array2);
    }

    //    /**
//     * @param array $array1
//     * @param array $array2
//     *
//     * @return static
//     */
//    public static function array(array $array1, array $array2): self
//    {
//        return new ArrayDiff($array1, $array2);
//    }
//
//    /**
//     * @param string $json1
//     * @param string $json2
//     *
//     * @return static
//     * @throws Exception
//     */
//    public static function json(string $json1, string $json2): self
//    {
//        return new JsonDiff($json1, $json2);
//    }
//
//    /**
//     * @param string $text1
//     * @param string $text2
//     * @param string $separator
//     *
//     * @return static
//     */
//    public static function text(string $text1, string $text2, string $separator = PHP_EOL): self
//    {
//        return new TextDiff($text1, $text2, $separator);
//    }

    //    /**
//     * @throws Exception
//     */
//    public static function jsonFiles(string $file1, string $file2): self
//    {
//        return JsonDiff::fromFiles($file1, $file2);
//    }
//
//    /**
//     * @throws Exception
//     */
//    public static function textFiles(string $file1, string $file2): self
//    {
//        return TextDiff::fromFiles($file1, $file2);
//    }

    //    /**
//     * @throws Exception
//     */
//    protected static function fromFiles(string $file1, string $file2): self
//    {
//        $class = get_called_class();
//
//        if (!file_exists($file1))
//            throw new FileNotFoundException($file1);
//        $text1 = file_get_contents($file1);
//
//        if (!file_exists($file2))
//            throw new FileNotFoundException($file2);
//        $text2 = file_get_contents($file2);
//
//        return new $class($text1, $text2);
//    }



    /**
     * @param array $array1
     * @param array $array2
     * @param int $depth
     *
     * @return array
     */
    protected static function difference(array $array1, array $array2, int $depth = 0): array
    {

        $aReturn = [];
        ++$depth;
        if ($depth > 50) {
            return $aReturn;
        }

        // cspell:ignore mhitza
        // as per @mhitza at https://stackoverflow.com/a/3877494/19746
        foreach ($array1 as $mKey => $mValue) {
            if (array_key_exists($mKey, $array2)) {
                if (is_array($mValue)) {
                    $aRecursiveDiff = self::difference($mValue, $array2[$mKey], $depth);
                    if (count($aRecursiveDiff)) {
                        $aReturn[$mKey] = $aRecursiveDiff;
                    }
                }
                elseif ($mValue !== $array2[$mKey]) {
                    $aReturn[$mKey] = $mValue;
                }
            }
            else {
                $aReturn[$mKey] = $mValue;
            }
        }
        foreach ($array2 as $mKey => $mValue) {
            if (array_key_exists($mKey, $array1)) {
                if (is_array($mValue)) {
                    $aRecursiveDiff = self::difference($mValue, $array1[$mKey], $depth);
                    if (count($aRecursiveDiff)) {
                        $aReturn[$mKey] = $aRecursiveDiff;
                    }
                }
                elseif ($mValue !== $array1[$mKey]) {
                    $aReturn[$mKey] = $mValue;
                }
            }
            else {
                $aReturn[$mKey] = $mValue;
            }
        }

        return $aReturn;
    }

    public function __toString(): string
    {
        if (!$this->diff)
            return "";

        return self::_print("", $this->diff, $this->array1, $this->array2);
    }

    protected static string $string;

    /**
     * @param string $prefix
     * @param array $diff
     * @param array $array1
     * @param array $array2
     * @param int $depth
     *
     * @return string
     */
    protected static function _print(string $prefix, array $diff, array $array1, array $array2, int $depth = 0): string
    {
        if ($depth === 0)
            self::$string = "";

        ++$depth;
        if ($depth > 50)
            return self::$string;

        foreach ($diff as $key => $value) {
            if (array_key_exists($key, $array1) && array_key_exists($key, $array2) && is_array($array1[$key])) {
                self::$string = self::_print(
                    $key . '.',
                    self::difference($array1[$key], $array2[$key]),
                    $array1[$key],
                    $array2[$key],
                    $depth
                );
            }
            else {
                self::$string = sprintf(
                    "    %s%s%s        file: %s%s        zip : %s%s",
                    $prefix,
                    $key,
                    PHP_EOL,
                    array_key_exists($key, $array1) ? (is_array($array1[$key]) ? 'Array' : $array1[$key]) : '(none)',
                    PHP_EOL,
                    array_key_exists($key, $array2) ? (is_array($array2[$key]) ? 'Array' : $array2[$key]) : '(none)',
                    PHP_EOL
                );
            }
        }

        return self::$string;
    }





}
