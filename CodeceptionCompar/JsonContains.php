<?php

namespace Compar\CodeceptionCompar;

use Compar\PhpUnitCompar\ArraySignComparator;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory;
use Codeception\PHPUnit\Constraint\JsonContains as CodeceptionJsonContains;

class JsonContains extends CodeceptionJsonContains
{
    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other Value or object to evaluate.
     *
     * @return bool
     */
    protected function matches($other): bool
    {
        $jsonResponseArray = new JsonArray($other);
        if (!is_array($jsonResponseArray->toArray())) {
            throw new \PHPUnit\Framework\AssertionFailedError('JSON response is not an array: ' . $other);
        }

        if ($jsonResponseArray->containsArray($this->expected)) {
            return true;
        }

        $comparator = new ArraySignComparator();
        $comparator->setFactory(new Factory);
        try {
            $comparator->assertEquals($this->expected, $jsonResponseArray->toArray());
        } catch (ComparisonFailure $failure) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Response JSON does not contain the provided JSON\n",
                $failure
            );
        }

        return false;
    }
}
