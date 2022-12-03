<?php

namespace Compar;

interface CompareSign
{
    //think about to ignore upper case
    const SIGNS = [
        '===', //just compare, ignore case, when the last doe no use like a compare sign
        '>',
        '<',
        '>=',
        '<=',
        '!==',
        'like',
        'isNull',
        'isNotNull',
    ];
    const SIGN = [
        '===' => '===',
        '>' => '>',
        '<' => '<',
        '>=' => '>=',
        '<=' => '<=',
        '!==' => '!==',
        'like' => 'like',
        'isNull' => 'isNull',
        'isNotNull' => 'isNotNull',
    ];
}
