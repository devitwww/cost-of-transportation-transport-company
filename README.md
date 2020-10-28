# Стоимость перевозки 8 транспортных компаний
### Главдоставка, Кит, ПЭК, ЦАП, ЖелДорЭкспедиция, Деловые линии, Vozovoz, dpd.


## Установка
```
composer require tpwwswww/cost-of-transportation-transport-company

```

## Пример

```php
// Перевозка terminal - terminal

<?php

require_once "vendor/autoload.php";

$api = new Tpwwswww\CostOfTransportationTransportCompany\ApiTransportCompany();

$from = 'Краснодар'; // Откуда
$to = 'Москва'; // Куда
$weight = 20; // Вес
$places = 2; // Количество мест
$length = 0.2; // Длина одного места
$width = 0.2; //Ширина одного места
$height = 0.2; //Высота одного места

// Главдоставка
echo $api->glavDostavka($from, $to, $weight, $places, $length, $weight, $height);

// Кит
echo $api->kit($from, $to, $weight, $places, $length, $weight, $height);

// ПЭК
echo $api->pecom('key', 'login', $from, $to, $weight, $places, $length, $weight, $height);

// ЦАП
echo $api->avtotransit($from, $to, $weight, $places, $length, $width, $height);

// ЖелДорЭкспедиция
echo $api->jde($from, $to, $weight, $places, $length, $width, $height);

// dpd
echo $api->dpd('clientNumber', 'clientKey', $from, $to, $weight, $places, $length, $width, $height);

// Деловые линии
echo $api->dellin('appKey', $from, $to, $weight, $places, $length, $width, $height);

// Vozovoz
echo $api->Vozovoz('token', $from, $to, $weight, $places, $length, $width, $height);
```
