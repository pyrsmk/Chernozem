MiniSuite 1.0.8
===============

MiniSuite is an very small and flexible unit testing tool.
Nothing to learn about.
Beautiful reports.
No headaches.

Installing
----------

You can download the class files (located in `src/`) or install MiniSuite with [Composer](https://getcomposer.org/) :

```json
{
    "require": {
        "pyrsmk/minisuite": "1.*"
    }
}
```

```shell
composer install
```

Contexts
--------

MiniSuite currently supports CLI and HTTP contexts.

```php
// Will print reports for CLI
$minisuite=new MiniSuite\Cli('My Test Suite');
```

```php
// Will print reports for browsers
$minisuite=new MiniSuite\Http('My Test Suite');
```

Run your tests
--------------

The `test()` method can accept any `callable`. If an exception occurs during the test, MiniSuite will quietly handle it and simply fails the test. Here's how to make a test :

```php
$minisuite->test('I have 3 fruits in my basket',function(){
    $fruits=array('apple','peach','strawberry');
    return count($fruits)==3;
});
```

To run all your tests, add this line :

```php
$minisuite->run();
```

And launch your PHP test file in the command line interface or your browser.

License
-------

MiniSuite is released under the MIT license.
