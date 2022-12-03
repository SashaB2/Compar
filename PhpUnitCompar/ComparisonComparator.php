<?php

namespace Compar\PhpUnitCompar;

use Compar\CompareSign;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\ScalarComparator;

class ComparisonComparator extends ScalarComparator implements CompareSign
{
    /**
     * Returns whether the comparator can compare two values with a MATH SYMBOL.
     *
     * @param StrForCompare $expected The first value to compare
     * @param mixed $actual The second value to compare
     *
     * @return bool
     *
     * @since  Method available since Release 3.6.0
     */
    public function accepts($expected, $actual)
    {
        return (($expected instanceof StrForCompare) &&
            (is_scalar($actual) xor null === $actual));
    }

    /**
     * Asserts that two values are equal.
     *
     * @param mixed $expected First value to compare
     * @param mixed $actual Second value to compare
     * @param float $delta Allowed numerical distance between two values to consider them equal
     * @param bool $canonicalize Arrays are sorted before comparison when set to true
     * @param bool $ignoreCase Case is ignored when set to true
     *
     * @throws ComparisonFailure
     */
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false)/*: void*/
    {
        $val1 = $expected->getValue();
        $sign = $expected->getSign();
        $val2 = $actual;
        $result = false;

        if ((is_string($val1) && !is_bool($val2)) || (is_string($val2) && !is_bool($val1))) {
            $val1 = (string) $val1;
            $val2   = (string) $val2;

            //TODO:think in future
            if ($sign) {
                $val1 = strtolower($val1);
                $val2   = strtolower($val2);
            }
        }

        switch ($sign) {
            case $this::SIGN['>']:
                $result = !($val2 > $val1);
                break;
            case $this::SIGN['<']:
                $result = !($val2 < $val1);
                break;
            case $this::SIGN['<=']:
                $result = !($val2 <= $val1);
                break;
            case $this::SIGN['>=']:
                $result = !($val2 >= $val1);
                break;
            case $this::SIGN['!==']:
                $result = !($val2 !== $val1);
                break;
            case $this::SIGN['like']:
                $result = !($this->like_match($val1, $val2));
                break;
            case $this::SIGN['isNull']:
                $result = !is_null($val2);
                break;
            case $this::SIGN['isNotNull']:
                $result = is_null($val2);
                break;
        }

        //if compare with math symbol then stop checking
        if($result) {
            throw new ComparisonFailure(
                $val1,
                $val2,
                $this->exporter->export($val1),
                $this->exporter->export($val2),
                false,
                "Failed comparing that two strings are $sign."
            );
        }

        if ($val2 !== $val1 && is_string($val1) && is_string($val2)) {
            throw new ComparisonFailure(
                $val1,
                $val2,
                $this->exporter->export($val1),
                $this->exporter->export($val2),
                false,
                'Failed asserting that two strings are equal.'
            );
        }

        if ($val2 != $val1) {
            throw new ComparisonFailure(
                $val1,
                $val2,
                // no diff is required
                '',
                '',
                false,
                sprintf(
                    'Failed asserting that %s matches expected %s.',
                    $this->exporter->export($val1),
                    $this->exporter->export($val2)
                )
            );
        }
    }

    /**
     * SQL Like operator in PHP.
     * Returns TRUE if match else FALSE.
     * @param string $pattern
     * @param string $subject
     * @return bool
     */
    private function like_match($pattern, $subject)
    {
        $pattern = str_replace('%', '.*', preg_quote($pattern, '/'));
        return (bool)preg_match("/^{$pattern}$/i", $subject);
    }
}
