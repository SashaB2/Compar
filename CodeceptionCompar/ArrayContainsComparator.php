<?php

namespace Compar\CodeceptionCompar;

use Compar\CompareSign;
use Codeception\Util\ArrayContainsComparator as CodeceptionArrayContainsComparator;

class ArrayContainsComparator extends CodeceptionArrayContainsComparator implements CompareSign
{
    public function containsArray(array $needle)
    {
        return $needle == $this->arrayIntersectRecursive($needle, $this->haystack);
    }

    /**
     * @param mixed $arr1
     * @param mixed $arr2
     *
     * @return array|bool
     * @author nleippe@integr8ted.com
     * @author tiger.seo@gmail.com
     * @link https://www.php.net/manual/en/function.array-intersect-assoc.php#39822
     *
     */
    private function arrayIntersectRecursive($arr1, $arr2)
    {
        if (!is_array($arr1) || !is_array($arr2)) {
            return false;
        }
        // if it is not an associative array we do not compare keys
        if ($this->arrayIsSequential($arr1) && $this->arrayIsSequential($arr2)) {
            return $this->sequentialArrayIntersect($arr1, $arr2);
        }
        return $this->associativeArrayIntersect($arr1, $arr2);
    }

    /**
     * This array has sequential keys?
     *
     * @param array $array
     *
     * @return bool
     */
    private function arrayIsSequential(array $array)
    {
        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    private function sequentialArrayIntersect(array $arr1, array $arr2)
    {
        $ret = [];

        // Do not match the same item of $arr2 against multiple items of $arr1
        $matchedKeys = [];
        foreach ($arr1 as $key1 => $value1) {

            if (!is_array($value1) && is_string($value1)) {
                $expNeed = explode(" ", $value1);
                $sign = end($expNeed);
                if (in_array($sign, $this::SIGNS)) {
                    $value1 = substr($value1, 0, (strlen($sign) + 1) * -1);
                }
            }
            foreach ($arr2 as $key2 => $value2) {
                if (isset($matchedKeys[$key2])) {
                    continue;
                }

                $return = $this->arrayIntersectRecursive($value1, $value2);
                if ($return !== false && $return == $value1) {
                    $ret[$key1] = $return;
                    $matchedKeys[$key2] = true;
                    break;
                }

                if ($this->isEqualValue($value1, $value2, $sign ?? '')) {
                    $ret[$key1] = $value1 . (!empty($sign) ? " $sign" : '');
                    $matchedKeys[$key2] = true;
                    break;
                }
            }
        }

        return $ret;
    }

    /**
     * @param array $arr1
     * @param array $arr2
     *
     * @return array|bool|null
     */
    private function associativeArrayIntersect(array $arr1, array $arr2)
    {
        $tempKeySign = [];
        foreach ($arr1 as $key1 => $value1) {
            if (!is_array($value1)) {
                $expNeed = explode(" ", $key1);
                $sign = end($expNeed);
                if (in_array($sign, $this::SIGNS)) {
                    $pureKey = substr($key1, 0, (strlen($sign) + 1) * -1);
                    $arr1[$pureKey] = $value1;
                    unset($arr1[$key1]);

                    $tempKeySign[$pureKey] = $sign;
                }
            }
        }

        $commonKeys = array_intersect(array_keys($arr1), array_keys($arr2));

        $ret = [];
        foreach ($commonKeys as $key) {
            $return = $this->arrayIntersectRecursive($arr1[$key], $arr2[$key]);
            if ($return !== false) {
                $ret[$key] = $return;
                continue;
            }

            if ($this->isEqualValue($arr1[$key], $arr2[$key], $tempKeySign[$key] ?? "")) {
                $ret[$key . (!empty($tempKeySign[$key]) ? " $tempKeySign[$key]" : '')] = $arr1[$key];
            }
        }

        if (empty($commonKeys)) {
            foreach ($arr2 as $arr) {
                $return = $this->arrayIntersectRecursive($arr1, $arr);
                if ($return && $return == $arr1) {
                    return $return;
                }
            }
        }

        if (count($ret) < min(count($arr1), count($arr2))) {
            return null;
        }

        return $ret;
    }

    private function isEqualValue($val1, $val2, $sign = '')
    {
        if (is_numeric($val1)) {
            $val1 = (string)$val1;
        }

        if (is_numeric($val2)) {
            $val2 = (string)$val2;
        }

        if (in_array($sign, $this::SIGNS)) {
            switch ($sign) {
                case $this::SIGN['>']:
                    return $val2 > $val1;
                case $this::SIGN['<']:
                    return $val2 < $val1;
                case $this::SIGN['<=']:
                    return $val2 <= $val1;
                case $this::SIGN['>=']:
                    return $val2 >= $val1;
                case $this::SIGN['!==']:
                    return $val2 !== $val1;
                case $this::SIGN['like']:
                    return $this->like_match($val1, $val2);
                case $this::SIGN['isNull']:
                    return is_null($val2);
                case $this::SIGN['isNotNull']:
                    return !is_null($val2);
            }
        }

        return $val1 === $val2;
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
