<?php

namespace DTW\FilamentMultilanguage\Commands;

use Illuminate\Console\Command;

class FilamentMultilanguageCommand extends Command
{
    public $signature = 'filament-multilanguage';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
