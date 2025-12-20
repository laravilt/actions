<?php

use Laravilt\Actions\ExportAction;

describe('ExportAction Basic Features', function () {
    it('can be instantiated with make method', function () {
        $action = ExportAction::make('export');

        expect($action)->toBeInstanceOf(ExportAction::class)
            ->and($action->getName())->toBe('export');
    });

    it('has default name of export', function () {
        $action = ExportAction::make();

        expect($action->getName())->toBe('export');
    });

    it('has default icon of download', function () {
        $action = ExportAction::make();

        expect($action->getIcon())->toBe('download');
    });

    it('has default color of gray', function () {
        $action = ExportAction::make();

        expect($action->getColor())->toBe('gray');
    });
});

describe('ExportAction Exporter Configuration', function () {
    it('can set exporter class', function () {
        $action = ExportAction::make()
            ->exporter('App\\Exports\\CustomerExporter');

        expect($action->getExporterClass())->toBe('App\\Exports\\CustomerExporter');
    });

    it('can set custom filename', function () {
        $action = ExportAction::make()
            ->fileName('customers.xlsx');

        expect($action->getFileName())->toBe('customers.xlsx');
    });

    it('generates default filename with timestamp when not set', function () {
        $action = ExportAction::make();
        $filename = $action->getFileName();

        expect($filename)->toStartWith('export-')
            ->and($filename)->toEndWith('.xlsx');
    });
});

describe('ExportAction Writer Types', function () {
    it('can set xlsx format', function () {
        $action = ExportAction::make()->xlsx();
        $filename = $action->getFileName();

        expect($filename)->toEndWith('.xlsx');
    });

    it('can set csv format', function () {
        $action = ExportAction::make()->csv();
        $filename = $action->getFileName();

        expect($filename)->toEndWith('.csv');
    });

    it('can set writer type directly', function () {
        $action = ExportAction::make()
            ->writerType(\Maatwebsite\Excel\Excel::CSV);
        $filename = $action->getFileName();

        expect($filename)->toEndWith('.csv');
    });

    it('can set formats from array', function () {
        $action = ExportAction::make()
            ->formats([\Maatwebsite\Excel\Excel::CSV]);
        $filename = $action->getFileName();

        expect($filename)->toEndWith('.csv');
    });
});

describe('ExportAction Queue and Disk', function () {
    it('can enable queue', function () {
        $action = ExportAction::make()->queue();

        // Queue setting is internal, verify action is still valid
        expect($action)->toBeInstanceOf(ExportAction::class);
    });

    it('can set disk', function () {
        $action = ExportAction::make()->disk('s3');

        expect($action)->toBeInstanceOf(ExportAction::class);
    });

    it('can set file path', function () {
        $action = ExportAction::make()->filePath('exports/custom');

        expect($action)->toBeInstanceOf(ExportAction::class);
    });
});

describe('ExportAction Query Modification', function () {
    it('can set query modifier', function () {
        $action = ExportAction::make()
            ->modifyQueryUsing(fn ($query) => $query->where('active', true));

        expect($action)->toBeInstanceOf(ExportAction::class);
    });

    it('can set columns', function () {
        $action = ExportAction::make()
            ->columns(['id', 'name', 'email']);

        expect($action)->toBeInstanceOf(ExportAction::class);
    });

    it('can set headings', function () {
        $action = ExportAction::make()
            ->headings(['ID', 'Name', 'Email']);

        expect($action)->toBeInstanceOf(ExportAction::class);
    });
});

describe('ExportAction Serialization', function () {
    it('serializes to array with url for download', function () {
        $action = ExportAction::make()
            ->exporter('App\\Exports\\CustomerExporter');

        $array = $action->toArray();

        expect($array)->toHaveKey('name')
            ->and($array)->toHaveKey('url')
            ->and($array)->toHaveKey('openUrlInNewTab')
            ->and($array['name'])->toBe('export')
            ->and($array['openUrlInNewTab'])->toBeTrue();
    });

    it('uses GET method for download URL', function () {
        $action = ExportAction::make()
            ->exporter('App\\Exports\\CustomerExporter');

        $array = $action->toArray();

        expect($array['method'])->toBe('GET');
    });
});

describe('ExportAction Method Chaining', function () {
    it('supports method chaining', function () {
        $action = ExportAction::make()
            ->exporter('App\\Exports\\CustomerExporter')
            ->fileName('customers.xlsx')
            ->xlsx()
            ->disk('public')
            ->queue()
            ->columns(['id', 'name'])
            ->headings(['ID', 'Name']);

        expect($action)->toBeInstanceOf(ExportAction::class);
    });
});
