<?php

use WebComplete\form\Validators;

class ValidatorsTest extends \PHPUnit\Framework\TestCase
{

    public function testEquals()
    {
        $validators = new Validators();
        $this->assertTrue($validators->equals('qwe', ['value' => 'qwe']));
        $this->assertFalse($validators->equals('qwe', ['value' => 'qwe1']));
        $this->assertFalse($validators->equals('qwe', ['value' => 'qwe', 'not' => true]));
        $this->assertTrue($validators->equals('qwe', ['value' => 'qwe1', 'not' => true]));
    }

    public function testCompare()
    {
        $form = new \WebComplete\form\FastForm();
        $form->setValue('a', 'aaa', false);
        $validators = new Validators();
        $this->assertEquals('aaa', $form->getValue('a'));
        $this->assertTrue($validators->compare('aaa', ['field' => 'a'], $form));
        $this->assertFalse($validators->compare('aab', ['field' => 'a'], $form));
        $this->assertFalse($validators->compare('aaa', ['field' => 'a', 'not' => true], $form));
        $this->assertTrue($validators->compare('aab', ['field' => 'a', 'not' => true], $form));
    }

    public function testEmail()
    {
        $validators = new Validators();
        $this->assertFalse($validators->email(''));
        $this->assertFalse($validators->email('aaaa'));
        $this->assertFalse($validators->email('aaaa@'));
        $this->assertFalse($validators->email('@gmail.com'));
        $this->assertFalse($validators->email('aaaa@gmail'));
        $this->assertTrue($validators->email('aaaa@gmail.com'));
        $this->assertTrue($validators->email('aaaa.bbb-ccc_fffFFF@gmail-com.com.com'));
    }

    public function testNumber()
    {
        $validators = new Validators();
        $this->assertFalse($validators->number(''));
        $this->assertFalse($validators->number('a'));
        $this->assertTrue($validators->number('0'));
        $this->assertTrue($validators->number(0));
        $this->assertTrue($validators->number('100'));
        $this->assertTrue($validators->number('100.1'));
        $this->assertTrue($validators->number(100));
        $this->assertTrue($validators->number(100.1));
        $this->assertTrue($validators->number(100, ['min' => 90.5, 'max' => 110.5]));
        $this->assertTrue($validators->number(90.5, ['min' => 90.5, 'max' => 110.5]));
        $this->assertTrue($validators->number(110.5, ['min' => 90.5, 'max' => 110.5]));
        $this->assertFalse($validators->number(90.4, ['min' => 90.5, 'max' => 110.5]));
        $this->assertFalse($validators->number(110.6, ['min' => 90.5, 'max' => 110.5]));
    }

    public function testString()
    {
        $validators = new Validators();
        $this->assertTrue($validators->string('aaa'));
        $this->assertTrue($validators->string('aaa', ['min' => 2]));
        $this->assertTrue($validators->string('aaa', ['max' => 3]));
        $this->assertTrue($validators->string('aaa', ['min' => 3, 'max' => 3]));
        $this->assertFalse($validators->string('aa', ['min' => 3, 'max' => 3]));
        $this->assertFalse($validators->string('aaaa', ['min' => 3, 'max' => 3]));
    }

    public function testRegex()
    {
        $validators = new Validators();
        $this->assertTrue($validators->regex('abcdefg'));
        $this->assertTrue($validators->regex('abcdefg', ['pattern' => '/b.d/']));
        $this->assertFalse($validators->regex('abcdefg', ['pattern' => '/^b.d$/']));
    }

}