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
    protected $signature = 'language:sync {--force}
         {main language : Enter the main language, eg. "en"} ' .
        '{languages* : Enter the languages to compare against.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View the missing language strings based on the main language.';

    /**
     * Execute the console command.
     *
     * @return integer
     */
    public function handle(): int
    {
        // Determine whether the main language exists.
        if (!$this->exists($source = $this->argument('main language'))) {
            $this->error("The given source language ({$source}) does not exist.");
            return Command::FAILURE;
        }

        $targets = array_map('strtolower', (array) $this->argument('languages'));

        // Check with the developer before continuing.
        if (!$this->option('force')) {
            if (strtolower($this->ask(
                'This action could overwrite files. Consider backing up all language files or ' .
                'using version control. Continue? [y/n]',
                'y'
            )) !== 'y') {
                return Command::SUCCESS;
            }
        }

        $files = $this->getLanguageFiles($source);

        // Sync all JSON files with the requested languages.
        foreach ($targets as $target) {
            $this->syncJson($source, $target);

            foreach ($files as $file) {
                $this->syncFile($source, $target, pathinfo($file, PATHINFO_BASENAME));
            }

            $this->line("Synced \"{$target}\" with \"{$source}\"!");
        }

        $this->info('Done!');

        return Command::SUCCESS;
    }

    /**
     * Check if the given language exists.
     *
     * @param  string $language The folder name.
     * @return boolean
     */
    private function exists(string $language): bool
    {
        return is_dir(lang_path($language)) || is_file(lang_path($language . '.json'));
    }

    /**
     * Find all the language files for a given language.
     *
     * @param  string $language The language.
     * @return string[]
     */
    private function getLanguageFiles(string $language): array
    {
        $iterator = new DirectoryIterator(lang_path($language));
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
     * Sync one JSON language file to another.
     *
     * @param  string $source The source language.
     * @param  string $target The target language.
     * @return boolean
     */
    private function syncJson(string $source, string $target): bool
    {
        $sourceFile = lang_path("{$source}.json");
        $targetFile = lang_path("{$target}.json");

        // Check whether there is a file to sync with.
        if (!is_file($sourceFile)) {
            return true;
        }

        // Read both files if available.
        $sourceData = (array) json_decode(file_get_contents($sourceFile));
        $targetData = is_file($targetFile)
            ? (array) json_decode(file_get_contents($targetFile))
            : [];

        $difference = array_diff_key($sourceData, $targetData);

        // Add missing translations.
        foreach (array_keys($difference) as $key) {
            $targetData[$key] = '__MISSING_TRANSLATION__';
        }

        // Remove non-existing translations.
        foreach (array_keys(array_diff_key($targetData, $sourceData)) as $key) {
            unset($targetData[$key]);
        }

        // Write the updated translation to disk.
        return file_put_contents($targetFile, json_encode($targetData, JSON_PRETTY_PRINT)) > 0;
    }

    /**
     * Sync one language file to another.
     *
     * @param  string $source The source language.
     * @param  string $target The target language.
     * @param  string $file   The file name.
     * @return boolean
     */
    private function syncFile(string $source, string $target, string $file): bool
    {
        $targetFile = lang_path("{$target}/{$file}");

        $sourceData = Arr::dot(require lang_path("{$source}/{$file}"));
        $targetData = is_file($targetFile)
            ? Arr::dot(require $targetFile)
            : [];

        $difference = array_diff_key($sourceData, $targetData);

        // Add missing translations.
        foreach (array_keys($difference) as $key) {
            $targetData[$key] = "__MISSING_TRANSLATION__";
        }

        // Remove non-existing translations.
        foreach (array_keys(array_diff_key($targetData, $sourceData)) as $key) {
            unset($targetData[$key]);
        }

        // Determine if the language folder exists.
        if (!is_dir($folder = dirname($targetFile))) {
            mkdir($folder);
        }

        $targetData = Arr::undot($targetData);

        // Write the updated translation to disk.
        $buffer = '<?php' . PHP_EOL . PHP_EOL . 'return [' . PHP_EOL;
        $buffer .= $this->writePhpArray($targetData, 1);
        $buffer .= '];' . PHP_EOL;

        return file_put_contents($targetFile, $buffer) > 0;
    }

    /**
     * Recursively write a PHP array with array support.
     *
     * @param  array   $translations The translation list.
     * @param  integer $level        The current recursion level.
     * @return string
     */
    private function writePhpArray(array $translations, int $level): string
    {
        $buffer = '';

        foreach ($translations as $key => $value) {
            if (is_array($value)) {
                $buffer .= str_repeat('    ', $level) .
                    "'{$key}' => [" . PHP_EOL .
                    $this->writePhpArray($value, $level + 1)
                    . str_repeat('    ', $level) . '],' . PHP_EOL;
                continue;
            }

            $value = addslashes($value);
            $buffer .= str_repeat('    ', $level) . "'{$key}' => '{$value}'," . PHP_EOL;
        }

        return $buffer;
    }
}
