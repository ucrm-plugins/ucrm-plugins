<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Support\Diffs;

use UCRM\Plugins\Support\Diff;

class ArrayDiff extends Diff
{

    public function __construct(array $array1, array $array2)
    {
        parent::__construct($array1, $array2);
    }


}
