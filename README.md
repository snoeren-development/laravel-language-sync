# Laravel Language Sync
[![Latest version on Packagist](https://img.shields.io/packagist/v/snoeren-development/laravel-language-sync.svg?style=flat-square)](https://packagist.org/packages/snoeren-development/laravel-language-sync)
[![Software License](https://img.shields.io/github/license/snoeren-development/laravel-language-sync?style=flat-square)](LICENSE)
[![Downloads](https://img.shields.io/packagist/dt/snoeren-development/laravel-language-sync?style=flat-square)](https://packagist.org/packages/snoeren-development/laravel-language-sync)

Sync other languages with a source language using `php artisan language:sync`! That'll add all missing translations
and remove those that are not in the source language anymore.

## Installation
You can install the package using Composer:
```bash
composer require snoeren-development/laravel-language-sync
```

### Requirements
This package requires at least PHP 7.4 and Laravel 7.

### Usage
Run `php artisan language:sync {main language} {targets}` to sync the targets with the main language. You can specify more
than one target at once. It is recommended to backup your files or stash/commit files before running this command.

### Culprits
This package currently doesn't work well with nested language strings, for example:
```php
return [
    'size' => [
        'large' => 'Large',
        'small' => 'Small',
    ],
]
```

## Credits
- [Michael Snoeren](https://github.com/MSnoeren)
- [All Contributors](https://github.com/snoeren-development/laravel-language-sync/graphs/contributors)

## License
The MIT license. See [LICENSE](LICENSE) for more information.
