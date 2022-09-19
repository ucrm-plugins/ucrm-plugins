<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Support\Diffs;

use Exception;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use UCRM\Plugins\Support\Diff;

class JsonDiff extends Diff
{
    /**
     * @throws Exception
     */
    public function __construct(string $json1, string $json2)
    {
        $array1 = json_decode($json1, TRUE);
        if (json_last_error() !== JSON_ERROR_NONE)
            throw new Exception(json_last_error_msg());

        $array2 = json_decode($json2, TRUE);
        if (json_last_error() !== JSON_ERROR_NONE)
            throw new Exception(json_last_error_msg());

        parent::__construct($array1, $array2);
    }

    /**
     * @throws Exception
     */
    public static function fromFiles(string $file1, string $file2): self
    {
        if (!file_exists($file1))
            throw new FileNotFoundException($file1);
        $json1 = file_get_contents($file1);

        if (!file_exists($file2))
            throw new FileNotFoundException($file2);
        $json2 = file_get_contents($file2);

        return new JsonDiff($json1, $json2);
    }

}
