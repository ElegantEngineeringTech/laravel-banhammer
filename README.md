# Laravel Banhammer

**Laravel Banhammer** is a robust, flexible solution for managing model bans (Users, Teams, Organizations, etc.) with support for ban levels, expiration dates, and performance-optimized querying.

## Key Features

- **Polymorphic:** Ban any Eloquent model.
- **Time-aware:** Support for permanent or temporary bans.
- **Tiered Restrictions:** Use "levels" to define the severity of the ban.
- **Performance First:** Includes a denormalization command to keep your queries fast.

---

## Installation

Install the package via composer:

```bash
composer require elegantly/laravel-banhammer

```

### 1. Configure Your Models

Publish the configuration file:

```bash
php artisan vendor:publish --tag="banhammer-config"

```

In `config/banhammer.php`, list the models that can be banned:

```php
return [
    'model_ban' => Elegantly\Banhammer\Models\Ban::class,

    'bannables' => [
        \App\Models\User::class,
        \App\Models\Team::class,
    ],
];

```

### 2. Migrations

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="banhammer-migrations"
php artisan migrate

```

### 3. Schedule the Denormalizer

This package uses a denormalization strategy to ensure checking a user's ban status doesn't require a heavy database join every time.

Add the command to your `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;
use Elegantly\Banhammer\Commands\DenormalizeBannableCommand;

// This updates the 'is_banned' status on your models based on ban expiration dates
Schedule::command(DenormalizeBannableCommand::class)->everyMinute();
```

---

## Usage

### Preparing the Model

Implement the `BannableContract` and use the `Bannable` trait in any model you wish to restrict:

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

### Banning a Model

You can specify the severity (level), the reason, and the duration.

```php
$user = User::find(1);

// Ban indefinitely
$user->ban(
    level: 0,
    reason: "Repeated spamming",
);

// Ban until a specific date (Temporary)
$user->ban(
    level: 1,
    reason: "Cooling off period",
    until: now()->addDays(7)
);

```

### Checking Ban Status

The package provides helpful methods to check if a model is currently restricted.

```php
// Check if currently banned
if ($user->isBanned()) {
    return response()->json(['error' => 'Your account is suspended.'], 403);
}

if ($user->isBanned(1)) { // $user->ban_level >= 1
    // Restricted from specific high-level actions
}

// Check for a specific ban level
if ($user->ban_level === 1) {
    // Restricted from specific high-level actions
}

```

### Unbanning

To lift all bans from a model:

```php
$user->unban();

```

---

## Testing

```bash
composer test

```

## Contributing

Please see [CONTRIBUTING](https://www.google.com/search?q=CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
