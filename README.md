# itertools
«Честная» (основанная на генераторах PHP 5.5) реализация пакета [itertools Пайтона](https://docs.python.org/2/library/itertools.html).

Реализованы все функции, для запуска требуется PHP 5.6 и выше.

Внимание! Из-за ограничений PHP изменён, по сравнению с оригиналом, порядок аргументов функций `product` и `izip_longest`.

Пример использования:

    require 'itertools.php';
    use itertools as it;

    foreach (it\islice(it\cycle(it\iter('ABC')), 10) as $element) {
        echo $element;
    }

В тех местах, где функции требуют на вход итерируемые значения, примитивные типы следует передавать,
обернув функцией `iter`, как в примере.

По сравнению с пакетом itertools, реализованы стандартные для Пайтона функции
`slice`, `enumerate`, `iter` и `xrange`.
