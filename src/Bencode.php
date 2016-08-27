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

use Welhott\Bencode\DataType\BencodedDataType;
use Welhott\Bencode\DataType\BencodedDictionary;
use Welhott\Bencode\DataType\BencodedInteger;
use Welhott\Bencode\DataType\BencodedList;
use Welhott\Bencode\DataType\BencodedString;

/**
 * Class Bencode
 * @package Welhott\Bencode
 */
class Bencode
{
    /**
     * @var string
     */
    private $bencoded = '';

    /**
     * @var int
     */
    private $position = 0;

    /**
     * Bencode constructor.
     * @param string $bencoded
     */
    public function __construct(string $bencoded)
    {
        $this->bencoded = $bencoded;
    }

    /**
     * @return BencodedDataType
     */
    private function recursive() : BencodedDataType
    {
        switch ($this->bencoded[$this->position]) {
            case BencodedDictionary::START_DELIMITER: {
                return $this->readDictionary();
            }

            case BencodedList::START_DELIMITER:
                return $this->readList();

            case BencodedInteger::START_DELIMITER: {
                return $this->readInteger();
            }

            default: {
                return $this->readString();
            }
        }
    }

    public function decode()
    {
        $data = [];

        while($this->position < mb_strlen($this->bencoded)) {
            $data[] = $this->recursive();
        }

        return count($data) == 1 ? reset($data) : $data;
    }

    /**
     * @return BencodedInteger
     */
    private function readInteger() : BencodedInteger
    {
        $this->position++;

        $token = mb_strpos($this->bencoded, BencodedInteger::END_DELIMITER, $this->position);
        $string = mb_substr($this->bencoded, $this->position, $token - $this->position);

        $this->position = $token + 1;
        return new BencodedInteger($string);
    }

    /**
     * @return BencodedString
     */
    private function readString() : BencodedString
    {
        $token = mb_strpos($this->bencoded, BencodedString::END_DELIMITER, $this->position);
        $length = mb_substr($this->bencoded, $this->position, $token - $this->position);

        $token++;
        $this->position = $length + $token;

        $string = mb_substr($this->bencoded, $token, $length);
        return new BencodedString($string);
    }

    /**
     * @return BencodedDictionary
     */
    private function readDictionary() : BencodedDictionary
    {
        $data = [];
        $zebra = 0;
        $key = '';

        $this->position++;

        while($this->bencoded[$this->position] != BencodedDictionary::END_DELIMITER) {
            if($zebra % 2 == 0) {
                $key = $this->recursive()->getValue();
            } else {
                $data[$key] = $this->recursive();
            }
            $zebra++;
        }

        $this->position++;
        return new BencodedDictionary($data);
    }

    /**
     * @return BencodedList
     */
    private function readList() : BencodedList
    {
        $this->position++;
        $list = [];

        while($this->bencoded[$this->position] != BencodedList::END_DELIMITER) {
            $list[] = $this->recursive();
        }

        $this->position++;
        return new BencodedList($list);
    }
}
