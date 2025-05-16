<?php

declare(strict_types=1);

namespace Elegantly\Banhammer;

use Elegantly\Banhammer\Commands\DenormalizeBannableCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BanhammerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-banhammer')
            ->hasConfigFile()
            ->hasCommand(DenormalizeBannableCommand::class)
            ->hasMigration('create_bans_table')
            ->hasMigration('add_bannable_columns');
    }
}
