<?php

use WebComplete\form\FastForm;

require_once 'include/MyForm.php';
require_once 'include/Filters.php';
require_once 'include/Validators.php';

class FormTest extends \PHPUnit\Framework\TestCase
{

    public function testInstance()
    {
        $form = new FastForm();
        $this->assertInstanceOf(\WebComplete\form\AbstractForm::class, $form);

        $form = new FastForm(null, null, new Validators(), new Filters());
        $this->assertInstanceOf(\WebComplete\form\AbstractForm::class, $form);
    }

    public function testSetGetDataEmpty()
    {
        $form = new FastForm();
        $form->setData(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals([], $form->getData());
    }

    public function testSetGetDataSafe()
    {
        $form = new FastForm([
            [['a', 'c'],], // safe fields
        ]);
        $form->setData(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals(['a' => 1, 'c' => 3], $form->getData());
    }

    public function testFilterFromObject()
    {
        $form = new FastForm(
            [
                [['c'],], // safe fields
            ],
            [
                [['a', 'b'], 'increase', ['amount' => 2]],
            ],
            null,
            new Filters()
        );
        $form->setData(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals(['a' => 3, 'b' => 4, 'c' => 3], $form->getData());
    }

    public function testFilterFromCallable()
    {
        $object = new Filters();
        $form = new FastForm(
            [
                [['c'],], // safe fields
            ],
            [
                [['a', 'b'], [$object, 'increase'], ['amount' => 2]],
            ]
        );
        $form->setData(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals(['a' => 3, 'b' => 4, 'c' => 3], $form->getData());
    }

    public function testFilterFromMethod()
    {
        $form = new MyForm(
            [
                [['c'],], // safe fields
            ],
            [
                [['a', 'b'], 'filterDecrease', ['amount' => 1]],
            ]
        );
        $form->setData(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals(['a' => 0, 'b' => 1, 'c' => 3], $form->getData());
    }

    public function testValidateFromObject()
    {
        $form = new FastForm(
            [
                [['a', 'b', 'c'], 'number', ['min' => 2, 'max' => 5]],
            ],
            [],
            new Validators()
        );

        $form->setData(['a' => 2, 'b' => 3, 'c' => 5]);
        $this->assertTrue($form->validate());

        $form->setData(['a' => 2, 'b' => 3, 'c' => 6]);
        $this->assertFalse($form->validate());
    }

    public function testValidateFromCallable()
    {
        $object = new Validators();
        $form = new FastForm(
            [
                [['a', 'b', 'c'], [$object, 'number'], ['min' => 2, 'max' => 5]],
            ],
            []
        );

        $form->setData(['a' => 2, 'b' => 3, 'c' => 5]);
        $this->assertTrue($form->validate());

        $form->setData(['a' => 2, 'b' => 3, 'c' => 6]);
        $this->assertFalse($form->validate());
    }

    public function testValidateFromMethod()
    {
        $form = new MyForm(
            [
                [['a', 'b', 'c'], 'validateString', ['minLength' => 3]],
            ]
        );

        $form->setData(['a' => 'aa ', 'b' => 'bbb ', 'c' => 'cccc ']);
        $this->assertTrue($form->validate());

        $form->setData(['a' => 'aa', 'b' => 'bbb', 'c' => 'cccc']);
        $this->assertFalse($form->validate());
    }

    public function testValidateRequired()
    {
        $form = new MyForm(
            [
                ['a', 'validateString', ['minLength' => 3]],
                ['b', 'required'],
                ['b', 'validateString', ['minLength' => 3]],
            ]
        );

        $this->assertFalse($form->validate());
        $form->setData(['b' => 'bbb']);
        $this->assertTrue($form->validate());
        $form->setData(['a' => 'aa', 'b' => 'bbb']);
        $this->assertFalse($form->validate());
        $form->setData(['a' => 'aaa', 'b' => 'bbb']);
        $this->assertTrue($form->validate());
    }

    public function testValidateFilterAsterisk()
    {
        $form = new MyForm(
            [
                [['a', 'b', 'c'], 'validateString', ['minLength' => 3]],
            ],
            [
                ['*', 'trim']
            ],
            null,
            new Filters()
        );

        $form->setData(['a' => 'aa ', 'b' => 'bbb ', 'c' => 'cccc ']);
        $this->assertFalse($form->validate());
        $this->assertEquals(['a' => 'aa', 'b' => 'bbb', 'c' => 'cccc'], $form->getData());
    }

    public function testSetValue()
    {
        $form = new FastForm(
            [['a']],
            [['a', 'trim']],
            null,
            new Filters()
        );
        $form->setData(['a' => 'aa ']);
        $this->assertEquals('aa', $form->getValue('a'));
        $form->setValue('a', 'bb ');
        $this->assertEquals('bb', $form->getValue('a'));
        $form->setValue('a', 'cc ', false);
        $this->assertEquals('cc ', $form->getValue('a'));
    }

    public function testValidatorRepeatPassword()
    {
        $form = new FastForm(
            [
                [['p'],], // safe fields
                [['pr'], 'repeatPassword', ['field' => 'p']], // safe fields
            ],
            [
            ],
            new Validators()
        );
        $form->setData(['p' => 'qwe', 'pr' => 'qw']);
        $this->assertFalse($form->validate());
        $form->setValue('pr', 'qwe');
        $this->assertTrue($form->validate());
    }

    public function testCallableNotFoundFilter()
    {
        $this->expectException(\WebComplete\form\FormException::class);
        $this->expectExceptionMessage('Callable not found: "asd"');

        $form = new FastForm(
            [
            ],
            [
                [['a'], 'asd'],
            ]
        );
        $form->setData(['a' => 'aaa']);
    }

    public function testCallableNotFoundValidator()
    {
        $this->expectException(\WebComplete\form\FormException::class);
        $this->expectExceptionMessage('Callable not found: "qwe"');

        $form = new FastForm(
            [
                [['a'], 'qwe'],
            ],
            [
            ]
        );
        $form->setData(['a' => 'aaa']);
        $form->validate();
    }

    public function testErrors()
    {
        $form = new FastForm(
            [
                ['a', 'number', ['min' => 3, 'max' => 4], 'error2'],
                [['a', 'b', 'c'], 'number', ['min' => 2, 'max' => 5]],
            ],
            [],
            new Validators()
        );

        $form->setData(['a' => 1, 'b' => 3, 'c' => 6]);
        $this->assertFalse($form->hasErrors());
        $this->assertFalse($form->validate());
        $this->assertTrue($form->hasErrors());
        $this->assertEquals(['a' => ['error2', 'error'], 'c' => ['error']], $form->getErrors());
        $this->assertEquals(['a' => 'error2', 'c' => 'error'], $form->getFirstErrors());
        $this->assertEquals('error2', $form->getFirstError('a'));
        $this->assertEquals(['error2', 'error'], $form->getErrors('a'));
        $this->assertTrue($form->hasErrors('c'));
        $this->assertFalse($form->hasErrors('b'));
        $form->resetErrors('c');
        $this->assertTrue($form->hasErrors('a'));
        $this->assertFalse($form->hasErrors('c'));

        $form->setData(['a' => 3, 'b' => 3, 'c' => 5]);
        $this->assertTrue($form->validate());
        $this->assertFalse($form->hasErrors());
    }
}
