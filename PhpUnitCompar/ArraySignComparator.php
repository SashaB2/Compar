<?php declare(strict_types=1);
/*
 * This file extends sebastian/comparator.
 *
 * (c) Sviridenko Oleksandr <sviridenko.a@rozetka.ua>
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 */

namespace Compar\PhpUnitCompar;

use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\ComparisonFailure;
use function array_key_exists;
use function is_array;
use function sort;
use function sprintf;
use function str_replace;
use function trim;

/**
 * Compares arrays for equality and math symbols.
 *
 * Arrays are equal if they contain the same key-value pairs.
 * The order of the keys does not matter.
 * The types of key-value pairs do not matter.
 * Math Symbols >, <, >= ... to check dynamic value of POST, PUT response like created_at, updated_at
 */
class ArraySignComparator extends Comparator
{
    public function accepts($expected, $actual, $sign = '')
    {
        return is_array($expected) && is_array($actual);
    }

    /**
     * Asserts that two arrays are equal.
     *
     * @param mixed $expected     First value to compare
     * @param mixed $actual       Second value to compare
     * @param float $delta        Allowed numerical distance between two values to consider them equal
     * @param bool  $canonicalize Arrays are sorted before comparison when set to true
     * @param bool  $ignoreCase   Case is ignored when set to true
     * @param array $processed    List of already processed elements (used to prevent infinite recursion)
     *
     * @throws ComparisonFailure
     */
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false, $sign = '', array &$processed = [])/*: void*/
    {
        $remaining = $actual;
        $actualAsString = "Array (\n";
        $expectedAsString = "Array (\n";
        $equal = true;

        foreach ($expected as $key => $value) {
            $strForCompare = (new StrForCompare($key, $value))->ifIsSign();

            unset($remaining[$strForCompare->getKey()]);

            if (!array_key_exists($strForCompare->getKey(), $actual)) {
                $expectedAsString .= sprintf(
                    "    %s => %s\n",
                    $this->exporter->export($strForCompare->getNoPureKey()),
                    $this->exporter->shortenedExport($strForCompare->getNoPureValue())
                );

                $equal = false;

                continue;
            }

            try {
                $this->factory->register(new ArraySignComparator());
                $this->factory->register(new ComparisonComparator());

                $comparator = $this->factory->getComparatorFor($strForCompare->getObjectIfSign(), $actual[$strForCompare->getKey()]);
                $comparator->assertEquals($strForCompare->getObjectIfSign(), $actual[$strForCompare->getKey()], $delta, $canonicalize, $ignoreCase, $sign, $processed);

                $expectedAsString .= sprintf(
                    "    %s => %s\n",
                    $this->exporter->export($strForCompare->getNoPureKey()),
                    $this->exporter->shortenedExport($strForCompare->getNoPureValue())
                );

                $actualAsString .= sprintf(
                    "    %s => %s\n",
                    $this->exporter->export($strForCompare->getKey()),
                    $this->exporter->shortenedExport($actual[$strForCompare->getKey()])
                );
            } catch (ComparisonFailure $e) {
                $expectedAsString .= sprintf(
                    "    %s => %s\n",
                    $this->exporter->export($strForCompare->getNoPureKey()),
                    $e->getExpectedAsString() ? $this->indent($e->getExpectedAsString()) : $this->exporter->shortenedExport($e->getExpected())
                );

                $actualAsString .= sprintf(
                    "    %s => %s\n",
                    $this->exporter->export($strForCompare->getKey()),
                    $e->getActualAsString() ? $this->indent($e->getActualAsString()) : $this->exporter->shortenedExport($e->getActual())
                );

                $equal = false;
            }
        }

        foreach ($remaining as $key => $value) {
            $actualAsString .= sprintf(
                "    %s => %s\n",
                $this->exporter->export($key),
                $this->exporter->shortenedExport($value)
            );

            $equal = false;
        }

        $expectedAsString .= ')';
        $actualAsString .= ')';

        if (!$equal) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                $expectedAsString,
                $actualAsString,
                false,
                'Failed asserting that two arrays are equal.'
            );
        }
    }

    protected function indent($lines)
    {
        return trim(str_replace("\n", "\n    ", $lines));
    }
}
