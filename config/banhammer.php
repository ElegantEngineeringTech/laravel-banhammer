<?php

declare(strict_types=1);

use Elegantly\Banhammer\Models\Ban;
use Illuminate\Foundation\Auth\User;

return [

    'model_ban' => Ban::class,

    'model_user' => User::class,

    'bannables' => [
        // \App\Models\User::class
    ],

];
