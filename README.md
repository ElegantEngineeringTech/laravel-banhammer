# This is my package laravel-banhammer

[![Latest Version on Packagist](https://img.shields.io/packagist/v/elegantly/laravel-banhammer.svg?style=flat-square)](https://packagist.org/packages/elegantly/laravel-banhammer)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/elegantly/laravel-banhammer/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/elegantly/laravel-banhammer/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/elegantly/laravel-banhammer/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/elegantly/laravel-banhammer/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/elegantly/laravel-banhammer.svg?style=flat-square)](https://packagist.org/packages/elegantly/laravel-banhammer)

A simple package to ban an model (users, teams...).

## Installation

You can install the package via composer:

```bash
composer require elegantly/laravel-banhammer
```

First, publish the config with:

```bash
php artisan vendor:publish --tag="banhammer-config"
```

Then define the bannable models in the config file like this:

```php
use Elegantly\Banhammer\Models\Ban;
use Illuminate\Foundation\Auth\User;

return [

    'model_ban' => Ban::class,

    'model_user' => User::class,

    'bannables' => [
        \App\Models\User::class,
        // \App\Models\Team::class
    ],

];

```

Next, publish and run the migrations with:

```bash
php artisan vendor:publish --tag="banhammer-migrations"
php artisan migrate
```

Finally schedule the command from `bootstrap/app.php` with:

```php
use Illuminate\Foundation\Application;
use Illuminate\Console\Scheduling\Schedule;
use Elegantly\Banhammer\Commands\DenormalizeBannableCommand;

return Application::configure(basePath: dirname(__DIR__))
    // ...
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command(DenormalizeBannableCommand::class)->everyMinute();
    })
    ->create();
```

Or from `routes/console.php` with:

```php
use Illuminate\Support\Facades\Schedule;
use Elegantly\Banhammer\Commands\DenormalizeBannableCommand;

Schedule::command(DenormalizeBannableCommand::class)->everyMinute();
```

## Usage

### Prepare your models:

```php
namespace App\Models;

use Elegantly\Banhammer\Models\Concerns\Bannable;
use Elegantly\Banhammer\Models\Contracts\BannableContract;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements BannableContract
{
    use Bannable;
}
```

```php
$user->ban(
    level: 0,
    reason: "spam",
    from: now(),
    until: null,
);
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Quentin Gabriele](https://github.com/QuentinGab)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
