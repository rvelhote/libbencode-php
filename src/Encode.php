<?php
/**
 * MIT License
 *
 * Copyright (c) 2016 Ricardo Velhote
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
namespace Welhott\Bencode;

use Welhott\Bencode\DataType\BencodedDictionary;
use Welhott\Bencode\DataType\BencodedInteger;
use Welhott\Bencode\DataType\BencodedList;
use Welhott\Bencode\DataType\BencodedString;

/**
 * Class Encode
 * @package Welhott\Bencode
 */
class Encode
{
    /**
     * @var array
     */
    private $data;

    /**
     * Encode constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function encode() : string
    {
        $bencoded = '';

        foreach($this->data as $data) {
            $bencoded .= $this->recursive($data);
        }

        return $bencoded;
    }

    /**
     * @param $data
     * @return string
     */
    private function recursive($data) : string
    {
        if(is_int($data)) {
            return $this->createInteger($data);
        } else if(is_string($data)) {
            return $this->createString($data);
        } else if(is_array($data) && isset($data[0])) {
            return $this->createList($data);
        } else {
            return $this->createDictionary($data);
        }
    }

    /**
     * @param int $data
     * @return string
     */
    private function createInteger(int $data) : string
    {
        return BencodedInteger::START_DELIMITER.$data.BencodedInteger::END_DELIMITER;
    }

    /**
     * @param string $data
     * @return string
     */
    private function createString(string $data) : string
    {
        return mb_strlen($data).BencodedString::END_DELIMITER.$data;
    }

    /**
     * @param array $data
     * @return string
     */
    private function createList(array $data) : string
    {
        $list = '';

        foreach($data as $value) {
            $list .= $this->recursive($value);
        }

        return BencodedList::START_DELIMITER.$list.BencodedList::END_DELIMITER;
    }

    /**
     * @param array $data
     * @return string
     */
    private function createDictionary(array $data) : string
    {
        $list = '';

        ksort($data, SORT_STRING);

        foreach($data as $key => $value) {
            $list .= ($this->createString($key).$this->recursive($value));
        }

        return BencodedDictionary::START_DELIMITER.$list.BencodedDictionary::END_DELIMITER;
    }
}
