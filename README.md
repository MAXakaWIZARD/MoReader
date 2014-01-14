# MoReader
Gettext *.mo files reader for PHP.

This package is compliant with [PSR-0](http://www.php-fig.org/psr/0/), [PSR-1](http://www.php-fig.org/psr/1/), and [PSR-2](http://www.php-fig.org/psr/2/).
If you notice compliance oversights, please send a patch via pull request.

## Usage
```php
    $parser = new MoReader\Reader();
    $data = $reader->load('my-file.mo');
```

## License
This library is released under [MIT](http://www.tldrlegal.com/license/mit-license) license.