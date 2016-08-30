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
     * The raw array with the data the user wants to encode.
     * @var array
     */
    private $raw = [];

    /**
     * The final Bencoded string will be stored for reuse here.
     * @var string
     */
    private $bencoded = '';

    /**
     * Encode constructor.
     * @param array $raw
     */
    public function __construct(array $raw)
    {
        $this->raw = $raw;
    }

    /**
     * Returns the raw array dataset that will be encoded.
     * @return array The raw array.
     */
    public function getRawData() : array
    {
        return $this->raw;
    }

    /**
     * Returns the already bencoded string (it does not reencode).
     * @return string The Bencoded string.
     */
    public function getEncodedData() : string
    {
        return $this->bencoded;
    }

    /**
     * Recursively loop through the dataset and Bencode each item.
     * @return string A Bencoded string representing the whole dataset.
     */
    public function encode() : string
    {
        $bencoded = '';

        foreach($this->raw as $data) {
            $bencoded .= $this->recursive($data);
        }

        $this->bencoded = $bencoded;
        return $bencoded;
    }

    /**
     * Parses the current value of the dataset depending on its data type.
     * @param mixed $data data that we want to analyize and encode.
     * @return string The Bencoded data type in the correct format.
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
     * Creates a bencoded integer.
     * Integers have the following format: i[INT]e
     *
     * i » Start delimiter of an integer.
     * [INT] » The integer that we want to represent.
     * e » The end delimiter of the integer.
     *
     * @param int $integer The integer that we want to encode.
     * @return string A Bencoded integer value as a string.
     */
    private function createInteger(int $integer) : string
    {
        return BencodedInteger::START_DELIMITER.$integer.BencodedInteger::END_DELIMITER;
    }

    /**
     * Creates a Bencoded string.
     * String have the following format: 4:test
     *
     * 4 » The length of the string
     * : » The string delimiter which splits the lenth and the actual contents
     * [STRING] » A string with the same length as above.
     *
     * @param string $string The string we want to encode. Length is calculated automatically.
     * @return string A Bencoded string value.
     */
    private function createString(string $string) : string
    {
        return mb_strlen($string).BencodedString::END_DELIMITER.$string;
    }

    /**
     * Creates a Bencoded list.
     * Lists have the following format: l[... OTHER DATA TYPES ...]e
     *
     * l » Starting delimiter token
     * A number of values in which the value can be an Integer, a String, a list or a dictionary.
     * e » End delimiter token
     *
     * @param array $list An array of items that belong to the list we want to encode.
     * @return string A Bencoded string representing the list.
     */
    private function createList(array $list) : string
    {
        $encodedList = '';

        foreach($list as $value) {
            $encodedList .= $this->recursive($value);
        }

        return BencodedList::START_DELIMITER.$encodedList.BencodedList::END_DELIMITER;
    }

    /**
     * Creates a Bencoded dictionary.
     * Dictionaries have the following format: d[... OTHER DATA TYPES ...]e
     *
     * d » Starting delimiter token
     * A number of Key-Value pairs in which the value can be an Integer, a String, a list or another dictionary.
     * e » End delimiter token
     *
     * @param array $dictionary The array of the dictionary that we wish to encode.
     * @return string The dictionary and it's key-value pairs of other data types.
     */
    private function createDictionary(array $dictionary) : string
    {
        $encodedDictionary = '';

        ksort($dictionary, SORT_STRING);

        foreach($dictionary as $key => $value) {
            $encodedDictionary .= ($this->createString($key).$this->recursive($value));
        }

        return BencodedDictionary::START_DELIMITER.$encodedDictionary.BencodedDictionary::END_DELIMITER;
    }
}
