<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Support;

/**
 * JsonObject
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 */
class JsonObject
{
    /**
     * @param array|null $json
     */
    public function __construct(?array $json = null)
    {
        if ($json !== null)
            $this->set($json);
    }

    private function set(array $data)
    {
        foreach ($data as $key => $value)
        {
            if (is_array($value))
            {
                $sub = new JSONObject;
                $sub->set($value);
                $value = $sub;
            }
            $this->{$key} = $value;
        }
    }
}
