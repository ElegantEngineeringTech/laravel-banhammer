<?php

declare(strict_types=1);

namespace Elegantly\Banhammer\Models\Concerns;

use Carbon\Carbon;
use Elegantly\Banhammer\Models\Ban;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;

/**
 * @template TBan of Ban
 *
 * @property Collection<int, TBan> $bans
 * @property ?int $ban_level
 */
trait Bannable
{
    /**
     * @return class-string<TBan>
     */
    public static function getModelBan(): string
    {
        return config('banhammer.model_ban');
    }

    /**
     * @return MorphMany<TBan, $this>
     */
    public function bans(): MorphMany
    {
        return $this->morphMany(static::getModelBan(), 'bannable');
    }

    /**
     * @return MorphOne<TBan, $this>
     */
    public function latestBan(): MorphOne
    {
        return $this->morphOne(static::getModelBan(), 'bannable')->latestOfMany();
    }

    /**
     * @param  null|array<array-key, mixed>  $metadata
     * @return TBan
     */
    public function ban(
        int $level = 0,
        ?string $reason = null,
        ?Carbon $from = null,
        ?Carbon $until = null,
        bool|int|User $user = true,
        ?array $metadata = null
    ): Ban {

        $this->bans->each(fn ($ban) => $ban->end());

        $model = static::getModelBan();

        $ban = new $model([
            'level' => $level,
            'reason' => $reason,
            'started_at' => $from ?? now(),
            'ended_at' => $until,
            'metadata' => $metadata,
        ]);

        if ($user === true) {
            $ban->user()->associate(Auth::user());
        } elseif ($user === false) {
            $ban->user()->associate(null);
        } else {
            $ban->user()->associate($user);
        }

        $this->bans()->save($ban);

        $this->bans->push($ban);

        $this->ban_level = $ban->isActive() ? $ban->level : null;
        $this->save();

        return $ban;
    }

    public function unban(): static
    {

        $this->bans->each(fn ($ban) => $ban->end());

        if ($this->ban_level) {
            $this->ban_level = null;
            $this->save();
        }

        return $this;
    }

    public function isBanned(?int $level = null): bool
    {
        if ($level) {
            return $this->ban_level === $level;
        }

        return $this->ban_level !== null;
    }

    public function isNotBanned(?int $level = null): bool
    {
        return ! $this->isBanned($level);
    }

    public function scopeBanned(Builder $query, ?int $level = null): Builder
    {
        if ($level) {
            return $query->where('ban_level', '=', $level);
        }

        return $query->where('ban_level', '!=', null);
    }

    public function scopeNotBanned(Builder $query, ?int $level = null): Builder
    {
        if ($level) {
            return $query->where('ban_level', '!=', $level);
        }

        return $query->where('ban_level', '=', null);
    }
}
