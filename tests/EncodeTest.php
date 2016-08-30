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
namespace Welhott\Bencode\Tests;

use PHPUnit_Framework_TestCase;
use Welhott\Bencode\DataType\BencodedDictionary;
use Welhott\Bencode\DataType\BencodedInteger;
use Welhott\Bencode\DataType\BencodedList;
use Welhott\Bencode\DataType\BencodedString;
use Welhott\Bencode\Decode;
use Welhott\Bencode\Encode;

/**
 * Class EncodeTest
 * @package Welhott\Bencode\Tests
 */
class EncodeTest extends PHPUnit_Framework_TestCase
{
    /**
     * This test will encode the dataset and decode it to make sure the result if equal to the initial state.
     * @test Make sure the encoding has the expected format.
     */
    public function testEncoding()
    {
        $expected = [
            new BencodedInteger(time()),
            new BencodedString('String'),
            new BencodedList([new BencodedString('ListItem1'), new BencodedString('ListItem2')]),
            new BencodedDictionary([
                'Key1' => new BencodedString('Value1'),
                'Key2' => new BencodedString('Value2')
            ]),
        ];

        $bencode = new Encode($expected);
        $bencoded = $bencode->encode();

        $bdecode = new Decode($bencoded);
        $bdecoded = $bdecode->decode();

        $this->assertEquals($expected, $bdecoded);exit;
    }
}
