<?php

declare(strict_types=1);

namespace Elegantly\Banhammer\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Elegantly\Banhammer\Banhammer
 */
class Banhammer extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Elegantly\Banhammer\Banhammer::class;
    }
}
