<?php
declare(strict_types = 1);

beforeEach(function (): void {
    // Overwrite the language path to this test directory.
    app()->instance('path.lang', __DIR__ . '/resources/lang');

    // Remove the resulting files before each test.
    if (is_file($jsonFile = lang_path('nl.json'))) {
        unlink($jsonFile);
    }
    if (is_file($authFile = lang_path('nl/auth.php'))) {
        unlink($authFile);
    }

    // Restore any removed translations.
    file_put_contents(lang_path('en.json'), json_encode(['much' => 'wow', 'such' => 'amaze'], JSON_PRETTY_PRINT));
    file_put_contents(
        lang_path('en/auth.php'),
        <<<'PHP'
        <?php
        declare(strict_types = 1);

        return [
            'string-1' => 'Translation 1',
            'string-2' => 'Translation 2',
            'multi' => [
                'dimensional' => [
                    'translations' => [
                        'much' => 'wow',
                        'such' => 'amaze',
                    ],
                ],
            ],
        ];

        PHP
    );
});

it('throws an exception when missing all arguments', function (): void {
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Not enough arguments (missing: "main language, languages").');
    $this
        ->artisan('language:sync')
        ->assertFailed();
});

it('throws an exception when missing other languages', function (): void {
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Not enough arguments (missing: "languages").');
    $this
        ->artisan('language:sync en')
        ->assertFailed();
});

it('throws an exception when the source language does not exist', function (): void {
    $this
        ->artisan('language:sync it nl')
        ->expectsOutput('The given source language (it) does not exist.')
        ->assertFailed();
});

it('asks to overwrite files without force option', function (): void {
    $this
        ->artisan('language:sync en nl')
        ->expectsQuestion(
            'This action could overwrite files. Consider backing up all language files or ' .
            'using version control. Continue? [y/n]',
            'n'
        )
        ->assertSuccessful();
});

it('syncs without asking with force option', function (): void {
    $this
        ->artisan('language:sync en nl --force')
        ->assertSuccessful();
});

it('syncs json files', function (): void {
    $this
        ->artisan('language:sync en nl --force')
        ->assertSuccessful();

    $this->assertFileExists($file = lang_path('nl.json'));
    $this->assertNotFalse(strpos(file_get_contents($file), '"much": "__MISSING_TRANSLATION__"'));
});

it('syncs php files', function (): void {
    $this
        ->artisan('language:sync en nl --force')
        ->assertSuccessful();

    $this->assertFileExists($file = lang_path('nl/auth.php'));
    $this->assertNotFalse(strpos(file_get_contents($file), "'string-1' => '__MISSING_TRANSLATION__',"));
});

it('handles multi-dimensional php translations', function (): void {
    $this
        ->artisan('language:sync en nl --force')
        ->assertSuccessful();

    $this->assertFileExists($file = lang_path('nl/auth.php'));

    $translations = require $file;

    $this->assertTrue(array_key_exists('string-1', $translations));
    $this->assertTrue(array_key_exists('multi', $translations) && is_array($translations['multi']));
    $this->assertTrue(
        array_key_exists('dimensional', $translations['multi']) && is_array($translations['multi']['dimensional'])
    );
    $this->assertSame('__MISSING_TRANSLATION__', $translations['multi']['dimensional']['translations']['much']);
});

it('does not sync json if the main translation doesnt exist', function (): void {
    unlink(lang_path('en.json'));

    $this
        ->artisan('language:sync en nl --force')
        ->assertSuccessful();

    $this->assertFileDoesNotExist(lang_path('nl.json'));
});

it('syncs with an existing json file if both exist', function (): void {
    file_put_contents($target = lang_path('nl.json'), json_encode(['such' => 'owo'], JSON_PRETTY_PRINT));

    $this
        ->artisan('language:sync en nl --force')
        ->assertSuccessful();

    $content = file_get_contents($target);
    $this->assertNotFalse(strpos($content, '"such": "owo"'));
    $this->assertNotFalse(strpos($content, '"much": "__MISSING_TRANSLATION__"'));
});

it('removes json keys from an existing translation if the source is missing it', function (): void {
    file_put_contents($target = lang_path('nl.json'), json_encode(['string' => 'wow'], JSON_PRETTY_PRINT));

    $this
        ->artisan('language:sync en nl --force')
        ->assertSuccessful();

    $content = file_get_contents($target);
    $this->assertFalse(strpos($content, '"string": "wow"'));
    $this->assertNotFalse(strpos($content, '"much": "__MISSING_TRANSLATION__"'));
});

it('syncs the existing target php file if it already exists', function (): void {
    file_put_contents(
        $target = lang_path('nl/auth.php'),
        <<<'PHP'
        <?php

        return [
            'such' => 'amaze',
        ];

        PHP
    );

    $this
        ->artisan('language:sync en nl --force')
        ->assertSuccessful();

    $translations = require $target;
    $this->assertSame('__MISSING_TRANSLATION__', $translations['string-1']);
});

it('removes keys that are missing in the main translation', function (): void {
    file_put_contents(
        $target = lang_path('nl/auth.php'),
        <<<'PHP'
        <?php

        return [
            'such' => 'amaze',
        ];

        PHP
    );

    $this
        ->artisan('language:sync en nl --force')
        ->assertSuccessful();

    $translations = require $target;
    $this->assertFalse(array_key_exists('such', $translations));
});

it('creates folders for translations if not already there', function (): void {
    rmdir($dir = lang_path('nl'));

    $this
        ->artisan('language:sync en nl --force')
        ->assertSuccessful();

    $this->assertTrue(is_dir($dir));
});
