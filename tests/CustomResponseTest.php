<?php

use function Pest\Laravel\get;

it('can response custom wrap', function () {
    $response = get('/custom/wrap')->assertStatus(200);

    $response->assertSee('user');
});

it('can response custom collection wrap', function () {
    $response = get('/custom/collection-wrap')->assertStatus(200);

    $response->assertSee('users');
});

it('can response custom using response', function () {
    $response = get('/custom/response/using')->assertStatus(200);

    $response->assertSee('"code":200', false);
});

it('can response custom using response for data not array', function () {
    $response = get('/custom/response/data-not-array')->assertStatus(200);

    $response->assertSee('"code":200', false);
});
