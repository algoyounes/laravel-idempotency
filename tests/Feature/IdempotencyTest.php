<?php

use AlgoYounes\Idempotency\Config\IdempotencyConfig;
use AlgoYounes\Idempotency\Exceptions\CheckSumMismatchIdempotencyException;
use AlgoYounes\Idempotency\Exceptions\DuplicateIdempotencyException;
use AlgoYounes\Idempotency\Exceptions\LockWaitExceededException;
use AlgoYounes\Idempotency\Exceptions\PathMismatchIdempotencyException;
use AlgoYounes\Idempotency\Tests\Feature\fixtures\CustomUserIdResolver;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Symfony\Component\HttpFoundation\Response;

it('proceed with the request with idempotency', function () {
    $user = $this->createDefaultUser(['field' => 'default']);
    $this->actingAs($user);

    $response = $this->post('/user', ['field' => 'change'], ['Idempotency-Key' => '1234']);

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJson(['id' => $user->id, 'field' => 'change']);
});

it('add idempotency header only on repeated requests', function () {
    $user = $this->createDefaultUser(['field' => 'default']);
    $this->actingAs($user);

    $idempotencyKey = 'unique-key-123';
    $response = $this->post('/user', ['field' => 'change'], ['Idempotency-Key' => $idempotencyKey]);
    $response->assertHeader('Idempotency-Relayed', $idempotencyKey);

    $response2 = $this->post('/user', ['field' => 'change'], ['Idempotency-Key' => $idempotencyKey]);
    $response2->assertHeader('Idempotency-Relayed', $idempotencyKey);
    $response2->assertJson($response->json());
});

it('throws exception on duplicate idempotency key with same payload', function () {
    $this->withoutExceptionHandling();

    $this->config->setDuplicateHandling('exception');

    $user = $this->createDefaultUser(['field' => 'default']);
    $this->actingAs($user);

    $idempotencyKey = 'unique-key-1234';
    $response = $this->post('/user', ['field' => 'change'], ['Idempotency-Key' => $idempotencyKey]);
    $response->assertHeader('Idempotency-Relayed', $idempotencyKey);

    $this->expectException(DuplicateIdempotencyException::class);

    $this->post('/user', ['field' => 'change'], ['Idempotency-Key' => $idempotencyKey]);
});

it('throws exception on duplicate idempotency key with different payload', function () {
    $this->withoutExceptionHandling();

    $user = $this->createDefaultUser(['field' => 'default']);
    $this->actingAs($user);

    $idempotencyKey = 'unique-key-12345';
    $response = $this->post('/user', ['field' => 'change'], ['Idempotency-Key' => $idempotencyKey]);
    $response->assertHeader('Idempotency-Relayed', $idempotencyKey);

    $this->expectException(CheckSumMismatchIdempotencyException::class);

    $this->post('/user', ['field' => 'change_again'], ['Idempotency-Key' => $idempotencyKey]);
});

it('throws exception on duplicate idempotency key via different path', function () {
    $this->withoutExceptionHandling();

    $user = $this->createDefaultUser(['field' => 'default']);
    $this->actingAs($user);

    $idempotencyKey = 'unique-key-12345';
    $response = $this->post('/user', ['field' => 'change'], ['Idempotency-Key' => $idempotencyKey]);
    $response->assertHeader('Idempotency-Relayed', $idempotencyKey);

    $this->expectException(PathMismatchIdempotencyException::class);

    $this->post('/account', ['field' => 'change'], ['Idempotency-Key' => $idempotencyKey]);
});

it('request without idempotency key', function () {
    $user = $this->createDefaultUser(['field' => 'default']);
    $this->actingAs($user);

    $response = $this->post('/user', ['field' => 'change']);

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJson(['id' => $user->id, 'field' => 'change']);
});

it('request without authenticated account', function () {
    $response = $this->post('/account', [], ['Idempotency-Key' => '1234']);

    $this->assertTrue($this->idempotencyCacheManager->hasIdempotency('1234', 'guest'));

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJson(['message' => 'success']);
});

it('request without idempotency key on non applicable requests', function () {
    $user = $this->createDefaultUser(['field' => 'default']);
    $this->actingAs($user);

    $response = $this->get('/user');

    $response->assertStatus(Response::HTTP_OK);
    $response->assertHeaderMissing('Idempotency-Relayed');
    $response->assertJson(['id' => $user->id, 'field' => 'default']);
});

it('throws lock wait exceeded exception', function () {
    $this->withoutExceptionHandling();

    $this->config->setMaxLockWaitTime(1);

    $this->idempotencyCacheManager->acquireLock('1234', 'guest');

    $this->expectException(LockWaitExceededException::class);
    $this->post('/account', [], ['Idempotency-Key' => '1234']);
});

it('request with custom user id resolver', function () {
    $this->withoutExceptionHandling();

    updateIdempotencyConfig($this->app, [
        'idempotency.user_id_resolver' => CustomUserIdResolver::class,
    ]);

    $response = $this->post('/account', [], ['Idempotency-Key' => '1234']);

    $this->assertTrue($this->idempotencyCacheManager->hasIdempotency('1234', 'custom-user-id'));
    $response->assertStatus(Response::HTTP_OK);
    $response->assertJson(['message' => 'success']);
});

function updateIdempotencyConfig($app, array $config): void
{
    config($config);

    $app->singleton(IdempotencyConfig::class, function ($app) {
        /** @var ConfigRepository $configRepository */
        $configRepository = $app->make(ConfigRepository::class);
        $config = (array) $configRepository->get('idempotency', []);

        return IdempotencyConfig::createFromArray($config);
    });
}
