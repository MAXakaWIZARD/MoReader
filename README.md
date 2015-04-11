# MoReader
[![Build Status](https://api.travis-ci.org/MAXakaWIZARD/MoReader.png?branch=master)](https://travis-ci.org/MAXakaWIZARD/MoReader) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/MAXakaWIZARD/MoReader/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/MAXakaWIZARD/MoReader/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/maxakawizard/mo-reader/v/stable.svg)](https://packagist.org/packages/maxakawizard/mo-reader) 
[![Total Downloads](https://poser.pugx.org/maxakawizard/mo-reader/downloads.svg)](https://packagist.org/packages/maxakawizard/mo-reader) 
[![Latest Unstable Version](https://poser.pugx.org/maxakawizard/mo-reader/v/unstable.svg)](https://packagist.org/packages/maxakawizard/mo-reader) 
[![License](https://poser.pugx.org/maxakawizard/mo-reader/license.svg)](https://packagist.org/packages/maxakawizard/mo-reader)

Gettext *.mo files reader for PHP.

This package is compliant with [PSR-0](http://www.php-fig.org/psr/0/), [PSR-1](http://www.php-fig.org/psr/1/), and [PSR-2](http://www.php-fig.org/psr/2/).
If you notice compliance oversights, please send a patch via pull request.

## Usage
```php
$parser = new \MoReader\Reader();
$data = $reader->load('my-file.mo'); //data is an array with entries
```

## License
This library is released under [MIT](http://www.tldrlegal.com/license/mit-license) license.