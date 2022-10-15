<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Support;

use ArrayAccess;
use Closure;

class ArrayHelper
{
//    protected array $array;
//    protected string $delimiter;
//
//    /**
//     * @param array $array
//     * @param string $delimiter
//     */
//    public function __construct(array $array, string $delimiter = ".")
//    {
//        $this->array = $array;
//        $this->delimiter = $delimiter;
//    }
//
//    public static function convert(array $array, string $delimiter = "."): self
//    {
//        return new ArrayHelper($array, $delimiter);
//    }
//


    /**
     * Get an item from an array using "dot" notation.
     *
     * @param ArrayAccess|array $array
     * @param string $key
     * @param mixed $default
     * @param string $separator
     *
     * @return mixed
     */
    public static function get($array, string $key, $default = null, string $separator = ".")
    {
        if (! static::accessible($array)) {
            return value($default);
        }


        if ($key === "") {
            return $array;
        }
        if (static::exists($array, $key)) {
            return $array[$key];
        }
        if (strpos($key, $separator) === false) {
            return $array[$key] ?? value($default);
        }
        foreach (explode($separator, $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return value($default);
            }
        }
        return $array;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @param string $separator
     *
     * @return array
     */
    public static function set(array &$array, string $key, $value, string $separator = "."): array
    {
        if ($key === "") {
            return $array = $value;
        }

        $keys = explode($separator, $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Determine whether the given value is array accessible.
     *
     * @param  mixed  $value
     * @return bool
     */
    public static function accessible($value): bool
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param ArrayAccess|array  $array
     * @param  string|int  $key
     *
     * @return bool
     */
    public static function exists($array, $key): bool
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }
        return array_key_exists($key, $array);
    }



}

if (! function_exists('value'))
{
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}
