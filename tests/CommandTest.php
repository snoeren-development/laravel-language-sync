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
