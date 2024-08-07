# itertools
«Честная» (основанная на генераторах PHP 5.5) реализация пакета [itertools Пайтона](https://docs.python.org/2/library/itertools.html).

Реализованы все функции, для запуска требуется PHP 8.1 и выше.

Пример использования:

```php
require_once 'src/Itertools/Itertools.php';
use Itertools\Itertools as it;

foreach (it::islice(it::cycle('ABC'), 10) as $element) {
    echo $element;
}
```
По сравнению с пакетом itertools, реализованы стандартные для Пайтона функции `enumerate` и `xrange`.
