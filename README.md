# Yii2 Gender API

This extension provides the [Gender API](https://gender-api.com) for the [Yii framework 2.0](http://www.yiiframework.com).


### Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist richweber/yii2-gender-api
```

or add

```
"richweber/yii2-gender-api": "^1.0.0"
```

to the require section of your composer.json

### Configure

```
'components' => [
    ...
    'gender' => [
        'class' => 'richweber\gender\components\Gender',
        'serverKey' => '<your private server key>',
    ],
    ...
],

```

### Basic Usage

```php
/** @var \richweber\gender\components\Gender $component */
$component = Yii::$app->gender;

$result = $component->checkName('Roman');
if (!isset($result->errno) && $result->accuracy > 60) {
    $gender = (string) $result->gender;
    var_dump($gender);
}
```

#### As multiple names

```php
$result = $component->checkName(['Roman', 'Богдан']);
if (!isset($result->errno)) {
    var_dump($result->result);
}
```

#### Localization by country

```php
$result = $component->byLocalization('UA')->checkName('Roman');
if (!isset($result->errno) && $result->accuracy > 60) {
    $gender = (string) $result->gender;
    var_dump($gender);
}
```

#### Localization by IP

```php
$result = $component->byIP('54.201.16.177')->checkName('Roman');
if (!isset($result->errno) && $result->accuracy > 60) {
    $gender = (string) $result->gender;
    var_dump($gender);
}
```

#### Localization by language

```php
$result = $component->byLanguage('de-DE')->checkName('Roman');
if (!isset($result->errno) && $result->accuracy > 60) {
    $gender = (string) $result->gender;
    var_dump($gender);
}
```

#### Get gender by an email address

```php
$result = $component->checkNameByEmail('markus.p@gmail.com');
if (!isset($result->errno) && $result->accuracy > 60) {
    $gender = (string) $result->gender;
    var_dump($gender);
}
```
