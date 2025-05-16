<?php

declare(strict_types=1);

namespace Elegantly\Banhammer\Commands;

use Elegantly\Banhammer\Models\Ban;
use Elegantly\Banhammer\Models\Contracts\BannableContract;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

class DenormalizeBannableCommand extends Command
{
    public $signature = 'banhammer:denormalize';

    public $description = 'Dernomalize ban status on bannable models';

    public function handle(): int
    {

        /** @var class-string<Model&BannableContract<Ban<User>>>[] */
        $bannables = config('banhammer.bannables');

        foreach ($bannables as $bannable) {

            // @phpstan-ignore-next-line
            $bannable::query()->banned()
                ->whereDoesntHave('bans', fn ($query) => $query->active())
                ->chunkById(500, function ($models) {

                    foreach ($models as $model) {
                        $model->ban_level = null;
                        $model->save();
                    }

                });

        }

        return self::SUCCESS;
    }
}
