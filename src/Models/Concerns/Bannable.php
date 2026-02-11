<?php

declare(strict_types=1);

namespace Elegantly\Banhammer\Models\Concerns;

use BackedEnum;
use Carbon\Carbon;
use Elegantly\Banhammer\Models\Ban;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;

use function Illuminate\Support\enum_value;

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
        BackedEnum|int $level = 0,
        ?string $reason = null,
        ?Carbon $from = null,
        ?Carbon $until = null,
        bool|int|User $user = true,
        ?array $metadata = null
    ): Ban {

        $this->bans->each(fn ($ban) => $ban->end());

        $model = static::getModelBan();

        $ban = new $model([
            'level' => (int) enum_value($level),
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

    public function isBanned(null|BackedEnum|int $level = null): bool
    {
        if ($level) {
            return $this->ban_level === enum_value($level);
        }

        return $this->ban_level !== null;
    }

    public function isNotBanned(null|BackedEnum|int $level = null): bool
    {
        return ! $this->isBanned(enum_value($level));
    }

    public function scopeBanned(Builder $query, null|BackedEnum|int $level = null): Builder
    {
        if ($level) {
            return $query->where('ban_level', '=', enum_value($level));
        }

        return $query->where('ban_level', '!=', null);
    }

    public function scopeNotBanned(Builder $query, null|BackedEnum|int $level = null): Builder
    {
        if ($level) {
            return $query->where('ban_level', '!=', enum_value($level));
        }

        return $query->where('ban_level', '=', null);
    }
}
