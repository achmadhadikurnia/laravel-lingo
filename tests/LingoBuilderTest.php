<?php

use Kanekescom\Lingo\Lingo;
use Kanekescom\Lingo\LingoBuilder;

describe('LingoBuilder chainable methods', function () {
    it('can be created via Lingo::make', function () {
        $builder = Lingo::make(['Hello' => 'Halo']);

        expect($builder)->toBeInstanceOf(LingoBuilder::class);
        expect($builder->get())->toBe(['Hello' => 'Halo']);
    });

    it('can be created via static make method', function () {
        $builder = LingoBuilder::make(['World' => 'Dunia']);

        expect($builder)->toBeInstanceOf(LingoBuilder::class);
        expect($builder->get())->toBe(['World' => 'Dunia']);
    });

    it('can sort keys ascending', function () {
        $builder = LingoBuilder::make(['z' => 'last', 'a' => 'first', 'm' => 'middle']);

        $result = $builder->sortKeys()->get();
        $keys = array_keys($result);

        expect($keys[0])->toBe('a');
        expect($keys[2])->toBe('z');
    });

    it('can sort keys descending', function () {
        $builder = LingoBuilder::make(['z' => 'last', 'a' => 'first']);

        $result = $builder->sortKeys(false)->get();
        $keys = array_keys($result);

        expect($keys[0])->toBe('z');
        expect($keys[1])->toBe('a');
    });

    it('can clean translations', function () {
        $builder = LingoBuilder::make([
            'valid' => 'value',
            'empty' => '',
            'another' => 'test',
        ]);

        $result = $builder->clean()->get();

        expect($result)->toHaveCount(2);
        expect($result)->toHaveKey('valid');
    });

    it('can add missing keys', function () {
        $builder = LingoBuilder::make(['Hello' => 'Halo']);

        $result = $builder->add(['Hello', 'World', 'Goodbye'])->get();

        expect($result)->toHaveCount(3);
        expect($result['World'])->toBe('World');
        expect($result['Goodbye'])->toBe('Goodbye');
    });

    it('can remove unused keys', function () {
        $builder = LingoBuilder::make([
            'Hello' => 'Halo',
            'World' => 'Dunia',
            'Unused' => 'Tidak Dipakai',
        ]);

        $result = $builder->remove(['Hello', 'World'])->get();

        expect($result)->toHaveCount(2);
        expect($result)->not->toHaveKey('Unused');
    });

    it('can remove empty values', function () {
        $builder = LingoBuilder::make([
            'valid' => 'value',
            'empty' => '',
            'another' => 'test',
        ]);

        $result = $builder->removeEmpty()->get();

        expect($result)->toHaveCount(2);
    });

    it('can filter to only untranslated', function () {
        $builder = LingoBuilder::make([
            'Hello' => 'Halo',
            'World' => 'World',
        ]);

        $result = $builder->onlyUntranslated()->get();

        expect($result)->toHaveCount(1);
        expect($result)->toHaveKey('World');
    });

    it('can filter to only translated', function () {
        $builder = LingoBuilder::make([
            'Hello' => 'Halo',
            'World' => 'World',
        ]);

        $result = $builder->onlyTranslated()->get();

        expect($result)->toHaveCount(1);
        expect($result)->toHaveKey('Hello');
    });

    it('can merge translations', function () {
        $builder = LingoBuilder::make(['Hello' => 'Halo']);

        $result = $builder->merge(['World' => 'Dunia'])->get();

        expect($result)->toHaveCount(2);
        expect($result['World'])->toBe('Dunia');
    });

    it('can transform translations with callback', function () {
        $builder = LingoBuilder::make(['Hello' => 'Halo']);

        $result = $builder->transform(function ($translations) {
            return array_map('strtoupper', $translations);
        })->get();

        expect($result['Hello'])->toBe('HALO');
    });

    it('can tap translations without modifying', function () {
        $tapped = null;
        $builder = LingoBuilder::make(['Hello' => 'Halo']);

        $builder->tap(function ($translations) use (&$tapped) {
            $tapped = $translations;
        });

        expect($tapped)->toBe(['Hello' => 'Halo']);
    });

    it('can get statistics', function () {
        $builder = LingoBuilder::make([
            'Hello' => 'Halo',
            'World' => 'World',
        ]);

        $stats = $builder->stats();

        expect($stats['total'])->toBe(2);
        expect($stats['translated'])->toBe(1);
        expect($stats['untranslated'])->toBe(1);
    });

    it('can count translations', function () {
        $builder = LingoBuilder::make(['a' => 'b', 'c' => 'd']);

        expect($builder->count())->toBe(2);
    });

    it('can check if empty', function () {
        expect(LingoBuilder::make([])->isEmpty())->toBeTrue();
        expect(LingoBuilder::make(['a' => 'b'])->isEmpty())->toBeFalse();
    });

    it('can check if not empty', function () {
        expect(LingoBuilder::make([])->isNotEmpty())->toBeFalse();
        expect(LingoBuilder::make(['a' => 'b'])->isNotEmpty())->toBeTrue();
    });

    it('can convert to array', function () {
        $builder = LingoBuilder::make(['Hello' => 'Halo']);

        expect($builder->toArray())->toBe(['Hello' => 'Halo']);
    });

    it('can convert to JSON', function () {
        $builder = LingoBuilder::make(['Hello' => 'Halo']);

        $json = $builder->toJson();

        expect($json)->toBeString();
        expect(json_decode($json, true))->toBe(['Hello' => 'Halo']);
    });

    it('can save to file', function () {
        $tempDir = sys_get_temp_dir().'/lingo-builder-'.getmypid();
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $filePath = $tempDir.'/translations.json';
        $builder = LingoBuilder::make(['Hello' => 'Halo', 'World' => 'Dunia']);

        $result = $builder->save($filePath);

        expect($result)->toBeTrue();
        expect(file_exists($filePath))->toBeTrue();

        $content = json_decode(file_get_contents($filePath), true);
        expect($content)->toHaveKey('Hello');
        expect($content)->toHaveKey('World');

        @unlink($filePath);
        @rmdir($tempDir);
    });

    it('can chain multiple operations', function () {
        $builder = LingoBuilder::make([
            'z' => 'z',
            'a' => 'translated',
            'empty' => '',
        ]);

        $result = $builder
            ->add(['new'])
            ->removeEmpty()
            ->sortKeys()
            ->get();

        $keys = array_keys($result);

        expect($result)->toHaveCount(3);
        expect($keys[0])->toBe('a');
        expect($result)->toHaveKey('new');
    });

    it('can create empty builder', function () {
        $builder = LingoBuilder::make();

        expect($builder->get())->toBe([]);
        expect($builder->isEmpty())->toBeTrue();
        expect($builder->count())->toBe(0);
    });

    it('can handle empty translations in stats', function () {
        $builder = LingoBuilder::make([]);

        $stats = $builder->stats();

        expect($stats['total'])->toBe(0);
        expect($stats['percentage'])->toBe(0);
    });

    it('can load by locale using Lingo::locale', function () {
        $langDir = lang_path();
        if (! is_dir($langDir)) {
            mkdir($langDir, 0777, true);
        }

        $filePath = lang_path('test-locale.json');
        file_put_contents($filePath, json_encode(['Hello' => 'Halo'], JSON_PRETTY_PRINT));

        $builder = Lingo::locale('test-locale');

        expect($builder)->toBeInstanceOf(LingoBuilder::class);
        expect($builder->get())->toBe(['Hello' => 'Halo']);

        @unlink($filePath);
    });

    it('can load from file using Lingo::fromFile', function () {
        $tempDir = sys_get_temp_dir().'/lingo-fromfile-'.getmypid();
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $filePath = $tempDir.'/test.json';
        file_put_contents($filePath, json_encode(['World' => 'Dunia'], JSON_PRETTY_PRINT));

        $builder = Lingo::fromFile($filePath);

        expect($builder)->toBeInstanceOf(LingoBuilder::class);
        expect($builder->get())->toBe(['World' => 'Dunia']);

        @unlink($filePath);
        @rmdir($tempDir);
    });

    it('can set target locale using to method', function () {
        $langDir = lang_path();
        if (! is_dir($langDir)) {
            mkdir($langDir, 0777, true);
        }

        $filePath = lang_path('to-test.json');

        $builder = LingoBuilder::make(['Test' => 'Tes']);
        $result = $builder->to('to-test')->save();

        expect($result)->toBeTrue();
        expect(file_exists($filePath))->toBeTrue();

        $content = json_decode(file_get_contents($filePath), true);
        expect($content)->toBe(['Test' => 'Tes']);

        @unlink($filePath);
    });

    it('can sync with default views path', function () {
        $builder = LingoBuilder::make(['Existing' => 'Ada']);

        // sync() with null uses resource_path('views') as default
        // This test just ensures the method runs without error
        $result = $builder->sync();

        expect($result)->toBeInstanceOf(LingoBuilder::class);
    });

    it('can sync with single path string', function () {
        $tempDir = sys_get_temp_dir().'/lingo-sync-single-'.getmypid();
        mkdir($tempDir, 0777, true);

        file_put_contents($tempDir.'/test.php', "<?php echo __('NewKey');");

        $builder = LingoBuilder::make(['Existing' => 'Ada']);
        $result = $builder->sync($tempDir)->get();

        expect($result)->toHaveKey('NewKey');
        expect($result)->not->toHaveKey('Existing'); // removed as unused

        @unlink($tempDir.'/test.php');
        @rmdir($tempDir);
    });

    it('can sync with array of paths', function () {
        $tempDir1 = sys_get_temp_dir().'/lingo-sync-arr1-'.getmypid();
        $tempDir2 = sys_get_temp_dir().'/lingo-sync-arr2-'.getmypid();
        mkdir($tempDir1, 0777, true);
        mkdir($tempDir2, 0777, true);

        file_put_contents($tempDir1.'/file1.php', "<?php echo __('Key1');");
        file_put_contents($tempDir2.'/file2.php', "<?php echo __('Key2');");

        $builder = LingoBuilder::make([]);
        $result = $builder->sync([$tempDir1, $tempDir2])->get();

        expect($result)->toHaveKey('Key1');
        expect($result)->toHaveKey('Key2');

        @unlink($tempDir1.'/file1.php');
        @unlink($tempDir2.'/file2.php');
        @rmdir($tempDir1);
        @rmdir($tempDir2);
    });

    it('can load from relative path using LingoBuilder::load', function () {
        $langDir = lang_path();
        if (! is_dir($langDir)) {
            mkdir($langDir, 0777, true);
        }

        file_put_contents(lang_path('relative-test.json'), json_encode(['Rel' => 'Tive'], JSON_PRETTY_PRINT));

        $builder = LingoBuilder::load('relative-test.json');

        expect($builder->get())->toBe(['Rel' => 'Tive']);

        @unlink(lang_path('relative-test.json'));
    });

    it('handles locale that creates empty builder when file not exists', function () {
        $builder = Lingo::locale('non-existent-locale-xyz');

        expect($builder)->toBeInstanceOf(LingoBuilder::class);
        expect($builder->isEmpty())->toBeTrue();
    });
});
