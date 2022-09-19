<?php
declare(strict_types=1);

namespace UCRM\Plugins\Support;

/**
 * An assortment of Regular Expression (RegEx) functions.
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 - Spaeth Technologies Inc.
 */
final class RegEx
{
    /**
     * Determines whether the given string ia a valid RegEx pattern.
     *
     * @param string $pattern Teh pattern to be tested.
     * @return boolean Returns TRUE if the pattern is valid, otherwise FALSE.
     */
    public static function isValidPattern(?string $pattern): bool
    {
        return ($pattern !== null) && !(@preg_match($pattern, "") === false);
    }

    /**
     * Filters an array using Regular Expressions (RegEx).
     *
     * @param string|null $reKey
     * The pattern with which to compare the array's keys
     * - An empty string throws an exception
     * - NULL matches any key
     *
     * @param string|null $reVal
     * The pattern with which to compare the array's values
     * - An empty string throws an exception
     * - A valid pattern will NEVER match an array
     * - NULL matches any value, including arrays
     *
     * @param array $array
     * The array to filter
     * - Can be indexed or associative
     *
     * @param callable(string &$key, mixed &$value): bool $callback
     * An optional callback function
     * - The function can manipulate the $key and $value parameters (passed by reference)
     * - Return true/false to further include/exclude in the filtering process
     *
     * @return array|false
     * Returns the filtered (or empty) array or FALSE if an error occurred.
     *
     * @throws Exceptions\RegExInvalidException
     * Throws an exception if either $key_pattern or $val_pattern are invalid RegEx expressions.
     */
    public static function array_match(?string $reKey, ?string $reVal, array $array, ?callable $callback = null)
    {
        if (!self::isValidPattern($reKey) && $reKey !== null)
            throw new Exceptions\RegExInvalidException("The provided key pattern is invalid!");

        if (!self::isValidPattern($reVal) && $reVal !== null)
            throw new Exceptions\RegExInvalidException("The provided value pattern is invalid!");

        $matches = [];

        foreach($array as $key => $val)
        {
            try
            {
                $key_match = $reKey === null ? true : preg_match($reKey, (string)$key);

                if (is_array($val))
                    $val_match = ($reVal === null);
                else
                    $val_match = $reVal === null ? true : preg_match($reVal, (string)$val);
            }
            catch(\Exception $e)
            {
                return false;
            }

            if ($key_match && $val_match)
            {
                $callback = $callback ?? function(&$key, &$val): bool { return true; };

                if ($callback($key, $val))
                    $matches[$key] = $val;

            }
        }

        return $matches;
    }



}
