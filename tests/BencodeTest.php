<?php

namespace Welhott\Bencode\Tests;

use PHPUnit_Framework_TestCase;
use Welhott\Bencode\Bencode;

class BencodeTest extends PHPUnit_Framework_TestCase
{
    public function testByteString()
    {
        $strings = [
            'these pretzels are making me thirsty',
            'and you wanna be my latex salesman?',
            'i am speechless; i am without speech',
            'another babka?'
        ];

        foreach($strings as $string) {
            $bencoded = new Bencode(mb_strlen($string).':'.$string);
            $this->assertEquals([$string], $bencoded->decode());
        }
    }

    public function testInteger()
    {
        for($i = -1; $i < 1; $i++) {
            $bencoded = new Bencode('i'.$i.'e');
            $this->assertEquals([$i], $bencoded->decode());
        }
    }

    public function testMultipleIntegers()
    {
        $bencoded = new Bencode('i-1ei1e');
        $this->assertEquals([-1, 1], $bencoded->decode());

        $time1 = time();
        $time2 = time() * time();
        $bencoded = new Bencode('i'.$time1.'ei'.$time2.'e');

        $this->assertEquals([$time1, $time2], $bencoded->decode());
    }

    public function testList()
    {
        $bencoded = new Bencode('li-100ei1e8:pretzelse');
        $this->assertEquals([[-100, 1, 'pretzels']], $bencoded->decode());
    }

    public function testDictionary()
    {
        $bencoded = new Bencode('d8:pretzelsi-100ei1ee');
        $this->assertEquals([['pretzels' => -100]], $bencoded->decode());
    }
}
