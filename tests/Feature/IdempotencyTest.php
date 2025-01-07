<?php

use AlgoYounes\Idempotency\Exceptions\DuplicateIdempotencyRequestException;
use Symfony\Component\HttpFoundation\Response;

it('can proceed with the request with idempotency', function () {
    $user = $this->createDefaultUser(['field' => 'default']);
    $this->actingAs($user);

    $response = $this->post('/user', ['field' => 'change'], ['Idempotency-Key' => '1234']);

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJson(['id' => $user->id, 'field' => 'change']);
});

it('can add idempotency header only on repeated requests', function () {
    $user = $this->createDefaultUser(['field' => 'default']);
    $this->actingAs($user);
    $idempotencyKey = 'unique-key-123';
    $response = $this->post('/user', ['field' => 'change'], ['Idempotency-Key' => $idempotencyKey]);
    $response->assertHeaderMissing('Idempotency-Relayed');

    $response = $this->post('/user', ['field' => 'change_again'], ['Idempotency-Key' => $idempotencyKey]);
    $response->assertHeader('Idempotency-Relayed', $idempotencyKey);
});

it('can throw DuplicateIdempotencyRequestException if duplicate handling is enabled', function () {
    $this->withoutExceptionHandling();

    $this->config->setDuplicateHandling('exception');

    $user = $this->createDefaultUser(['field' => 'default']);
    $this->actingAs($user);
    $idempotencyKey = 'unique-key-1234';
    $response = $this->post('/user', ['field' => 'change'], ['Idempotency-Key' => $idempotencyKey]);
    $response->assertHeaderMissing('Idempotency-Relayed');

    $this->expectException(DuplicateIdempotencyRequestException::class);

    $this->post('/user', ['field' => 'change_again'], ['Idempotency-Key' => $idempotencyKey]);
});
