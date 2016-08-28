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
use Welhott\Bencode\DataType\BencodedInteger;

/**
 * Class BencodedDictionaryTest
 * @package Welhott\Bencode\Tests\DataType
 */
class BencodedDictionaryTest extends PHPUnit_Framework_TestCase
{
    public function testDictionary()
    {
        $expected = [
            'pretzels' => new BencodedInteger(-100)
        ];

        $bencoded = new Decode('d8:pretzelsi-100ee');
        $decoded = $bencoded->decode();

        $this->assertEquals($expected, $decoded->getValue());
        $this->assertEquals($expected['pretzels'], $decoded['pretzels']);

        $this->assertTrue(isset($decoded['pretzels']));
        $this->assertFalse(isset($decoded['unset pretzels']));
    }

    public function testMissingEndDelimiter()
    {
        $this->expectException('\\Welhott\\Bencode\\Exception\\TokenNotFoundException');

        $bencoded = new Decode('d8:pretzelsi-100e');
        $bencoded->decode();
    }

    public function testUnevenDataset()
    {
        $this->expectException('\\Welhott\\Bencode\\Exception\\BadDataException');

        $bencoded = new Decode('d8:pretzelse');
        $bencoded->decode();
    }
}
