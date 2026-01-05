<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

pest()->use(RefreshDatabase::class);

test('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('returns expansion data', function () {
    $response = $this->get('/');
    $expac = $response->inertiaProps('expac');
    expect(sizeof($expac))->toBeGreaterThan(0);
    // $response->assertInertia(fn(Assert $page) => $page
    //     ->has('expac', fn(Assert $page) => $page
    //         ->where('id', 2)));
});

test('returns valid zone data', function () {
    $response = $this->get('/');
    $expac = $response->inertiaProps('expac');
    expect($expac[0])->toHaveKey('zones');
    $zone = $expac[0]['zones'][0];
    expect($zone)->toHaveKeys(['id', 'name', 'mobs', 'spawn_points']);
});
