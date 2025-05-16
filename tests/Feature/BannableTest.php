<?php

declare(strict_types=1);

use Workbench\App\Models\User;

it('can ban a user', function ($level, $from, $until, $user, $expected) {
    $user = User::factory()->create();

    $user->ban(
        level: $level,
        from: $from ? now()->add($from) : null,
        until: $until ? now()->add($until) : null,
        user: $user
    );

    expect($user->isBanned())->toBe($expected);
    expect($user->isNotBanned())->toBe(! $expected);

    if ($expected) {
        expect($user->ban_level)->toBe($level);
    }

})->with([
    [0, null, null, true, true],
    [1, null, null, true, true],
    [0, '1 day', null, true, false],
    [0, null, '1 day', true, true],
]);

it('can query banned users', function ($from, $until, $expected) {
    $total = 3;

    $users = User::factory()
        ->times($total)
        ->create();

    $user = $users->last();

    $user->ban(
        from: $from ? now()->add($from) : null,
        until: $until ? now()->add($until) : null,
    );

    $banned_count = User::query()->banned()->count();

    expect($banned_count)->toBe($expected);

    $not_banned_count = User::query()->notBanned()->count();

    expect($not_banned_count)->toBe($total - $expected);

})->with([
    [null, null, 1],
    ['1 day', null, 0],
    [null, '1 day', 1],
]);
