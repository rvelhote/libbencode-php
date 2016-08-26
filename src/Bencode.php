<?php

namespace Welhott\Bencode;


class Bencode
{
    /**
     * @var string
     */
    private $bencoded = '';

    private $position = 0;

    public function __construct(string $bencoded)
    {
        $this->bencoded = $bencoded;
    }

    private function recursive()
    {
        switch ($this->bencoded[$this->position]) {
            case 'd': {
                $this->move();
                return $this->readDictionary();
            }

            case 'l':
                $this->move();
                return $this->readList();

            case 'i': {
                $this->move();
                return $this->readInteger();

            }

            default: {
                return $this->readByteString();
            }

        }

    }

    public function decode() : array
    {
        $data = [];

        while($this->position < mb_strlen($this->bencoded)) {
            $data[] = $this->recursive();
        }

        return $data;
    }

    public function move(int $length = 1)
    {
        $this->position += $length;
    }

    private function readDictionary() : array
    {
        $data = [];
        $zebra = 0;
        $key = '';

        while($this->bencoded[$this->position] != 'e') {
            if($zebra % 2 == 0) {
                $key = $this->recursive();
            } else {
                $data[$key] = $this->recursive();
            }
            $zebra++;
        }

        $this->move();
        return $data;
    }

    /**
     * @return int
     */
    private function readInteger() : int
    {
        $token = mb_strpos($this->bencoded, "e", $this->position);
        $string = mb_substr($this->bencoded, $this->position, $token - $this->position);

        $this->move($token - $this->position + 1);
        return intval($string);
    }

    /**
     * @return string
     */
    private function readByteString() : string
    {
        $token = mb_strpos($this->bencoded, ":", $this->position);
        $length = mb_substr($this->bencoded, $this->position, $token - $this->position);

        $token++;
        $string = mb_substr($this->bencoded, $token, $length);

        $this->move($length - $this->position + $token);
        return $string;
    }

    /**
     * @return array
     */
    private function readList() : array
    {
        $list = [];

        while($this->bencoded[$this->position] != 'e') {
            $list[] = $this->recursive();
        }

        $this->move();
        return $list;
    }
}
