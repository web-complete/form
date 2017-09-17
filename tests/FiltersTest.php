<?php

use WebComplete\form\Filters;

class FiltersTest extends \PHPUnit\Framework\TestCase
{

    public function testTrim()
    {
        $filters = new Filters();
        $this->assertEquals('asd', $filters->trim(' asd '));
        $this->assertEquals(' asd', $filters->trim(' asd ', ['left' => false]));
        $this->assertEquals('asd ', $filters->trim(' asd ', ['right' => false]));
        $this->assertEquals('as', $filters->trim(' asd ', ['charlist' => ' d']));
        $this->assertEquals(' asd ', $filters->trim(' asd ', ['left' => false, 'right' => false]));
    }

    public function testEscape()
    {
        $filters = new Filters();
        $this->assertEquals('&lt;asd\&gt;', $filters->escape('<asd\\>'));
    }

    public function testCapitalize()
    {
        $filters = new Filters();
        $this->assertEquals('Test', $filters->capitalize('tEsT'));
        $this->assertEquals('Проверка', $filters->capitalize('пРоВеРкА'));
    }

    public function testLowercase()
    {
        $filters = new Filters();
        $this->assertEquals('test', $filters->lowercase('tEsT'));
        $this->assertEquals('проверка', $filters->lowercase('пРоВеРкА'));
    }

    public function testUppercase()
    {
        $filters = new Filters();
        $this->assertEquals('TEST', $filters->uppercase('tEsT'));
        $this->assertEquals('ПРОВЕРКА', $filters->uppercase('пРоВеРкА'));
    }

    public function testReplace()
    {
        $filters = new Filters();
        $this->assertEquals('aefg', $filters->replace('abcdefg', ['pattern' => 'bcd']));
        $this->assertEquals('a1efg', $filters->replace('abcdefg', ['pattern' => 'bcd', 'to' => '1']));
        $this->assertEquals('a1efg', $filters->replace('abcdefg', ['pattern' => '/(b.d)/', 'to' => '1']));
    }

    public function testStripTags()
    {
        $filters = new Filters();
        $this->assertEquals('aaaabbb', $filters->stripTags('<a>aaaa</a><br>bbb'));
        $this->assertEquals('aaaa<br/>bbb', $filters->stripTags('<a>aaaa</a><br/>bbb', ['allowableTags' => '<br>']));
    }

    public function testStripJs()
    {
        $filters = new Filters();
        $this->assertEquals('aaaabbb', $filters->stripJs('aaaa<script type="text/javascript">alert(1)</script>bbb'));
        $this->assertEquals('aaaabbb', $filters->stripJs('aaaa<script>alert(1)</script>bbb'));
    }

}