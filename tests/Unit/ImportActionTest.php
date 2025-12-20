<?php

use Laravilt\Actions\ImportAction;

describe('ImportAction Basic Features', function () {
    it('can be instantiated with make method', function () {
        $action = ImportAction::make('import');

        expect($action)->toBeInstanceOf(ImportAction::class)
            ->and($action->getName())->toBe('import');
    });

    it('has default name of import', function () {
        $action = ImportAction::make();

        expect($action->getName())->toBe('import');
    });

    it('has default icon of upload', function () {
        $action = ImportAction::make();

        expect($action->getIcon())->toBe('upload');
    });

    it('has default color of gray', function () {
        $action = ImportAction::make();

        expect($action->getColor())->toBe('gray');
    });

    it('requires confirmation by default', function () {
        $action = ImportAction::make();

        expect($action->getRequiresConfirmation())->toBeTrue();
    });
});

describe('ImportAction Importer Configuration', function () {
    it('can set importer class', function () {
        $action = ImportAction::make()
            ->importer('App\\Imports\\CustomerImporter');

        expect($action->getImporterClass())->toBe('App\\Imports\\CustomerImporter');
    });
});

describe('ImportAction Reader Types', function () {
    it('can set xlsx format', function () {
        $action = ImportAction::make()->xlsx();

        expect($action)->toBeInstanceOf(ImportAction::class);
    });

    it('can set csv format', function () {
        $action = ImportAction::make()->csv();

        expect($action)->toBeInstanceOf(ImportAction::class);
    });

    it('can set reader type directly', function () {
        $action = ImportAction::make()
            ->readerType(\Maatwebsite\Excel\Excel::CSV);

        expect($action)->toBeInstanceOf(ImportAction::class);
    });
});

describe('ImportAction File Types', function () {
    it('can set accepted file types', function () {
        $action = ImportAction::make()
            ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel']);

        expect($action)->toBeInstanceOf(ImportAction::class);
    });
});

describe('ImportAction Queue and Disk', function () {
    it('can enable queue', function () {
        $action = ImportAction::make()->queue();

        expect($action)->toBeInstanceOf(ImportAction::class);
    });

    it('can set disk', function () {
        $action = ImportAction::make()->disk('local');

        expect($action)->toBeInstanceOf(ImportAction::class);
    });

    it('can set chunk size', function () {
        $action = ImportAction::make()->chunkSize(1000);

        expect($action)->toBeInstanceOf(ImportAction::class);
    });
});

describe('ImportAction Callbacks', function () {
    it('can set before import callback', function () {
        $action = ImportAction::make()
            ->beforeImport(fn ($file) => null);

        expect($action)->toBeInstanceOf(ImportAction::class);
    });

    it('can set after import callback', function () {
        $action = ImportAction::make()
            ->afterImport(fn ($file) => null);

        expect($action)->toBeInstanceOf(ImportAction::class);
    });
});

describe('ImportAction Modal Form Schema', function () {
    it('generates modal form schema with hidden importer and file upload', function () {
        $action = ImportAction::make()
            ->importer('App\\Imports\\CustomerImporter');

        $schema = $action->getModalFormSchema();

        expect($schema)->toBeArray()
            ->and($schema)->toHaveCount(2);
    });
});

describe('ImportAction Serialization', function () {
    it('serializes to array with hasAction true', function () {
        $action = ImportAction::make()
            ->importer('App\\Imports\\CustomerImporter');

        $array = $action->toArray();

        expect($array)->toHaveKey('name')
            ->and($array)->toHaveKey('hasAction')
            ->and($array)->toHaveKey('url')
            ->and($array['name'])->toBe('import')
            ->and($array['hasAction'])->toBeTrue();
    });

    it('includes accepted file types in serialization', function () {
        $action = ImportAction::make()
            ->importer('App\\Imports\\CustomerImporter');

        $array = $action->toArray();

        expect($array)->toHaveKey('acceptedFileTypes')
            ->and($array['acceptedFileTypes'])->toBeArray();
    });

    it('includes importer data in serialization', function () {
        $action = ImportAction::make()
            ->importer('App\\Imports\\CustomerImporter');

        $array = $action->toArray();

        expect($array)->toHaveKey('data')
            ->and($array['data'])->toHaveKey('importer')
            ->and($array['data']['importer'])->toBe('App\\Imports\\CustomerImporter');
    });
});

describe('ImportAction Method Chaining', function () {
    it('supports method chaining', function () {
        $action = ImportAction::make()
            ->importer('App\\Imports\\CustomerImporter')
            ->xlsx()
            ->disk('local')
            ->queue()
            ->chunkSize(500)
            ->acceptedFileTypes(['text/csv'])
            ->beforeImport(fn ($file) => null)
            ->afterImport(fn ($file) => null);

        expect($action)->toBeInstanceOf(ImportAction::class);
    });
});
