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
use Welhott\Bencode\DataType\BencodedDataType;
use Welhott\Bencode\Decode;
use Welhott\Bencode\Encode;

class BencodeTest extends PHPUnit_Framework_TestCase
{
    public function testBencode()
    {
        $content = 'd8:announce23:http://torrent.tracker17:comment7:Comment10:created by25:Transmission/2.82 (14160)13:creation datei1471991229e8:encoding5:UTF-84:infod6:lengthi9e4:name11:torrent.txt12:piece lengthi32768e6:pieces1:X7:privatei1eee';
        $bencoded = new Decode($content);

        /** @var BencodedDataType[] $decoded */
        $decoded = $bencoded->decode();

        $this->assertEquals($decoded['announce']->value(), 'http://torrent.tracker1');
        $this->assertEquals($decoded['comment']->value(), 'Comment');
    }

    public function testEncode()
    {
        $dataset = [time(), 'String', ['ListItem1', 'ListItem2'], ['Key1' => 'Value1', 'Key2' => 'Value2']];

        $bencode = new Encode($dataset);
        $bencoded = $bencode->encode();

        $bdecode = new Decode($bencoded);
        $bdecoded = $bdecode->decode();

//        var_dump($bdecoded);
    }
}
