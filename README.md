# Form model

[![Build Status](https://travis-ci.org/web-complete/form.svg?branch=master)](https://travis-ci.org/web-complete/form)
[![Coverage Status](https://coveralls.io/repos/github/web-complete/form/badge.svg?branch=master)](https://coveralls.io/github/web-complete/form?branch=master)

Простая, но очень гибкая модель для фильтрации и валидации данных на стороне сервера, созданная по мотивам Yii Forms.

Библиотека не имеет зависимостей и может быть легко использована в любом фреймворке.

## Usage

Для использования формы необходимо создать класс, унаследованный от AbstractForm и реализовать два абстрактных метода: rules() и filters(). В примитивном случае, они могут возвращать пустые массивы.

**Filters** (правила фильтрации) представляет из себя массив следующего формата:
```
[
    [field, filter, params]
]
```
где:

**field** - название поля (ключ массива данных) либо массив полей<br>
**filter** - название фильтра. Может быть строкой, либо callable. В случае, если передана строка, то форма проверит наличие одноименного метода в порядке приоритета: в собственном объекте, в объекте фильтров (filtersObject). Фильтр будет вызван с параметрами: $value (значение), $params (параметры из правила), $form (текущий объект формы) и должен вернуть отфильтрованное значение.<br>
**params** - массив параметров, будет передан в фильтр. Обязательность зависит от вызываемого фильтра. 

**Rules** (правила валидации) представляет из себя массив следующего формата:
```
[
    [field, validator, params, message]
]
```
где:

**field** - название поля (ключ массива данных) либо массив полей<br>
**validator** - название валидатора. Может быть строкой, либо callable. В случае, если передана строка, то форма проверит наличие одноименного метода в порядке приоритета: в собственном объекте, в объекте валидаций (validatorsObject). Валидатор будет вызван с параметрами: $value (значение) и $params (параметры из правила) и должен вернуть булево значение.<br>
**params** - массив параметров, будет передан в фильтр. Обязательность зависит от вызываемого фильтра.<br> 
**message** - сообщение в случае ошибки. По умолчанию "error". Также может быть переопределен в свойстве $defaultError.

Если поле данных не имеет ни одного правила фильтрации или валидации, то оно будет отфильтровано. Если поле необходимо, но не требует фильтрации и валидации, то можно указать его безопасность, добавив в правила rules без дополнительных параметров:
```
[
    ['name', 'string', ['min' => 3]],
    ['age'], // данное поле считается безопасным
]
```
 
Форма имеет один встроенный валидатор **required**, который проверяет, что данное поле не пустое. Остальные валидаторы будут применены только к непустым полям.

Конструктор класса может принимать следующие параметры:
```
    public function __construct(
        $rules = null,
        $filters = null,
        $validatorsObject = null,
        $filtersObject = null
    )
```

**rules** - массив правил, будет добавлен к правилам **rules()** (необязательный)<br>
**filters** - массив правил, будет добавлен к правилам **filters()** (необязательный)<br>
**validatorsObject** - объект, содержащий методы валидации (необязательный)<br>
**filtersObject** - объект, содержащий методы фильтрации (необязательный)<br>

API формы предоставляет следующие методы:

**validate()** : - проверить данные на валидность<br>
**setData($data)** - отфильтровать и заполнить данные формы <br>
**getData()** - получить данные формы <br>
**setValue($field, $value, $filter = true)** - отфильтровать (по умолчанию) и заполнить поле формы <br>
**getValue($field)** - получить значение поля<br>
**addError($field, $error)** - добавить ошибку поля<br>
**hasErrors($field = null)** - проверить наличие ошибок у формы или поля <br>
**getErrors($field = null)** - получить ошибки формы или поля <br>
**getFirstErrors()** - получить первые ошибки всех полей формы <br>
**resetErrors()** - сбросить ошибки формы <br>

В комплекте библиотеки в качестве **validatorsObject** и **filtersObject** поставляются два класса: Validators, Filters, содержащие наиболее часто используемые наборы фильтров и валидаторов.<br>
Также в библиотеке поставляется класс **FastForm**, унаследованный от AbstractForm с пустыми rules и filters. Может быть использован для простых форм с передачей rules и filters через параметры конструктора.
 

## Filters
Фильтры, поставляемые в комплекте с библиотекой (могут быть использованы самостоятельно):

**trim** - обрезание пробелов по краям (Возможные параметры: charlist, left, right)<br>
**escape**  - htmlspecialchars<br>
**capitalize** - перевод значение в нижний регистр + заглавная буква<br>
**lowercase** - перевод значение в нижний регистр <br>
**uppercase** - перевод значение в верхний регистр <br>
**replace** - замена подстроки (Возможные параметры: pattern (строка или регулярное выражение), to)<br>
**stripTags** - вырезание html-тегов <br>
**stripJs** - вырезание js <br>

Для более подробной информации см. аннотации в классе Filters

## Validators
Валидаторы, поставляемые в комплекте с библиотекой (могут быть использованы самостоятельно):

**equals** - сравнение с другим значением (Параметры: value, not (проверяет неравенство, если true))<br>
**compare** - сравнение с другим полем формы (Параметры: field (третьим аргументом ожидает объект формы), not (проверяет неравенство, если true))<br>
**email** - e-mail <br>
**number** - числовое значение (Возможные параметры: min, max) <br>
**string** - строка (Возможные параметры: min, max - длина строки) <br>
**regex** - регулярное выражение (Параметры: pattern) <br>

Для более подробной информации см. аннотации в классе Validators

## Examples

Задание правил фильтрации и валидации:

```
class MyForm1 extends \WebComplete\form\AbstractForm
{

    protected function rules()
    {
        return [
            [['description', 'label'], ], // safe fields (no validation)
            [['name', 'email'], 'required', [], 'Field is required'],
            ['name', 'string', ['min' => 2, 'max' => 50], 'Incorrect name'],
            ['email', 'email', [], 'Incorrect email'],
            ['price', 'methodValidator', [], 'Incorrect'],
            ['some', [SomeValidator::class, 'method'], ['customParam' => 100], 'Incorrect'],
            [['*'], 'regex', ['pattern' => '/^[a-z]$/'], 'Field is required'],
        ];
    }
    
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
    
}
```

Использование формы:
```
$form->setData($_POST);
if($form->validate()) {
    $filteredData = $form->getData();
    ...
}
```

Использование быстрой формы:
```
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

Создание своей абстрактной формы с правилами по умолчанию: 
```
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