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
use Welhott\Bencode\DataType\BencodedInteger;
use Welhott\Bencode\Decode;

/**
 * Class BencodedIntegerTest
 * @package Welhott\Bencode\Tests\DataType
 */
class BencodedIntegerTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testInteger()
    {
        for ($i = -10; $i < 10; $i++) {
            $bencoded = new Decode('i' . $i . 'e');
            $this->assertEquals($i, $bencoded->decode()->getValue());
        }
    }

    /**
     *
     */
    public function testMultipleIntegers()
    {
        $bencoded = new Decode('i-1ei1e');
        $this->assertEquals([new BencodedInteger(-1), new BencodedInteger(1)], $bencoded->decode());
    }

    /**
     *
     */
    public function testMultipleLargeIntegers()
    {
        $time1 = time();
        $time2 = time() * time();

        $bencoded = new Decode('i' . $time1 . 'ei' . $time2 . 'e');
        $this->assertEquals([new BencodedInteger($time1), new BencodedInteger($time2)], $bencoded->decode());
    }

    /**
     * @test
     * @expectedException \Welhott\Bencode\Exception\TokenNotFoundException
     */
    public function testMissingEndDelimiter()
    {
        $bencoded = new Decode('i' . time());
        $bencoded->decode();
    }

    /**
     * @test
     * @expectedException \Welhott\Bencode\Exception\BadDataException
     * @expectedExceptionMessage Integers must be numeric
     */
    public function testNonNumericContent()
    {
        $bencoded = new Decode('ipretzelse');
        $bencoded->decode();
    }

    /**
     * @test Confirm that a single zero is parsed correctly.
     */
    public function testZero()
    {
        $bencoded = new Decode('i0e');
        $this->assertEquals(0, $bencoded->decode()->getValue());
    }

    /**
     * @test Integers cannot have leading zeroes.
     * @expectedException \Welhott\Bencode\Exception\BadDataException
     * @expectedExceptionMessage Integers cannot have leading zeroes
     */
    public function testLeadingZeroes()
    {
        $bencoded = new Decode('i00e');
        $bencoded->decode();
    }

    /**
     * @test The beencoded string i-0e is not valid according to the specification
     * @expectedException \Welhott\Bencode\Exception\BadDataException
     * @expectedExceptionMessage Having -0 in a bencoded integer is not valid.
     */
    public function testMinusZero()
    {
        $bencoded = new Decode('i-00000e');
        $bencoded->decode();
    }
}
