<?php

declare(strict_types=1);

namespace Elegantly\Banhammer\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User;

/**
 * @template TUser of User
 *
 * @property int $id
 * @property ?int $user_id
 * @property ?TUser $user
 * @property int $level
 * @property string $reason
 * @property Model $bannable
 * @property string $bannable_type
 * @property int $bannable_id
 * @property Carbon $started_at
 * @property ?Carbon $ended_at
 * @property ?array<array-key, mixed> $metadata
 * @property Carbon $updated_at
 * @property Carbon $created_at
 */
class Ban extends Model
{
    public $guarded = ['id'];

    public $attributes = [
        'level' => 0,
    ];

    public function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * @return class-string<TUser>
     */
    public static function getModelUser(): string
    {
        // @phpstan-ignore-next-line
        return config('banhammer.model_user');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function bannable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<TUser, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(static::getModelUser());
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query
                ->where('ended_at', '=', null)
                ->orWhere('ended_at', '>', now());
        });

    }

    public function isActive(): bool
    {
        if ($this->ended_at) {
            return $this->started_at->isNowOrPast() && $this->ended_at->isFuture();
        }

        return $this->started_at->isNowOrPast();

    }

    public function isNotActive(): bool
    {
        return ! $this->isActive();
    }

    public function end(): static
    {
        if ($this->isActive()) {
            $this->ended_at = now();
            $this->save();
        }

        return $this;
    }
}
