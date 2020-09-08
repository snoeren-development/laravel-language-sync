<?php
declare(strict_types = 1);

namespace SnoerenDevelopment\LanguageSync\Commands;

use DirectoryIterator;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;

class LanguageSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'language:sync
        {main language : Enter the main language, eg. "en"} ' .
        '{languages?* : Enter the languages to compare against. Omit this parameter ' .
        'to simply sync with all other available languages.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View the missing language strings based on the main language.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Retrieve the main language.
        $mainLanguage = $this->argument('main language');
        if (!$this->languageExists($mainLanguage)) {
            $this->error('The given main language does not exist.');
            return 1;
        }

        // Retrieve the other languages.
        $languages = !empty($this->argument('languages'))
            ? $this->argument('languages')
            : $this->getLanguages($mainLanguage);

        // Check every language if provided by console input.
        if (!empty($this->argument('languages'))) {
            foreach ($languages as $language) {
                if (!$this->languageExists($language)) {
                    $this->error(sprintf(
                        'The given language "%s" does not exist.',
                        $language
                    ));
                    return 1;
                }
            }
        }

        // Calculate the difference for every language.
        foreach ($languages as $language) {
            $differences = $this->compare($mainLanguage, $language);

            $this->info($language);
            foreach ($differences as $key => $_) {
                $this->line('- ' . $key);
            }
            $this->line('');
        }

        return 0;
    }

    /**
     * Check if the given language exists.
     *
     * @param  string $language The folder name.
     * @return boolean
     */
    private function languageExists(string $language): bool
    {
        return is_dir($this->getPath() . $language);
    }

    /**
     * Find all other languages besides the main language.
     *
     * @param  string $mainLanguage The main language.
     * @return array
     */
    private function getLanguages(string $mainLanguage): array
    {
        $iterator = new DirectoryIterator($this->getPath());
        $directories = [];

        foreach ($iterator as $resource) {
            if (!$resource->isDir() || $resource->isDot() || $resource->getBasename() === $mainLanguage) {
                continue;
            }

            $directories[] = $resource->getBasename();
        }

        return $directories;
    }

    /**
     * Find all the language files for a given language.
     *
     * @param  string $language The language.
     * @return array
     */
    private function getLanguageFiles(string $language): array
    {
        $iterator = new DirectoryIterator($this->getPath() . $language);
        $files = [];

        foreach ($iterator as $resource) {
            if (!$resource->isFile()) {
                continue;
            }

            $files[] = $resource->getBasename();
        }

        return $files;
    }

    /**
     * Get the path to the language folder.
     *
     * @return string
     */
    private function getPath(): string
    {
        return resource_path('lang/');
    }

    /**
     * Get the differences between two languages.
     *
     * @param  string $mainLanguage The main language.
     * @param  string $language     The language to compare with.
     * @return array
     */
    private function compare(string $mainLanguage, string $language): array
    {
        // Get the files from both languages.
        $mainFiles = $this->getLanguageFiles($mainLanguage);
        $files = $this->getLanguageFiles($language);

        // Get the main language strings.
        $mainStrings = [];
        foreach ($mainFiles as $file) {
            $path = $this->getPath() . $mainLanguage . '/' . $file;
            $strings = require $path;

            // Skip bad language files.
            if (!is_array($strings)) {
                continue;
            }

            $key = preg_replace('/\.php$/', '', $file);
            $mainStrings = array_merge($mainStrings, Arr::dot([$key => $strings]));
        }

        // Get the other language strings.
        $otherStrings = [];
        foreach ($files as $file) {
            $path = $this->getPath() . $language . '/' . $file;
            $strings = require $path;

            // Skip bad language files.
            if (!is_array($strings)) {
                continue;
            }

            $key = preg_replace('/\.php$/', '', $file);
            $otherStrings = array_merge($otherStrings, Arr::dot([$key => $strings]));
        }

        // Return the differences.
        return array_diff_key($mainStrings, $otherStrings);
    }
}
