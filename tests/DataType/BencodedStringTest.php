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
namespace Welhott\Bencode\Tests\DataType;

use PHPUnit_Framework_TestCase;
use Welhott\Bencode\Decode;
use Welhott\Bencode\DataType\BencodedDataType;

/**
 * Class BencodedStringTest
 * @package Welhott\Bencode\Tests\DataType
 */
class BencodedStringTest extends PHPUnit_Framework_TestCase
{
    private static $strings = [
        'these pretzels are making me thirsty',
        'and you wanna be my latex salesman?',
        'i am speechless; i am without speech',
        'another babka?'
    ];

    public function testSingleByteString()
    {
        foreach (self::$strings as $string) {
            $bencoded = new Decode(mb_strlen($string) . ':' . $string);
            $this->assertEquals($string, $bencoded->decode()->getValue());
        }
    }

    public function testMultipleByteStrings()
    {
        $concatenated = '';
        foreach (self::$strings as $string) {
            $concatenated .= mb_strlen($string) . ':' . $string;
        }

        $bencoded = new Decode($concatenated);
        $bencodedData = $bencoded->decode();

        $this->assertTrue(is_array($bencodedData));

        /** @var BencodedDataType $data */
        foreach ($bencodedData as $i => $data) {
            $this->assertEquals(self::$strings[$i], $data->getValue());
        }
    }
}
