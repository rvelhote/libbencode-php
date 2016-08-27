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
namespace Welhott\Bencode\DataType;

use DateTime;

/**
 * Class BencodedInteger
 * @package Welhott\Bencode\DataType
 */
class BencodedInteger implements BencodedDataType
{
    /**
     *
     */
    const START_DELIMITER = 'i';

    /**
     *
     */
    const END_DELIMITER = 'e';

    /**
     * @var int
     */
    private $value;

    /**
     * BencodedInteger constructor.
     * @param int $value
     */
    public function __construct(int $value)
    {
        $this->value = $value;
    }

    /**
     * The integer value of this object.
     * @return int An integer.
     */
    public function getValue() : int
    {
        return $this->value;
    }

    /**
     * Obtains this integer value as a DateTime object. Dates in Bencoded strings are stored as unix timestamps.
     * @return DateTime A DateTime object representation of the a date.
     */
    public function getDate() : DateTime
    {
        return new DateTime($this->value);
    }

    /**
     * Returns the current integer as a human-readable size. This is for usage in dictionary values that represent
     * filesizes. We cannot determine these so we trust the developer to invoke the correct method.
     *
     * @param int $decimals The number of decimal places that the human-readable string should have.
     * @return string A human-readable size of this integer (which should represent a filezise).
     *
     * @see http://jeffreysambells.com/2012/10/25/human-readable-filesize-php
     */
    public function getHumanFilesize(int $decimals = 2) : string
    {
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($this->value) - 1) / 3);
        return sprintf("%.{$decimals}f", $this->value / pow(1024, $factor)) . $size[$factor];
    }
}
