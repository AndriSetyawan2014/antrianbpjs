<?php


/**
 * Created by PhpStorm.
 * User: sics
 * Date: 27.02.2016
 * Time: 15:54
 */

namespace LZCompressor;


class LZUtil
{
    /**
     * @var string
     */
    public static $keyStrBase64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    public static $keyStrUriSafe = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+-$";
    private static $baseReverseDic = array();

    /**
     * @param string $alphabet
     * @param integer $character
     * @return string
     */
    public static function getBaseValue($alphabet, $character)
    {
        if(!array_key_exists($alphabet, self::$baseReverseDic)) {
            self::$baseReverseDic[$alphabet] = array();
            for($i=0; $i<strlen($alphabet); $i++) {
                self::$baseReverseDic[$alphabet][$alphabet[$i]] = $i;
            }
        }
        return self::$baseReverseDic[$alphabet][$character];
    }

    /**
     * @return string
     */
    public static function fromCharCode()
    {
        return array_reduce(func_get_args(), function ($a, $b) {
            $a .= self::utf8_chr($b);
            return $a;
        });
    }

    /**
     * Phps chr() equivalent for UTF-8 encoding
     *
     * @param int|string $u
     * @return string
     */
    public static function utf8_chr($u)
    {
        return mb_convert_encoding('&#' . intval($u) . ';', 'UTF-8', 'HTML-ENTITIES');
    }

    /**
     * @param string $str
     * @param int $num
     *
     * @return bool|integer
     */
    public static function charCodeAt($str, $num=0)
    {
        return self::utf8_ord(self::utf8_charAt($str, $num));
    }

    /**
     * @param string $ch
     *
     * @return bool|integer
     */
    public static function utf8_ord($ch)
    {
        // must remain php's strlen
        $len = strlen($ch);
        if ($len <= 0) {
            return -1;
        }
        $h = ord($ch[0]);
        if ($h <= 0x7F) return $h;
        if ($h < 0xC2) return -3;
        if ($h <= 0xDF && $len > 1) return ($h & 0x1F) << 6 | (ord($ch[1]) & 0x3F);
        if ($h <= 0xEF && $len > 2) return ($h & 0x0F) << 12 | (ord($ch[1]) & 0x3F) << 6 | (ord($ch[2]) & 0x3F);
        if ($h <= 0xF4 && $len > 3)
            return ($h & 0x0F) << 18 | (ord($ch[1]) & 0x3F) << 12 | (ord($ch[2]) & 0x3F) << 6 | (ord($ch[3]) & 0x3F);
        return -2;
    }

    /**
     * @param string $str
     * @param integer $num
     *
     * @return string
     */
    public static function utf8_charAt($str, $num)
    {
        return mb_substr($str, $num, 1, 'UTF-8');
    }

    /**
     * @param string $str
     * @return integer
     */
    public static function utf8_strlen($str) {
        return mb_strlen($str, 'UTF-8');
    }
}


/**
 * Created by PhpStorm.
 * User: sics
 * Date: 27.02.2016
 * Time: 15:54
 */

namespace LZCompressor;


class LZUtil16
{

    /**
     * @return string
     */
    public static function fromCharCode()
    {
        return array_reduce(func_get_args(), function ($a, $b) {
            $a .= self::utf16_chr($b);
            return $a;
        });
    }

    /**
     * Phps chr() equivalent for UTF-16 encoding
     *
     * @param int|string $u
     * @return string
     */
    public static function utf16_chr($u)
    {
        return mb_convert_encoding('&#' . intval($u) . ';', 'UTF-16', 'HTML-ENTITIES');
    }

    /**
     * @param string $str
     * @param int $num
     *
     * @return bool|integer
     */
    public static function charCodeAt($data)
    {
        $sub = substr($data->str, $data->index, 2);
        $sub = self::utf16_charAt($sub, 0);
        $data->index += strlen($sub);
        $data->end = strlen($sub) <= 0;
        return self::utf16_ord($sub);
    }

    /**
     * @source http://blog.sarabande.jp/post/35970262740
     * @param string $ch
     * @return bool|integer
     */
    public static function utf16_ord($ch) {
        $length = strlen($ch);
        if (2 === $length) {
            return hexdec(bin2hex($ch));
        } else if (4 === $length) {
            $w1 = $ch[0].$ch[1];
            $w2 = $ch[2].$ch[3];
            if ($w1 < "\xD8\x00" || "\xDF\xFF" < $w1 || $w2 < "\xDC\x00" || "\xDF\xFF" < $w2) {
                return false;
            }
            $w1 = (hexdec(bin2hex($w1)) & 0x3ff) << 10;
            $w2 =  hexdec(bin2hex($w2)) & 0x3ff;
            return $w1 + $w2 + 0x10000;
        }
        return false;
    }

    /**
     * @param string $str
     * @param integer $num
     *
     * @return string
     */
    public static function utf16_charAt($str, $num)
    {
        return mb_substr($str, $num, 1, 'UTF-16');
    }

    /**
     * @param string $str
     * @return integer
     */
    public static function utf16_strlen($str)
    {
        return mb_strlen($str, 'UTF-16');
    }

}


namespace LZCompressor;

class LZContext
{
    /**
     * @var array
     */
    public $dictionary = array();

    /**
     * @var array
     */
    public $dictionaryToCreate = array();

    /**
     * @var string
     */
    public $c = '';

    /**
     * @var string
     */
    public $wc = '';

    /**
     * @var string
     */
    public $w = '';

    /**
     * @var int
     */
    public $enlargeIn = 2;

    /**
     * @var int
     */
    public $dictSize = 3;

    /**
     * @var int
     */
    public $numBits = 2;

    /**
     * @var LZData
     */
    public $data;

    function __construct()
    {
        $this->data = new LZData;
    }

    // Helper

    /**
     * @param string $val
     * @return bool
     */
    public function dictionaryContains($val) {
        return array_key_exists($val, $this->dictionary);
    }

    /**
     * @param $val
     */
    public function addToDictionary($val) {
        $this->dictionary[$val] = $this->dictSize++;
    }

    /**
     * @param string $val
     * @return bool
     */
    public function dictionaryToCreateContains($val) {
        return array_key_exists($val, $this->dictionaryToCreate);
    }

    /**
     * decrements enlargeIn and extends numbits in case enlargeIn drops to 0
     */
    public function enlargeIn() {
        $this->enlargeIn--;
        if($this->enlargeIn==0) {
            $this->enlargeIn = pow(2, $this->numBits);
            $this->numBits++;
        }
    }
}



namespace LZCompressor;

class LZData
{
    /**
     * @var
     */
    public $str = '';

    /**
     * @var
     */
    public $val;

    /**
     * @var int
     */
    public $position = 0;

    /**
     * @var int - index of letters (may be multiple of characters)
     */
    public $index = 1;
    
    /*
     * @var bool - set to true if theindex is out of str range
     */
    public $end = true;
    
    /**
     * @param unknown $str
     */
    public function append($str) {
        $this->str .= $str;
    }
}



/**
 * Created by PhpStorm.
 * User: sics
 * Date: 28.02.2016
 * Time: 12:53
 */

namespace LZCompressor;


class LZReverseDictionary
{

    public $entries = array(0, 1 ,2);

    public function size() {
        return count($this->entries);
    }

    public function hasEntry($index) {
        return array_key_exists($index, $this->entries);
    }

    public function getEntry($index) {
        return $this->entries[$index];
    }

    public function addEntry($char) {
        $this->entries[] = $char;
    }

}


namespace LZCompressor;

class LZString
{
    /**
     * Compress into a string that is already URI encoded
     *
     * @param string $input
     *
     * @return string
     */
    public static function compressToEncodedURIComponent($input)
    {
        if ($input === null) {
            return "";
        }
        return self::_compress(
            $input,
            6,
            function($a) {
                return LZUtil::$keyStrUriSafe[$a];
            }
        );
    }

    /**
     * Decompress from an output of compressToEncodedURIComponent
     *
     * @param string $input
     *
     * @return null|string
     */
    public static function decompressFromEncodedURIComponent($input)
    {
        if ($input === null) {
            return "";
        }
        if ($input === "") {
            return null;
        }

        $input = str_replace(' ', "+", $input);

        return self::_decompress(
            $input,
            32,
            function($data) {
                $sub = substr($data->str, $data->index, 6);
                $sub = LZUtil::utf8_charAt($sub, 0);
                $data->index += strlen($sub);
                $data->end = strlen($sub) <= 0;
                return LZUtil::getBaseValue( LZUtil::$keyStrUriSafe, $sub );
            });
    }

    public static function compressToBase64($input)
    {
        $res = self::_compress($input, 6, function($a) {
            return LZUtil::$keyStrBase64[$a];
        });
        switch (strlen($res) % 4) { // To produce valid Base64
            default: // When could this happen ?
            case 0 : return $res;
            case 1 : return $res ."===";
            case 2 : return $res ."==";
            case 3 : return $res ."=";
        }
    }

    public static function decompressFromBase64($input)
    {
        return self::_decompress($input, 32, function($data) {
            $sub = substr($data->str, $data->index, 6);
            $sub = LZUtil::utf8_charAt($sub, 0);
            $data->index += strlen($sub);
            $data->end = strlen($sub) <= 0;
            return LZUtil::getBaseValue(LZUtil::$keyStrBase64, $sub);
        });
    }

    public static function compressToUTF16($input) {
        return self::_compress($input, 15, function($a) {
                return LZUtil16::fromCharCode($a+32);
            }) . LZUtil16::utf16_chr(32);
    }

    public static function decompressFromUTF16($input) {
        return self::_decompress($input, 16384, function($data) {
            return LZUtil16::charCodeAt($data)-32;
        });
    }

    /**
     * @param string $uncompressed
     * @return string
     */
    public static function compress($uncompressed)
    {
        return self::_compress($uncompressed, 16, function($a) {
            return LZUtil::fromCharCode($a);
        });
    }

    /**
     * @param string $compressed
     * @return string
     */
    public static function decompress($compressed)
    {
        return self::_decompress($compressed, 32768, function($data) {
            $sub = substr($data->str, $data->index, 16);
            $sub = LZUtil::utf8_charAt($sub, 0);
            $data->index += strlen($sub);
            $data->end = strlen($sub) <= 0;
            return LZUtil::charCodeAt($sub, 0);
        });
    }

    /**
     * @param string $uncompressed
     * @param integer $bitsPerChar
     * @param callable $getCharFromInt
     * @return string
     */
    private static function _compress($uncompressed, $bitsPerChar, $getCharFromInt) {

        if(!is_string($uncompressed) || strlen($uncompressed) === 0) {
            return '';
        }

        $context = new LZContext();
        $length = 0;
        $ii = 0;
        do {
            // take the context symbol in UTF-8
            $sub = substr( $uncompressed, $ii, 6); // cover the full utf-8 character space
            $context->c = mb_substr( $sub, 0, 1, 'UTF-8'); // fast take the character
            $length = strlen( $context->c ); // get amount of bytes taken
            $ii += $length; // advance the index
            // handle the compression
            if(!$context->dictionaryContains($context->c)) {
                $context->addToDictionary($context->c);
                $context->dictionaryToCreate[$context->c] = true;
            }
            $context->wc = $context->w . $context->c;
            if($context->dictionaryContains($context->wc)) {
                $context->w = $context->wc;
            } else {
                self::produceW($context, $bitsPerChar, $getCharFromInt);
            }
        } while( $length > 0 );

        if($context->w !== '') {
            self::produceW($context, $bitsPerChar, $getCharFromInt);
        }

        $value = 2;
        for($i=0; $i<$context->numBits; $i++) {
            self::writeBit($value&1, $context->data, $bitsPerChar, $getCharFromInt);
            $value = $value >> 1;
        }

        while (true) {
            $context->data->val = $context->data->val << 1;
            if ($context->data->position == ($bitsPerChar-1)) {
                $context->data->append($getCharFromInt($context->data->val));
                break;
            }
            $context->data->position++;
        }

        return $context->data->str;
    }

    /**
     * @param LZContext $context
     * @param integer $bitsPerChar
     * @param callable $getCharFromInt
     *
     * @return LZContext
     */
    private static function produceW(LZContext $context, $bitsPerChar, $getCharFromInt)
    {
        if($context->dictionaryToCreateContains($context->w)) {
            if(LZUtil::charCodeAt($context->w)<256) {
                for ($i=0; $i<$context->numBits; $i++) {
                    self::writeBit(null, $context->data, $bitsPerChar, $getCharFromInt);
                }
                $value = LZUtil::charCodeAt($context->w);
                for ($i=0; $i<8; $i++) {
                    self::writeBit($value&1, $context->data, $bitsPerChar, $getCharFromInt);
                    $value = $value >> 1;
                }
            } else {
                $value = 1;
                for ($i=0; $i<$context->numBits; $i++) {
                    self::writeBit($value, $context->data, $bitsPerChar, $getCharFromInt);
                    $value = 0;
                }
                $value = LZUtil::charCodeAt($context->w);
                for ($i=0; $i<16; $i++) {
                    self::writeBit($value&1, $context->data, $bitsPerChar, $getCharFromInt);
                    $value = $value >> 1;
                }
            }
            $context->enlargeIn();
            unset($context->dictionaryToCreate[$context->w]);
        } else {
            $value = $context->dictionary[$context->w];
            for ($i=0; $i<$context->numBits; $i++) {
                self::writeBit($value&1, $context->data, $bitsPerChar, $getCharFromInt);
                $value = $value >> 1;
            }
        }
        $context->enlargeIn();
        $context->addToDictionary($context->wc);
        $context->w = $context->c.'';
    }

    /**
     * @param string $value
     * @param LZData $data
     * @param integer $bitsPerChar
     * @param callable $getCharFromInt
     */
    private static function writeBit($value, LZData $data, $bitsPerChar, $getCharFromInt)
    {
        if(null !== $value) {
            $data->val = ($data->val << 1) | $value;
        } else {
            $data->val = ($data->val << 1);
        }
        if ($data->position == ($bitsPerChar-1)) {
            $data->position = 0;
            $data->append($getCharFromInt($data->val));
            $data->val = 0;
        } else {
            $data->position++;
        }
    }

    /**
     * @param LZData $data
     * @param integer $resetValue
     * @param callable $getNextValue
     * @param integer $exponent
     * @param string $feed
     * @return integer
     */
    private static function readBits(LZData $data, $resetValue, $getNextValue, $exponent)
    {
        $bits = 0;
        $maxPower = pow(2, $exponent);
        $power=1;
        while($power != $maxPower) {
            $resb = $data->val & $data->position;
            $data->position >>= 1;
            if ($data->position == 0) {
                $data->position = $resetValue;
                $data->val = $getNextValue($data);
            }
            $bits |= (($resb>0 ? 1 : 0) * $power);
            $power <<= 1;
        }
        return $bits;
    }

    /**
     * @param string $compressed
     * @param integer $resetValue
     * @param callable $getNextValue
     * @return string
     */
    private static function _decompress($compressed, $resetValue, $getNextValue)
    {
        if(!is_string($compressed) || strlen($compressed) === 0) {
            return '';
        }

        $entry = null;
        $enlargeIn = 4;
        $numBits = 3;
        $result = '';

        $dictionary = new LZReverseDictionary();

        $data = new LZData();
        $data->str = $compressed;
        $data->index = 0;
        $data->end = false;
        $data->val = $getNextValue($data);
        $data->position = $resetValue;

        $next = self::readBits($data, $resetValue, $getNextValue, 2);

        if($next < 0 || $next > 1) {
            return '';
        }

        $exponent = ($next == 0) ? 8 : 16;
        $bits = self::readBits($data, $resetValue, $getNextValue, $exponent);

        $c = LZUtil::fromCharCode($bits);
        $dictionary->addEntry($c);
        $w = $c;

        $result .= $c;

        while(true) {
            if($data->end) {
                return '';
            }
            $bits = self::readBits($data, $resetValue, $getNextValue, $numBits);

            $c = $bits;

            switch($c) {
                case 0:
                    $bits = self::readBits($data, $resetValue, $getNextValue, 8);
                    $c = $dictionary->size();
                    $dictionary->addEntry(LZUtil::fromCharCode($bits));
                    $enlargeIn--;
                    break;
                case 1:
                    $bits = self::readBits($data, $resetValue, $getNextValue, 16);
                    $c = $dictionary->size();
                    $dictionary->addEntry(LZUtil::fromCharCode($bits));
                    $enlargeIn--;
                    break;
                case 2:
                    return $result;
                    break;
            }

            if($enlargeIn == 0) {
                $enlargeIn = pow(2, $numBits);
                $numBits++;
            }

            if($dictionary->hasEntry($c)) {
                $entry = $dictionary->getEntry($c);
            }
            else {
                if ($c == $dictionary->size()) {
                    $entry = $w . $w[0];
                } else {
                    return null;
                }
            }

            $result .= $entry;
            $dictionary->addEntry($w . LZUtil::utf8_charAt($entry, 0));
            $w = $entry;

            $enlargeIn--;
            if($enlargeIn == 0) {
                $enlargeIn = pow(2, $numBits);
                $numBits++;
            }
        }
    }
}


