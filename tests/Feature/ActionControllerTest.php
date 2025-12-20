<?php

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;

/**
 * Note: These tests require the full panel package to be installed
 * because the routes use HandleLocalization middleware from the panel package.
 * The tests are skipped when the routes are not properly registered.
 */
beforeEach(function () {
    // Skip all tests if routes are not registered
    if (! Route::has('actions.execute')) {
        $this->markTestSkipped('Action routes not registered');
    }
});

describe('ActionController Execute Endpoint', function () {
    it('returns 404 when component not found', function () {
        $token = Crypt::encrypt([
            'component' => 'App\\NonExistent\\Component',
            'id' => 1,
            'action' => 'delete',
            'panel' => 'admin',
        ]);

        $response = $this->post('/actions/execute', [
            'token' => $token,
        ]);

        $response->assertStatus(404);
    });

    it('returns error for invalid token', function () {
        $response = $this->post('/actions/execute', [
            'token' => 'invalid-token',
        ]);

        $response->assertStatus(500);
    });

    it('handles missing token', function () {
        $response = $this->post('/actions/execute', []);

        $response->assertStatus(500);
    });
});

describe('ActionController Export Endpoint', function () {
    it('returns error when token is missing', function () {
        $response = $this->get('/actions/export');

        // Controller aborts with 400 for missing token
        $response->assertStatus(400);
    });

    it('returns error for invalid token', function () {
        $response = $this->get('/actions/export?token=invalid-token');

        // Controller returns 400 for invalid decrypt
        $response->assertStatus(400);
    });

    it('returns 404 for non-existent exporter class', function () {
        $token = Crypt::encrypt([
            'exporter' => 'App\\NonExistent\\Exporter',
            'fileName' => 'test.xlsx',
        ]);

        $response = $this->get('/actions/export?token='.$token);

        $response->assertStatus(404);
    });
});

describe('ActionController Import Endpoint', function () {
    it('returns error for missing importer class', function () {
        $response = $this->post('/actions/import', []);

        $response->assertRedirect();
    });

    it('returns error for non-existent importer class', function () {
        $response = $this->post('/actions/import', [
            'importer' => 'App\\NonExistent\\Importer',
        ]);

        $response->assertRedirect();
    });

    it('returns error when no file provided', function () {
        $response = $this->post('/actions/import', [
            'importer' => 'App\\Imports\\TestImporter',
        ]);

        $response->assertRedirect();
    });
});

describe('ActionController Standalone Action Execution', function () {
    it('handles expired action', function () {
        $token = Crypt::encrypt([
            'action_id' => 'non-existent-action-id',
            'panel' => 'admin',
        ]);

        $response = $this->post('/actions/execute', [
            'token' => $token,
        ]);

        $response->assertRedirect();
    });
});
