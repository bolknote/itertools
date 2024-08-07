# itertools

"Fair" (based on PHP 5.5 generators) implementation of the [Python itertools](https://docs.python.org/3/library/itertools.html) module.

All functions are implemented; PHP 8.1 or higher is required to run.

Usage example:

```php
require_once 'src/Itertools/Itertools.php';
use Itertools\Itertools as it;

foreach (it::islice(it::cycle('ABC'), 10) as $element) {
    echo $element;
}
```
Compared to the itertools package, this implementation includes standard Python functions such as `enumerate` and `xrange`.
