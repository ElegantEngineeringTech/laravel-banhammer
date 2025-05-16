<?php

declare(strict_types=1);

namespace Elegantly\Banhammer\Models\Contracts;

use Carbon\Carbon;
use Elegantly\Banhammer\Models\Ban;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User;

/**
 * @template TBan of Ban
 * 
 * @property Collection<int, TBan> $bans
 * @property ?int $ban_level
 */
interface BannableContract
{
    /**
     * @return MorphMany<TBan, Model>
     */
    public function bans(): MorphMany;

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
    ): Ban;

    public function isBanned(): bool;

    public function isNotBanned(): bool;
}
