<?php

namespace Compar\PhpUnitCompar;


use Compar\CompareSign;

class StrForCompare implements CompareSign
{
    private $key;
    private $pureKey;
    private $value;
    private $pureValue;
    private $sign = '';

    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
        $this->pureKey = $key;
        $this->pureValue = $value;
    }

    /**
     * @return StrForCompare|string
     * if $key has a sign then overwrite $pureKey
     * if $value has a sign then overwrite $pureValue
     * $ $key or $value has a sign then set $sign
     */
    public function ifIsSign()
    {
        //only string can contain a sign.
        if (is_string($this->key)) {
            $expNeed = explode(" ", $this->key);
            $sign = end($expNeed);
            if (in_array($sign, $this::SIGNS)) {
                $this->pureKey = substr($this->pureKey, 0, (strlen($sign) + 1) * -1);
                $this->sign = $sign;
            }
            //if key has the sign or is string then value does not have the sign
            return $this;
        }

        //only string can contain a sign.
        if (is_string($this->value)) {
            $expNeed = explode(" ", $this->value);
            $sign = end($expNeed);
            if (in_array($sign, $this::SIGNS)) {
                $this->pureValue = substr($this->value, 0, (strlen($sign) + 1) * -1);
                $this->sign = $sign;
            }
        }

        return $this;
    }

    public function getObjectIfSign()
    {
        if ($this->sign != '') {
            return $this;
        } else {
            return $this->getValue();
        }
    }

    public function getKey()
    {
        return $this->pureKey;
    }

    public function getValue()
    {
        return $this->pureValue;
    }

    public function getSign()
    {
        return $this->sign ?? '';
    }

    public function getNoPureKey()
    {
        if (is_string($this->getKey())) {
            return $this->getKey() . ($this->getSign() === '' ? '' : ' ' . $this->getSign());
        }

        return $this->getKey();
    }

    public function getNoPureValue()
    {
        if (is_string($this->getValue())) {
            return $this->getValue() . ($this->getSign() === '' ? '' : ' ' . $this->getSign());
        }

        return $this->getValue();
    }
}
