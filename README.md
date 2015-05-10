# itertools
«Честная» (основанная на генераторах PHP 5.5) реализация пакета [itertools Пайтона](https://docs.python.org/2/library/itertools.html).

Реализованы все функции, для запуска требуется PHP 5.6 и выше.

Пример использования:
```php
require 'itertools.php';
use function itertools\islice, itertools\cycle;

foreach (islice(cycle('ABC'), 10) as $element) {
    echo $element;
}
```
По сравнению с пакетом itertools, реализованы стандартные для Пайтона функции
`slice`, `enumerate`, `iter` и `xrange`.
