# Form model

[![Build Status](https://travis-ci.org/web-complete/form.svg?branch=master)](https://travis-ci.org/web-complete/form)
[![Coverage Status](https://coveralls.io/repos/github/web-complete/form/badge.svg?branch=master)](https://coveralls.io/github/web-complete/form?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/web-complete/form/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/web-complete/form/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/web-complete/form/version)](https://packagist.org/packages/web-complete/form)
[![License](https://poser.pugx.org/web-complete/form/license)](https://packagist.org/packages/web-complete/form)

Flexible filtration and validation server side Form Model like in Yii2.

The library has no dependencies and can be easily used with any frameworks or code.

## Installation

```
composer require web-complete/form
```

## Usage

To use the Form, you need to create a class which extend AbstractForm and implement abstract methods: rules () and filters (). In the primitive case, they can return empty arrays.

**Filters** (filtration rules) is an array of the following format:
```php
[
    [field, filter, params]
]
```
where:

**field** - field name or array of fields<br>
**filter** - filter name. It can be a string or callable function. The Form will check method availability in the own object and if not exists then in filter objects array (filtersObject) in case of string. The filter will be called with arguments: $value, $params (parameters from the filtration rules), $form (current from object) and will return the filtered value.<br>
**params** - array of parameters will be passed to the filter. The requirement depends on the called filter.

**Rules** (validation rules) is an array of the following format:
```php
[
    [field, validator, params, message]
]
```
where:

**field** - field name or array of fields<br>
**validator** - validator name. Может быть строкой, либо callable. It can be a string or callable function. The Form will check method availability in the own object and if not exists then in validator objects array (validatorsObject) in case of string. The validator will be called with arguments: $value and $params (parameters from the filtration rules) and will return boolean.<br>
**params** - array of parameters will be passed to the filter. The requirement depends on the called filter.<br>
**message** - Message in case of error. Returns "error" by default or value from overridden the $defaultError property.

If the data field does not have any filtering or validation rules, it will be deleted. However, if the field is necessary, but does not require filtering and validation, you can specify its security by adding it to the rules rules without additional arguments:
```php
[
    ['name', 'string', ['min' => 3]],
    ['age'], // this field is considered as safe
]
```

The form has a built-in validator **required**, which checks that this field is not empty. The other validators will only be applied to non-empty fields.

The class constructor takes the following arguments:

```php
    public function __construct(
        $rules = null,
        $filters = null,
        $validatorsObject = null,
        $filtersObject = null
    )
```

**rules** - will be merged with **rules()** (optional)<br>
**filters** - will be merged with **filters()** (optional)<br>
**validatorsObject** - object with validation methods (optional)<br>
**filtersObject** - object with filtration methods (optional)<br>

The form API provides the following methods:

**validate()** : - validate data<br>
**setData($data)** - filter and set form data<br>
**getData()** - get form data<br>
**setValue($field, $value, $filter = true)** - filter (by default) and set form field value<br>
**getValue($field)** - get form field value<br>
**addError($field, $error)** - add an error for field<br>
**hasErrors($field = null)** - check for errors in the form or field <br>
**getErrors($field = null)** - get form or field errors <br>
**getFirstErrors()** - get the first errors of all form fields <br>
**resetErrors()** - reset form errors <br>

This library has classes **Validators** and **Filters** which contains the most commonly used filters and validators and
**FastForm** class form simple forms.
 
## Filters
    Filters supplied with the library (can be used independently):

**trim** - trim spaces (arguments: charlist, left, right)<br>
**escape**  - htmlspecialchars<br>
**capitalize** - transform string to lowercase and capitalize first char<br>
**lowercase** - transform string to lowercase <br>
**uppercase** - transform string to uppercase <br>
**replace** - replace substring (optional arguments: pattern (string or regular expression), to)<br>
**stripTags** - strip html-tags <br>
**stripJs** - strip js <br>

For more information, see the **Filters** class annotations  

## Validators
    Validators supplied with the library (can be used independently):

**equals** - comparison with other value (arguments: value, not (check inequality in case of **true**))<br>
**compare** - comparison with other value (arguments: field (The third argument is the **Form** object), not (check inequality in case of **true**))<br>
**email** - e-mail <br>
**number** - validate if numeric (optional arguments: min, max) <br>
**string** - validate if string (optional arguments: min, max - string length) <br>
**regex** - regular expression (arguments: pattern) <br>

For more information, see the **Validators** class annotations  

## Examples

Filtering and validation rules:

```php
class MyForm1 extends \WebComplete\form\AbstractForm
{
    
    protected function filters()
    {
        return [
            [['first_name', 'last_name'], 'capitalize'],
            ['description', 'stripTags'],
            ['content', 'stripJs'],
            ['email', 'replace', ['pattern' => 'email.com', 'to' => 'gmail.com']],
            ['*', 'trim'],
        ];
    }

    protected function rules()
    {
        return [
            [['description', 'label'], ], // safe fields (no validation)
            [['name', 'email'], 'required', [], 'Field is required'],
            ['name', 'string', ['min' => 2, 'max' => 50], 'Incorrect name'],
            ['email', 'email', [], 'Incorrect email'],
            ['price', 'validatePrice'],
            ['password', 'required'],
            ['password_repeat', 'compare', ['field' => 'password'], 'Repeat password error'],
            ['some', [SomeValidator::class, 'method'], ['customParam' => 100], 'Incorrect'],
            [['*'], 'regex', ['pattern' => '/^[a-z]$/'], 'Field is required'],
        ];
    }
    
    protected function validatePrice($value, $params, AbstractForm $form)
    {
        ...
        return true;
    }
    
}
```

Form usage:

```php
$form = new MyForm([], [], new Validators(), new Filters());
$form->setData($_POST);
if($form->validate()) {
    $filteredData = $form->getData();
    ...
}
```

Fast Form usage:

```php
$form = new FastForm([['name', 'required'], ['email', 'email']]);
$form->setData($_POST);
if($form->validate()) {
    $filteredData = $form->getData();
    ...
}
else {
    $form->getErrors();
//    $form->getErrors('name');
//    $form->getFirstErrors();
//    $form->hasErrors();
    ...
}
```

Custom abstract form creation with default rules:
 
```php
abstract class MyAbstractForm extends \WebComplete\form\AbstractForm
{

    protected function filters()
    {
        return [
            ['*', 'trim'] 
        ];
    }

    public function __construct($rules = [], $filters = [])
    {
        parent::__construct($rules, $filters, new Validators(), new Filters();
    }
    
}
```