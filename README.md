# Laravel Language Sync
[![Latest version on Packagist](https://img.shields.io/packagist/v/snoeren-development/laravel-language-sync.svg?style=flat-square)](https://packagist.org/packages/snoeren-development/laravel-language-sync)
[![Software License](https://img.shields.io/github/license/snoeren-development/laravel-language-sync?style=flat-square)](LICENSE)
[![Downloads](https://img.shields.io/packagist/dt/snoeren-development/laravel-language-sync?style=flat-square)](https://packagist.org/packages/snoeren-development/laravel-language-sync)

Find missing languages in all other languages using `php artisan language:sync`!

## Installation
You can install the package using Composer:
```bash
composer require snoeren-development/laravel-language-sync
```

### Requirements
This package requires at least PHP 7.2 and Laravel 6.

### Usage
Run `php artisan language:sync {main language}` to get the missing language strings for all other languages. Append languages after the main language to compare specific languages, for example `php artisan language:sync en nl de`. That'll sync `nl` and `de` with `en`.

## Credits
- [Michael Snoeren](https://github.com/MSnoeren)
- [All Contributors](https://github.com/snoeren-development/laravel-language-sync/graphs/contributors)

## License
The MIT license. See [LICENSE](LICENSE) for more information.
