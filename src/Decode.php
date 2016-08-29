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
use Welhott\Bencode\Exception\BadDataException;
use Welhott\Bencode\Exception\TokenNotFoundException;

/**
 * Class Bencode
 * @package Welhott\Bencode
 */
class Decode
{
    /**
     * The string/file contents of what we want to decode.
     * @var string
     */
    private $bencoded = '';

    /**
     * The current position of the pointer. Position points to a single char in $this->bencoded. That single char will
     * either mean that we have a Dictionary, List, Integer or String data type that we must do something with.
     * @var int
     */
    private $position = 0;

    /**
     * The length of $this->bencoded.
     * This is mostly just to avoid repeated calls in decode() function to mb_strlen().
     * @var int
     */
    private $length = 0;

    /**
     * Bencode constructor.
     * @param string $bencoded The Bencoded string that we want to decode.
     */
    public function __construct(string $bencoded)
    {
        $this->bencoded = $bencoded;
        $this->length = mb_strlen($this->bencoded);
    }

    /**
     * Parses/Decoded the bencoded string in this object into objects.
     *
     * This method calls recursive() sucessively after it returns until there are no more chars to process and will
     * return an array of objects of various data types or a single data type (in case there is only one item in the
     * bencoded string).
     *
     * Since the Bencode format is only (that I know of) used in torrent files the first element will always be a
     * dictionary of values so, technically, we could always return a dictionary and avoid having the recursive()
     * method. However I would prefer to have this more flexible.
     *
     * @return array|mixed An array of objects of all the discovered data types or a single object with a data type.
     */
    public function decode()
    {
        $data = [];

        while ($this->position < $this->length) {
            $data[] = $this->recursive();
        }

        return count($data) == 1 ? reset($data) : $data;
    }

    /**
     * Processes each char in the Bencoded string and matches it with the correct data type.
     * @return BencodedDataType An object with the matched data type (Dictionary, List, Integer or String).
     */
    private function recursive() : BencodedDataType
    {
        switch ($this->bencoded[$this->position]) {
            case BencodedDictionary::START_DELIMITER: {
                return $this->readDictionary();
            }

            case BencodedList::START_DELIMITER: {
                return $this->readList();
            }

            case BencodedInteger::START_DELIMITER: {
                return $this->readInteger();
            }

            default: {
                return $this->readString();
            }
        }
    }

    /**
     * Process an integer.
     *
     * The format of an integer data type is: i[NUMBER]e
     *
     * i » Starting Delimiter
     * [NUMBER] » The value of the integer (can be negative value of course)
     * e » Ending Delimiter
     *
     * The strategy for obtaining [NUMBER] is done by discovering the position of 'e' and obtaining the substring
     * between 'i' and 'e'. After this is done, the pointer (defined by $this->position) is set to the char after the
     * ending delimiter.
     *
     * @return BencodedInteger An integer object.
     *
     * @throws BadDataException
     * @throws TokenNotFoundException
     */
    private function readInteger() : BencodedInteger
    {
        $this->position++;

        $token = mb_strpos($this->bencoded, BencodedInteger::END_DELIMITER, $this->position);

        if ($token === false) {
            $message = sprintf('Token \'%s\' not found while parsing Integer value', BencodedInteger::END_DELIMITER);
            throw new TokenNotFoundException($message);
        }

        $integer = mb_substr($this->bencoded, $this->position, $token - $this->position);

        if(mb_strlen($integer) > 1 && $integer[0] == '0') {
            $message = sprintf('Integers cannot have leading zeroes. \'%s\' starts with zero.', $integer);
            throw new BadDataException($message);
        }

        if(intval($integer) === 0 && $integer[0] == '-') {
            $message = sprintf('Having -0 in a bencoded integer is not valid. You have %s', $integer);
            throw new BadDataException($message);
        }

        if (!is_numeric($integer)) {
            $message = sprintf('Integer \'%s\' is not a numeric value. Integers must be numeric.', $integer);
            throw new BadDataException($message);
        }

        $this->position = $token + 1;
        return new BencodedInteger($integer);
    }

    /**
     * Process a string. Strings are the default data type in a way i.e. if the char is not the START DELIMITER of a
     * Dictionary (d), List (l) or Integer (i) then it's for sure a string.
     *
     * The format of a string data type is: [LENGTH]:[STRING].
     *
     * [LENGTH] » The length of the string we will find
     * : » The separator between the length and the actual string
     * [STRING] » The string that was bencoded
     *
     * The strategy for discovering [STRING] is to discover the position of ':' and obtain the substring between
     * $this->position and the position of ':'. This will give us the length of the string. After we will need to
     * obtain the substring between the positon of ':' (+1 of course) plus the length that we previously discovered.
     *
     * After this is done, the pointer (defined by $this->position) is set to the char the length of the string.
     *
     * @return BencodedString A string object.
     * @throws BadDataException
     * @throws TokenNotFoundException
     */
    private function readString() : BencodedString
    {
        $token = mb_strpos($this->bencoded, BencodedString::END_DELIMITER, $this->position);

        if ($token === false) {
            $message = sprintf('Token \'%s\' not found while parsing String value', BencodedString::END_DELIMITER);
            throw new TokenNotFoundException($message);
        }

        $length = mb_substr($this->bencoded, $this->position, $token - $this->position);

        $token++;
        $this->position = $length + $token;

        $string = mb_substr($this->bencoded, $token, $length);

        if (mb_strlen($string) != $length) {
            $message = sprintf('Length of string %d does not match expected length of %d', mb_strlen($string), $length);
            throw new BadDataException($message);
        }

        return new BencodedString($string);
    }

    /**
     * Process a dictionary.
     *
     * The format of the dictionary data type is: d[OTHER DATA TYPES]e
     *
     * d » The starting delimiter.
     * [OTHER DATA TYPES] » A dictionary can contain multiple other data types.
     * e » The ending delimiter.
     *
     * The strategy for discovering [OTHER DATA TYPES] is by performing more recursive calls to search for the other
     * data types in the dictionary until 'e' is found to finish off the dictionary. The dictionary composed by a key
     * and a value so every two datatypes found we have a Key » Value pair.
     *
     * @return BencodedDictionary A dictionary object.
     * @throws BadDataException
     * @throws TokenNotFoundException
     */
    private function readDictionary() : BencodedDictionary
    {
        $data = [];
        $zebra = 0;
        $key = '';

        $this->position++;

        while ($this->bencoded[$this->position] != BencodedDictionary::END_DELIMITER) {
            if ($zebra % 2 == 0) {
                $key = $this->recursive()->getValue();

                if (!is_string($key) && !is_numeric($key)) {
                    $message = sprintf('Dictionary keys must be a string. This is is an \'%s\'', gettype($key));
                    throw new BadDataException($message);
                }
            } else {
                $data[$key] = $this->recursive();
            }

            $zebra++;

            if ($this->position >= $this->length
                && (!isset($this->bencoded[$this->position])
                    || $this->bencoded[$this->position] != BencodedDictionary::END_DELIMITER)
            ) {
                $message = sprintf('End of data reached. Ending delimiter for dictionary data type not found.');
                throw new TokenNotFoundException($message);
            }
        }

        if ($zebra % 2 !== 0) {
            $message = sprintf('The dictionary contains \'%d\' value. It must be an even value', $zebra);
            throw new BadDataException($message);
        }

        $this->position++;
        return new BencodedDictionary($data);
    }

    /**
     * Process a list.
     *
     * A list is very similar to a dictionary but its values are not Key » Value pairs. It can, however, contain other
     * dictionaries and lists.
     *
     * The format of a list of the following: l[OTHER DATA TYPES]e
     *
     * The strategy for discovering [OTHER DATA TYPES] is by performing more recursive calls to search for the other
     * data types in the list until 'e' is found.
     *
     * @return BencodedList A list object.
     * @throws TokenNotFoundException
     */
    private function readList() : BencodedList
    {
        $this->position++;
        $list = [];

        while ($this->bencoded[$this->position] != BencodedList::END_DELIMITER) {
            $list[] = $this->recursive();

            if ($this->position >= $this->length
                && (!isset($this->bencoded[$this->position])
                    || $this->bencoded[$this->position] != BencodedList::END_DELIMITER)
            ) {
                $message = sprintf('End of data reached. Ending delimiter for list data type not found.');
                throw new TokenNotFoundException($message);
            }
        }

        $this->position++;
        return new BencodedList($list);
    }
}
