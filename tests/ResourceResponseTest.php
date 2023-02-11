<?php

use function Pest\Laravel\get;
use function PHPUnit\Framework\assertEquals;

it('can response resource', function () {
    $response = get('/resource')->assertStatus(200);

    assertEquals($response->getContent(), json_encode(['data' => ['id' => 1, 'name' => 'name']]));
});

it('can response resource without wrap', function () {
    $response = get('/resource/no-wrap')->assertStatus(200);

    assertEquals($response->getContent(), json_encode(['id' => 1, 'name' => 'name']));
});

it('can response collection resource', function () {
    $response = get('/resource/collection')->assertStatus(200);

    assertEquals($response->getContent(), json_encode([
        'data' => [
            [
                'id' => 1,
                'name' => 'name',
            ],
            [
                'id' => 2,
                'name' => 'name2',
            ],
        ],
    ]));
});

it('can response paginator resource', function () {
    $response = get('/resource/paginator?per_page=2')->assertStatus(200);

    $response->assertSee('data');
    $response->assertSee('links');
    $response->assertSee('first');
    $response->assertSee('meta');
    $response->assertSee('total');
    $response->assertSee('"per_page":2', false);
});
