<?php
declare(strict_types = 1);

namespace SnoerenDevelopment\LanguageSync\Commands;

use Illuminate\Console\Command;

class SyncLanguage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'language:sync
        {main language : Enter the main language, eg. "en"} ' .
        '{languages*? : Enter the languages to compare against. Omit this parameter ' .
        'to simply sync with all other available languages.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View the missing or overhead language strings based on the main language.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $mainLanguage = $this->argument('main language');
        dd($mainLanguage);
    }
}
