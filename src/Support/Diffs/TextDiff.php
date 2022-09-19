<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Support\Diffs;

use Exception;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use UCRM\Plugins\Support\Diff;

class TextDiff extends Diff
{

    public function __construct(string $text1, string $text2, string $separator = PHP_EOL)
    {
        $array1 = explode($separator, $text1);
        $array2 = explode($separator, $text2);

        parent::__construct($array1, $array2);
    }

    /**
     * @throws Exception
     */
    public static function fromFiles(string $file1, string $file2): self
    {
        if (!file_exists($file1))
            throw new FileNotFoundException($file1);
        $text1 = file_get_contents($file1);

        if (!file_exists($file2))
            throw new FileNotFoundException($file2);
        $text2 = file_get_contents($file2);

        return new TextDiff($text1, $text2);
    }

}
