<?php

use function Pest\Laravel\get;
use function PHPUnit\Framework\assertEquals;

it('can response', function () {
    get('/ok')->assertStatus(200);
});

it('can response not found', function () {
    get('/not-found')->assertStatus(404);

    assertEquals(get('/not-found/message')->getContent(), json_encode(['message' => 'The resource you are looking for does not exist']));
});

it('can response array', function () {
    $response = $this->get('/array');

    assertEquals($response->getContent(), json_encode(['id' => 1, 'name' => 'name']));
});

it('can response true', function () {
    $response = $this->get('/true');

    assertEquals($response->getContent(), true);
});

it('can response paginator', function () {
    $response = get('/paginator?per_page=3')->assertStatus(200);

    $response->assertSee('data');
    $response->assertSee('links');
    $response->assertSee('first');
    $response->assertSee('meta');
    $response->assertSee('total');
    $response->assertSee('"per_page":3', false);
});
