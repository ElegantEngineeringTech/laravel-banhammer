<?php

declare(strict_types=1);

namespace Elegantly\Banhammer\Tests;

use Elegantly\Banhammer\BanhammerServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

use function Orchestra\Testbench\artisan;

#[WithMigration]
class TestCase extends Orchestra
{
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => match ($modelName) {
                User::class => UserFactory::class,
                default => 'Elegantly\\Banhammer\\Database\\Factories\\'.class_basename($modelName).'Factory',
            }
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            BanhammerServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'testing');

        $app['config']->set('banhammer.model_user', User::class);
        $app['config']->set('banhammer.bannables', [User::class]);
    }

    protected function defineDatabaseMigrations()
    {
        artisan($this, 'vendor:publish', ['--tag' => 'banhammer-migrations']);

        artisan($this, 'migrate');

        $this->beforeApplicationDestroyed(
            fn () => artisan($this, 'migrate:rollback')
        );
    }
}
