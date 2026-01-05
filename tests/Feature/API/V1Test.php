<?php

use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    $this->slug = $this->slug ?? '';
    $this->password = $this->password ?? '';
});

test('index loads and contains no data', function () {
    $response = $this->get('/api/v1/scout');

    $response->assertStatus(200)->assertJson([]);
});

test('user may create a blank scouting report', function () {
    $response = $this->post('/api/v1/scout', [
        'title' => 'API TEST CASE',
    ]);

    $this->slug = $response['slug'];
    $this->password = $response['collaborator_password'];
    $response->assertStatus(200)->assertJson(
        fn(AssertableJson $json) =>
        $json->has('collaborator_password')
            ->has('readonly_url')
            ->has('collaborate_url')
            ->has('slug')
    );
});

test('user may update fields in an existing report', function() {
    //
})

// test('index contains no data', function() {
//     // We should not allow listing of scouting reports
//     // Users must know specific slugs to access data
//     $response = $this->get('/api/v1/scout')
// })
