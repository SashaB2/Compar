<?php

namespace Compar\CodeceptionCompar;

use Codeception\Util\JsonArray as CodeceptionJsonArray;

class JsonArray extends CodeceptionJsonArray
{
    public function containsArray(array $needle)
    {
        return (new ArrayContainsComparator($this->jsonArray))->containsArray($needle);
    }
}
